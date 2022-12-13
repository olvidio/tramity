<?php

namespace entidades\domain\repositories;

use core\ConfigGlobal;
use entidades\domain\entity\EntidadDB;
use usuarios\domain\entity\Cargo;


class EntidadRepository extends EntidadDBRepository
{

    /* CONST -------------------------------------------------------------- */

    /* Utilizar los mismos que en cargo
    const AMBITO_CG  = 1;
    const AMBITO_CR  = 2;
    const AMBITO_DL  = 3;  //"dl"
    const AMBITO_CTR = 4;
    */

    private string $sdir;
    private string $sfileLog;
    private string $snameFileLog;
    private string $ssql_txt;

    private string $nom_schema;

    /* CONSTRUCTOR -------------------------------------------------------------- */


    /* MÉTODOS PÚBLICOS ----------------------------------------------------------*/

    public function getArrayTipo(): array
    {
        return [
            Cargo::AMBITO_CTR => _("ctr"),
            Cargo::AMBITO_DL => _("dl"),
            Cargo::AMBITO_CR => _("cr"),
            Cargo::AMBITO_CG => _("cg"),
        ];
    }


    public function eliminarEsquema(EntidadDB $EntidadDB): string
    {
        return $this->dropEsquema($EntidadDB);
    }

    private function dropEsquema(EntidadDB $EntidadDB): string
    {
        $oDbl = $this->getoDbl();
        $nom_schema = $EntidadDB->getSchema();
        $err_txt = '';
        $sql = "DROP SCHEMA IF EXISTS \"$nom_schema\" CASCADE ";

        if (($oDblSt = $oDbl->prepare($sql)) === false) {
            $sClauError = 'Entidad.eliminarSchema.prepare';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            $err_txt .= sprintf(_("ERROR AL ELIMINAR EL ESQUEMA: %s"),$sClauError);
        } else {
            if ($oDblSt->execute() === false) {
                $sClauError = 'Entidad.eliminarSchema.execute';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
                $err_txt .= sprintf(_("ERROR AL ELIMINAR EL ESQUEMA: %s"),$sClauError);
            }
        }
        return $err_txt;
    }

    public function nuevoEsquema(EntidadDB $EntidadDB): string
    {
        $err = $this->crearEsquema($EntidadDB);
        $err .= $this->crearTablas($EntidadDB);

        return $err;
    }

    private function crearEsquema(EntidadDB $EntidadDB): string
    {
        $oDbl = $this->getoDbl();
        $nom_schema = $EntidadDB->getSchema();
        $err_txt = '';
        $sql = "CREATE SCHEMA IF NOT EXISTS \"$nom_schema\" ";

        if (($oDblSt = $oDbl->prepare($sql)) === false) {
            $sClauError = 'Entidad.crearSchema.prepare';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            $err_txt .= sprintf(_("ERROR AL CREAR EL ESQUEMA: %s"), $sClauError);
        } else {
            if ($oDblSt->execute() === false) {
                $sClauError = 'Entidad.crearSchema.execute';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
                $err_txt .= sprintf(_("ERROR AL CREAR EL ESQUEMA: %s"), $sClauError);
            }
        }
        return $err_txt;
    }

    /**
     * Tablas actuales
     * global:
     *    lugares
     *    entidades
     *    x_locales
     *
     *  cada esquema:
     *    acciones
     *    aux_cargos
     *    aux_usuarios
     *    documentos
     *    entrada_adjuntos
     *    entrada_doc
     *    entrada_doc_bytea
     *    entrada_doc_json
     *    entrada_doc_txt
     *    entradas
     *    escrito_adjuntos
     *    escritos
     *    etiquetas
     *    etiquetas_documento
     *    etiquetas_expediente
     *    expediente_firmas
     *    expedientes
     *    pendientes
     *    plantillas
     *    tramite_cargo
     *    usuario_preferencias
     *    x_config
     *    x_tramites
     *
     *   sólo dl:
     *    cargos_grupos
     *    entradas_bypass
     *    lugares_grupos
     *    x_oficinas
     *
     */
    private function crearTablas(EntidadDB $EntidadDB): string
    {
        $this->nom_schema = $EntidadDB->getSchema();
        $err = '';
        // entradas:
        $err .= $this->ejecutarPsqlCrear('entradas');
        // para las dl:
        $tipo_entidad = $EntidadDB->getTipo();
        if ($tipo_entidad === Cargo::AMBITO_DL) {
            // entradas_bypass
            $err .= $this->ejecutarPsqlCrear('entradas', TRUE);
        }
        // escritos:
        $err .= $this->ejecutarPsqlCrear('escritos');
        // expedientes:
        $err .= $this->ejecutarPsqlCrear('expedientes');
        // etiquetas:
        $err .= $this->ejecutarPsqlCrear('etiquetas');
        // documentos:
        $err .= $this->ejecutarPsqlCrear('documentos');
        // pendientes:
        $err .= $this->ejecutarPsqlCrear('pendientes');
        // plantillas:
        $err .= $this->ejecutarPsqlCrear('plantillas');
        // config:
        $err .= $this->ejecutarPsqlCrear('config');
        // usuarios:
        $err .= $this->ejecutarPsqlCrear('usuarios');
        // tramites (tiene que estar después de usuarios, porque depende de la tabla aux_cargos):
        $err .= $this->ejecutarPsqlCrear('tramites');

        // para las dl:
        if ($tipo_entidad === Cargo::AMBITO_DL) {
            // x_oficinas y cargos_grupos
            $err .= $this->ejecutarPsqlCrear('usuarios', TRUE);
            // lugares_grupos
            $err .= $this->ejecutarPsqlCrear('lugares', TRUE);
        }
        // INSERTS
        if ($EntidadDB->getTipo() === Cargo::AMBITO_DL) {
            // insert cargos mínimos usuarios:
            $err .= $this->ejecutarPsqlInsert('usuarios', TRUE);
            $err .= $this->ejecutarPsqlInsert('tramites', TRUE);
            $err .= $this->ejecutarPsqlInsert('config', TRUE);
        } else {
            // insert cargos mínimos usuarios:
            $err .= $this->ejecutarPsqlInsert('usuarios');
            $err .= $this->ejecutarPsqlInsert('tramites');
            $err .= $this->ejecutarPsqlInsert('config');
        }
        // añadir la sigla en config:
        $sigla = $EntidadDB->getNombre();
        $err .= $this->ejecutarSql("INSERT INTO nombre_del_esquema.x_config (parametro, valor) VALUES ('sigla', '$sigla')");

        return $err;
    }

    private function ejecutarPsqlCrear(string $app, bool $dl = FALSE): string
    {
        if ($dl) {
            $this->ssql_txt = file_get_contents("../../$app/db/$app" . "_dl.sql");
        } else {
            $this->ssql_txt = file_get_contents("../../$app/db/$app.sql");
        }
        $this->snameFileLog = $this->getFileLog($app);

        return $this->ejecutarPsql();
    }

    private function getFileLog(string $filename = 'tramity'): string
    {
        $this->sfileLog = $this->getDir() . '/' . $filename . '.pg_error.sql';

        $command = "touch " . $this->sfileLog;
        passthru($command); // no output to capture so no need to store it

        return $this->sfileLog;
    }

    private function getDir(): string
    {
        $this->sdir = empty($this->sdir) ? ConfigGlobal::$directorio . '/log/db' : $this->sdir;
        return $this->sdir;
    }

    private function ejecutarPsql(): string
    {
        $err_txt = '';
        $sql_txt = $this->ssql_txt;
        $file_log = $this->snameFileLog;

        // cambiar nombre esquema
        $nom_schema = "\\\"" . $this->nom_schema . "\\\"" . '.';

        $sql_txt_nou = str_replace('nombre_del_esquema.', $nom_schema, $sql_txt);
        // para la función idglobal
        $idglobal_txt_nou = "idglobal('" . $this->nom_schema;
        $sql_txt_nou = str_replace('idglobal(\'nombre_del_esquema', $idglobal_txt_nou, $sql_txt_nou);

        $command = "/usr/bin/psql -U tramity -d tramity -c << EOF \" $sql_txt_nou \" ";
        $command .= " 2> " . $file_log;
        //passthru($command); // no output to capture so no need to store it
        $output = [];
        $return_var = 0;
        exec($command, $output, $return_var);
        // read the file, if empty all's well
        $error = file_get_contents($file_log);
        if (trim($error) !== ''
            && stripos($error, 'error') !== FALSE // evitar los NOTICE y otros
            && ConfigGlobal::is_debug_mode()) {
            $err_txt .= sprintf("PSQL ERROR IN COMMAND(1): %s<br> mirar: %s<br>", $command, $file_log);
        }
        return $err_txt;
    }

    private function ejecutarPsqlInsert(string $app, bool $dl = FALSE): string
    {
        if ($dl) {
            $this->ssql_txt = file_get_contents("../../$app/db/insert_$app" . "_dl.sql");
        } else {
            $this->ssql_txt = file_get_contents("../../$app/db/insert_$app.sql");
        }
        $this->snameFileLog = $this->getFileLog($app);

        return $this->ejecutarPsql();
    }

    private function ejecutarSql(string $sql): string
    {
        $oDbl = $this->getoDbl();
        $err_txt = '';

        // cambiar nombre esquema
        $nom_schema = "\"" . $this->nom_schema . "\"" . '.';
        $sql_txt_nou = str_replace('nombre_del_esquema.', $nom_schema, $sql);

        if (($oDblSt = $oDbl->prepare($sql_txt_nou)) === false) {
            $sClauError = 'Entidad.sql.prepare';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            $err_txt .= sprintf(_("ERROR AL EJECUTAR SQL: %s"), $sClauError);
        } else {
            if ($oDblSt->execute() === false) {
                $sClauError = 'Entidad.sql.execute';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
                $err_txt .= sprintf(_("ERROR AL EJECUTAR SQL: %s"), $sClauError);
            }
        }
        return $err_txt;
    }
}