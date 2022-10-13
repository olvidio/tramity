<?php
namespace web;

class DesplegableArray extends Desplegable
{
    /* ATRIBUTOS ----------------------------------------------------------------- */

    /**
     * sSeleccionados del Desplegable
     *
     * @var string
     */
    private $sSeleccionados;

    /**
     * sNomConjunto del Desplegable
     *
     * @var string
     */
    private $sNomConjunto;
    /**
     * sAccionConjunto del Desplegable
     *
     * @var string
     */
    private $sAccionConjunto;
    /**
     * iTabIndexIni del Desplegable
     *
     * @var integer
     */
    private $iTabIndexIni;


    /* CONSTRUCTOR -------------------------------------------------------------- */

    /**
     * Constructor de la classe.
     *
     */
    function __construct($id, $Opciones, $Nom)
    {
        if (isset($id) && $id !== '') {
            $this->sSeleccionados = $id;
        }
        if (isset($Opciones) && $Opciones !== '') {
            $this->oOpciones = $Opciones;
        }
        if (isset($Nom) && $Nom !== '') {
            $this->sNomConjunto = $Nom;
        }

    }

    /* MÉTODOS PÚBLICOS ----------------------------------------------------------*/


    /**
     *
     * Esta función sirve para hacer el echo en html de la lista de seleccionados
     * usando bootstrap.
     *
     * @return string html
     */
    public function Lista()
    {
        $aSeleccionados = '';
        if (is_array($this->sSeleccionados)) {
            $aSeleccionados = $this->sSeleccionados;
        }
        if (is_string($this->sSeleccionados)) {
            $aSeleccionados = explode(",", $this->sSeleccionados);
        }

        if (is_array($this->oOpciones)) {
            $a_opciones = $this->oOpciones;
        }

        $sLista = "<div class=\"btn-group btn-group-toggle\" data-bs-toggle=\"buttons\">";
        if (!empty($aSeleccionados)) {
            foreach ($aSeleccionados as $id) {
                $sLista .= "<label class=\"btn btn-outline-secondary form-label\" >";
                $sLista .= $a_opciones[$id];
                $sLista .= "</label>";
            }
        }
        $sLista .= "</div>";

        return $sLista;
    }

    /**
     *
     * Esta función sirve para hacer el echo en html de un input tipo select.
     * Dentro de una tabla.
     *
     * @return string html <select>...</select>
     */
    public function ListaSelects()
    {

        $fnjs_comprobar = 'fnjs_comprobar_' . $this->sNomConjunto;

        $aSeleccionados = '';
        if (is_array($this->sSeleccionados)) {
            $aSeleccionados = $this->sSeleccionados;
        }
        if (is_string($this->sSeleccionados)) {
            $aSeleccionados = explode(",", $this->sSeleccionados);
        }

        $span = $this->sNomConjunto . "_span";
        $n = 0;
        $sLista = "<span id=\"$span\" >";
        if (!empty($aSeleccionados)) {
            foreach ($aSeleccionados as $id) {
                $this->sNombre = $this->sNomConjunto . "[$n]";
                if (isset($this->iTabIndexIni)) {
                    $this->iTabIndex = $this->iTabIndexIni + $n;
                }
                $this->sOpcion_sel = $id;
                $this->sAction = "$fnjs_comprobar('" . $this->sNomConjunto . "',$n);";

                $sLista .= $this->desplegable();
                $n++;
            }
        }
        $sLista .= "</span>";
        // para que me salga una opción más en blanco
        $this->sNombre = $this->sNomConjunto . "_mas";
        $this->sAction = $this->sAccionConjunto;
        $this->sOpcion_sel = '';
        $sLista .= $this->desplegable();
        $sLista .= "<input type=hidden name='" . $this->sNomConjunto . "_num' id='" . $this->sNomConjunto . "_num' value=$n>";
        return $sLista;
    }

    /**
     *
     * Esta función sirve para hacer el echo en html de un input tipo select.
     * Dentro de una tabla.
     *
     * @return string para javascript.
     */
    public function ListaSelectsJs()
    {

        $fnjs_comprobar = 'fnjs_comprobar_' . $this->sNomConjunto;
        $nom = $this->sNomConjunto;
        $mas = $this->sNomConjunto . "_mas";
        $num = $this->sNomConjunto . "_num";
        $span = $this->sNomConjunto . "_span";
        $tab = isset($this->iTabIndexIni) ? $this->iTabIndexIni : 10;

        $txt_js = "\n\t\t\tvar num=$('#$num');";
        $txt_js .= "\n\t\t\tvar id_mas=$('#$mas').val();";
        $txt_js .= "\n\t\t\tvar n=Number(num.val());";
        $txt_js .= "\n\t\t\tvar txt;";
        $txt_js .= "\n\t\t\tvar tab=$tab+n;";

        $txt_js .= "\n\t\t\ttxt='<select tabindex=";
        $txt_js .= "'+tab+' id=" . $nom . "['+n+'] name=" . $nom . "['+n+'] class=contenido onChange=$fnjs_comprobar(\'" . $nom . "\','+n+');>';";
        $txt_js .= "\n\t\t\ttxt += '" . addslashes($this->options()) . "';";
        $txt_js .= "\n\t\t\ttxt += '</select>';";
        $txt_js .= "\n\t\t\t// antes del desplegable de añadir";
        $txt_js .= "\n\t\t\t$('#$span').append(txt);";
        $txt_js .= "\n\t\t\t// selecciono el valor del desplegable";
        $txt_js .= "\n\t\t\tvar nom='#" . $nom . "\\\\['+n+'\\\\]';";
        $txt_js .= "\n\t\t\t$(nom).val(id_mas);";
        $txt_js .= "\n\t\t\tn1=n+1;";
        $txt_js .= "\n\t\t\tnum.val(n1);";
        $txt_js .= "\n\t\t\t$('#$mas').val('');";
        $txt_js .= "\n";

        return $txt_js;
    }

    /**
     *
     * Esta función sirve para hacer el echo en html de un input tipo select.
     * Dentro de una tabla.
     *
     * @return string para javascript.
     */
    public function ComprobarSelectJs()
    {
        $fnjs_comprobar = 'fnjs_comprobar_' . $this->sNomConjunto;
        $txt_js = "\n$fnjs_comprobar = function (nom,n) {";
        $txt_js .= "\n\t" . 'var id="#"+nom+"\\\\["+n+"\\\\]";';
        $txt_js .= "\n\t" . 'var valor=$(id).val();';
        $txt_js .= "\n\tif (!valor) {";
        $txt_js .= "\n\t\t" . '$(id).hide();';
        $txt_js .= "\n\t}";
        $txt_js .= "\n}";
        $txt_js .= "\n";

        return $txt_js;
    }

    /* METODES PRIVATS ----------------------------------------------------------*/

    public function setSeleccionados($sSeleccionados)
    {
        $this->sSeleccionados = $sSeleccionados;
    }

    public function setNomConjunto($sNomConjunto)
    {
        $this->sNomConjunto = $sNomConjunto;
    }

    public function setAccionConjunto($sAccionConjunto)
    {
        $this->sAccionConjunto = $sAccionConjunto;
    }
}

?>
