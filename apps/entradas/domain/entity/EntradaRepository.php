<?php

namespace entradas\domain\entity;

use entradas\domain\repositories\EntradaDBRepository;
use usuarios\domain\Categoria;


class EntradaRepository extends EntradaDBRepository
{

    /**
     * Anula las entradas individuales en cada nombre_entidad para una entrada compartida
     *
     * @param integer $id_entrada_compartida
     * @param string $anular_txt
     * @param array $aEntidades nombre del esquema de la DB
     * @return boolean
     */
    public function anularCompartidas(int $id_entrada_compartida, string $anular_txt, array $aEntidades): bool
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        // Quitar esquema al $nom_tabla
        preg_replace('/(\w+)\.(\w+)/', '$2', $nom_tabla);
        $categoria = Categoria::CAT_NORMAL;

        foreach ($aEntidades as $schema) {
            $nom_tabla_entidad = '"' . $schema . '".' . $nom_tabla;
            $sQry = "UPDATE $nom_tabla_entidad SET anulado = '$anular_txt', categoria = $categoria
					WHERE id_entrada_compartida = $id_entrada_compartida";

            if (($oDbl->query($sQry)) === FALSE) {
                $sClauError = 'EntradaRepository.anular.execute';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
                return FALSE;
            }
        }
        return TRUE;
    }

}