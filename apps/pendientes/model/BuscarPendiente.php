<?php

namespace pendientes\model;


use core\ConfigGlobal;
use core\Converter;
use davical\model\Davical;
use davical\model\entity\GestorCalendarItem;
use usuarios\model\entity\Cargo;
use web\DateTimeLocal;
use web\NullDateTimeLocal;

// Archivos requeridos por esta url **********************************************
require_once("/usr/share/awl/inc/iCalendar.php");

class BuscarPendiente
{

    /**
     * calendario de BuscarPendiente
     *
     * @var string
     */
    private $calendario;
    /**
     * asunto de BuscarPendiente
     *
     * @var string
     */
    private $asunto;
    /**
     * status de BuscarPendiente
     *
     * @var string
     */
    private $status;
    /**
     * id_oficina de BuscarPendiente
     *
     * @var integer
     */
    private $id_oficina;
    /**
     * id_reg de BuscarPendiente
     *
     * @var integer[]
     */
    private $id_reg = [];
    /**
     * df_min de BuscarPendiente
     *
     * @var DateTimeLocal
     */
    private $df_min;
    /**
     * df_max de BuscarPendiente
     *
     * @var DateTimeLocal
     */
    private $df_max;

    /**
     * @return string
     */


    public function getPendientes()
    {
        $oDavical = new Davical($_SESSION['oConfig']->getAmbito());
        $cond = '';

        if (!empty($this->id_reg)) {
            $cond .= empty($cond) ? '' : ' AND ';
            $id_entradas = '';
            foreach ($this->id_reg as $id) {
                $id_entradas .= empty($id_entradas) ? '' : ',';
                $id_entradas .= "'REN" . $id . "'";
            }
            $cond .= "rtrim(substring(uid from '^REN.*-'),'-') IN ($id_entradas)";
        }

        // dav_name = /oficina_agd/oficina/20210211T123510.ics
        $parent_container = $oDavical->getNombreRecurso($this->id_oficina);
        if (!empty($this->id_oficina)) {
            $dav_name = '^\/' . $parent_container . '\/' . $this->calendario . '\/';
        } else {
            // por lo menos hay que limitar que sólo busque a los del ctr o la dl.
            $dav_name = '^\/' . $parent_container . '[^\/]*\/' . $this->calendario . '\/';
        }
        $cond .= empty($cond) ? '' : ' AND ';
        $cond .= "dav_name ~ '$dav_name'";

        if (!empty($this->asunto)) {
            $cond .= empty($cond) ? '' : ' AND ';
            $cond .= "summary ~* '.*" . $this->asunto . "'";
        }

        if (!empty($this->status) && ($this->status != 'all')) {
            $cond .= empty($cond) ? '' : ' AND ';
            $cond .= "status = '$this->status'";
        }

        if (!empty($this->df_min)) {
            $cond .= empty($cond) ? '' : ' AND ';
            $cond .= "due > '" . $this->getF_min()->getIso() . "'";
        }
        if (!empty($this->df_max)) {
            $cond .= empty($cond) ? '' : ' AND ';
            $cond .= "due < '" . $this->getF_max()->getIso() . "'";
        }

        $sql_pen = "SELECT * FROM calendar_item WHERE $cond";
        $gesCalendarItem = new GestorCalendarItem();
        $cCalendarItems = $gesCalendarItem->getCalendarItemsQuery($sql_pen);

        if ($_SESSION['oConfig']->getAmbito() == Cargo::AMBITO_DL) {
            // solo secretaría puede ver/crear pendientes de otras oficinas
            $user_davical = $oDavical->getUsernameDavicalSecretaria();
        } else {
            // nombre normalizado del usuario y oficina:
            $id_cargo_role = ConfigGlobal::role_id_cargo();
            $user_davical = $oDavical->getUsernameDavical($id_cargo_role);
        }

        // Buscar el uid para conseguir el Pendiente
        $cPendientes = [];
        foreach ($cCalendarItems as $oCalendarItem) {
            $uid = $oCalendarItem->getUid();
            $dav_name = $oCalendarItem->getDav_name();
            // "/oficina_agd/registro/REN20-20210225T124453.ics"
            $pos = strpos($dav_name, '/', 1);
            $parent_container = substr($dav_name, 1, $pos - 1);
            $calendario = $this->calendario;

            $oPendiente = new Pendiente($parent_container, $calendario, $user_davical, $uid);
            $cPendientes[] = $oPendiente;
        }

        return $cPendientes;

    }

    /**
     * Recupera l'atribut df_min de ExpedienteDB
     *
     * @return DateTimeLocal df_min
     */
    function getF_min()
    {
        if (!isset($this->df_min) && !$this->bLoaded) {
            $this->DBCarregar();
        }
        if (empty($this->df_min)) {
            return new NullDateTimeLocal();
        }
        $oConverter = new Converter('date', $this->df_min);
        return $oConverter->fromPg();
    }

    /**
     * Recupera l'atribut df_max de ExpedienteDB
     *
     * @return DateTimeLocal df_max
     */
    function getF_max()
    {
        if (!isset($this->df_max) && !$this->bLoaded) {
            $this->DBCarregar();
        }
        if (empty($this->df_max)) {
            return new NullDateTimeLocal();
        }
        $oConverter = new Converter('date', $this->df_max);
        return $oConverter->fromPg();
    }

    public function getCalendario()
    {
        return $this->calendario;
    }

    /**
     * @param string $calendario
     */
    public function setCalendario($calendario)
    {
        $this->calendario = $calendario;
    }

    /**
     * @return string
     */
    public function getAsunto()
    {
        return $this->asunto;
    }

    /**
     * @param string $asunto
     */
    public function setAsunto($asunto)
    {
        $this->asunto = $asunto;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return number
     */
    public function getId_reg()
    {
        return $this->id_reg;
    }

    /**
     * @param number $id_reg
     */
    public function setId_reg($a_id_reg)
    {
        $this->id_reg = $a_id_reg;
    }

    /**
     * estableix el valor de l'atribut df_min de ExpedienteDB
     * Si df_min es string, y convert=TRUE se convierte usando el formato web\DateTimeLocal->getFormat().
     * Si convert es FALSE, df_min debe ser un string en formato ISO (Y-m-d). Corresponde al pgstyle de la base de datos.
     *
     * @param web\DateTimeLocal|string df_min='' optional.
     * @param boolean convert=TRUE optional. Si es FALSE, df_min debe ser un string en formato ISO (Y-m-d).
     */
    function setF_min($df_min = '', $convert = TRUE)
    {
        if ($convert === TRUE && !empty($df_min)) {
            $oConverter = new Converter('date', $df_min);
            $this->df_min = $oConverter->toPg();
        } else {
            $this->df_min = $df_min;
        }
    }

    /**
     * estableix el valor de l'atribut df_max de ExpedienteDB
     * Si df_max es string, y convert=TRUE se convierte usando el formato web\DateTimeLocal->getFormat().
     * Si convert es FALSE, df_max debe ser un string en formato ISO (Y-m-d). Corresponde al pgstyle de la base de datos.
     *
     * @param web\DateTimeLocal|string df_max='' optional.
     * @param boolean convert=TRUE optional. Si es FALSE, df_max debe ser un string en formato ISO (Y-m-d).
     */
    function setF_max($df_max = '', $convert = TRUE)
    {
        if ($convert === TRUE && !empty($df_max)) {
            $oConverter = new Converter('date', $df_max);
            $this->df_max = $oConverter->toPg();
        } else {
            $this->df_max = $df_max;
        }
    }


    /**
     * @return string
     */
    public function getId_oficina()
    {
        return $this->id_oficina;
    }

    /**
     * @param string $id_oficina
     */
    public function setId_oficina($id_oficina)
    {
        $this->id_oficina = $id_oficina;
    }


}