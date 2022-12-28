<?php

namespace pendientes\model;


use core\ViewTwig;
use web\DateTimeLocal;

// Archivos requeridos por esta url **********************************************
require_once("/usr/share/awl/inc/iCalendar.php");

class Rrule
{

    /**
     * Función para buscar las fechas repetidas según una rrule dentro de un periodo
     *
     * @param string $rrule
     * @param string $dtstart (formato ISO)
     * @param string $dtend (formato ISO)
     * @param string $f_limite (formato ISO)
     * @return array|string[]
     */
    public static function recurrencias($rrule, $dtstart, $dtend, $f_limite)
    {
        if (empty($rrule)) {
            exit;
        }
        $a_dias_w = ["MO" => "Monday",
            "TU" => "Tuesday",
            "WE" => "Wednesday",
            "TH" => "Thursday",
            "FR" => "Friday",
            "SA" => "Saturday",
            "SU" => "Sunday",
        ];

        $rta = self::desmontar_rule($rrule);
        if (empty($rta['tipo'])) {
            echo _('No hay tipo en recurrencias');
            return array();
        }
        // si hay un "UNTIL", lo pongo como fecha fin.
        if (!empty($rta['until'])) {
            $dtend = $rta['until'];
        } // si hay un "UNTIL", lo pongo como fecha fin.

        $any_actual = date("Y");
        $oF_limite = new DateTimeLocal($f_limite);
        $f_limite = $oF_limite->getIso();
        // Si no existe f_fin del periódico, hago que sea igual al fin del periodo escogido:
        if (empty($dtend)) {
            $dtend = $f_limite;
        } else {
            if ($dtend > $f_limite) {
                $dtend = $f_limite;
            }
        }
        $oF_end = new DateTimeLocal($dtend);
        $any_fin = $oF_end->format('Y');
        $month_fin = $oF_end->format('m');
        $day_fin = $oF_end->format('d');
        $oF_start = new DateTimeLocal($dtstart);
        $any_ini = $oF_start->format('Y');
        $month_ini = $oF_start->format('m');
        $day_ini = $oF_start->format('d');

        $inc_a = 1;
        switch ($rta['tipo']) {
            case "d_a":
                switch ($rta['tipo_dia']) {
                    case "num_ini":
                        $dias_db = $day_ini; // cojo el dia de la fecha inicio.
                        $meses_db = array($month_ini);
                        $tipo_dia = "num_ini";
                        break;
                    case "num":
                        $dias_db = explode(",", $rta['dias']);
                        $meses_db = explode(",", $rta['meses']);
                        $tipo_dia = "num";
                        break;
                    case "ref":
                        $dia_w_db = $rta['dia_semana'];
                        $ordinal_db = $rta['ordinal'];
                        $meses_db = explode(",", $rta['meses']);
                        $tipo_dia = "ref";
                        break;
                    case "num_dm":
                        $meses_db = explode(",", $rta['meses']);
                        $dias_db = explode(",", $rta['dias']);
                        $tipo_dia = "num";
                        // Si hay interval, el incremento depende del interval,
                        // y el año de inicio debe ser el dtstart.
                        if (!empty($rta['interval'])) {
                            $inc_a = $rta['interval'];
                            $any_actual = $any_ini;
                        }
                        break;
                    default:
                        $err_switch = sprintf(_("opción no definida en switch en %s, linea %s"), __FILE__, __LINE__);
                        exit ($err_switch);
                }
                break;
            case "d_m":
                $meses_db = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12);
                switch ($rta['tipo_dia']) {
                    case "num_ini":
                        $dias_db = $day_ini; // cojo el dia de la fecha inicio.
                        $tipo_dia = "num_ini";
                        break;
                    case "num":
                        $dias_db = explode(",", $rta['dias']);
                        $tipo_dia = "num";
                        break;
                    case "ref":
                        $dia_w_db = $rta['dia_semana'];
                        $ordinal_db = $rta['ordinal'];
                        $tipo_dia = "ref";
                        break;
                    default:
                        $err_switch = sprintf(_("opción no definida en switch en %s, linea %s"), __FILE__, __LINE__);
                        exit ($err_switch);
                }
                break;
            case "d_s":
                $meses_db = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12);
                $tipo_dia = "semana";
                switch ($rta['tipo_dia']) {
                    case "num_ini":
                        // busco la letra del dia de la fecha inicio.
                        $dias_w_db = array(strtoupper(substr(date("D", mktime(0, 0, 0, $month_ini, $day_ini, $any_ini)), 0, 2)));
                        $tipo_dia = "semana";
                        break;
                    case "ref":
                        $dias_w_db = explode(",", $rta['dias']);
                        $tipo_dia = "semana";
                        break;
                    default:
                        $err_switch = sprintf(_("opción no definida en switch en %s, linea %s"), __FILE__, __LINE__);
                        exit ($err_switch);
                }
                break;
            case "d_d":
                $meses_db = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12);
                $dias_db = $day_ini; // cojo el dia de la fecha inicio.
                $tipo_dia = "todos";
                break;
            default:
                $err_switch = sprintf(_("opción no definida en switch en %s, linea %s"), __FILE__, __LINE__);
                exit ($err_switch);
        }
        // caso de dias y meses
        // antes tendría que mirar por cada año. Desde el actual hasta el fin de la condicion.
        $f_recurrencias = [];
        for ($any = $any_actual; $any <= $any_fin; $any = $any + $inc_a) {
            // Me salto los años anteriores a la fecha de inicio
            if ($any < $any_ini || $any > $any_fin) {
                continue;
            }
            // por cada mes miro que dias
            if (!is_array($meses_db)) {
                continue;
            }
            foreach ($meses_db as $mes) {
                $mes = trim($mes);
                // Me salto los meses anteriores a la fecha de inicio y los posteriores a la de fin
                if (($mes < $month_ini && $any == $any_ini) || ($mes > $month_fin && $any == $any_fin)) {
                    continue;
                }
                switch ($tipo_dia) {
                    case "num_ini":
                        $dia = trim($dias_db);
                        // Me salto los dias del mes anteriores a la fecha de inicio y los posteriores a la de fin
                        if (($dia < $day_ini && $mes == $month_ini && $any == $any_ini) || ($dia > $day_fin && $mes == $month_fin && $any == $any_fin)) {
                            break;
                        }
                        $f_recurrencias[] = "$any-$mes-$dia";
                        break;
                    case "num":
                        foreach ($dias_db as $dia) {
                            $txt_alert = '';
                            $dia = trim($dia);
                            if ($mes == 2 && $dia > 28) {
                                $txt_alert = sprintf(_("Cuidado con febrero para el día: %s"), $dia);
                            }
                            if ($dia > 30 && in_array($mes, [4, 6, 9, 11])) {
                                $txt_alert = _("Cuidado con los meses de menos de 31 días");
                            }
                            if (!empty($txt_alert)) {
                                $a_campos = ['txt_alert' => $txt_alert];
                                $oView = new ViewTwig('expedientes/controller');
                                $oView->renderizar('alerta.html.twig', $a_campos);
                            }
                            // Me salto los dias del mes anteriores a la fecha de inicio y los posteriores a la de fin
                            if (($dia < $day_ini && $mes == $month_ini && $any == $any_ini) || ($dia > $day_fin && $mes == $month_fin && $any == $any_fin)) {
                                break;
                            }
                            $f_recurrencias[] = "$any-$mes-$dia";
                        }
                        break;
                    case "ref":
                        if ($ordinal_db > 0) {
                            $dia_w_txt = $a_dias_w[$dia_w_db];
                            $txt = "$ordinal_db $dia_w_txt";
                            $dia = date("d", strtotime($txt, mktime(0, 0, 0, $mes, 1, $any)));
                        }
                        if ($ordinal_db < 0) {
                            $dia_w_txt = $a_dias_w[$dia_w_db];
                            $txt = "$ordinal_db $dia_w_txt";
                            $dia = date("d", strtotime($txt, mktime(0, 0, 0, $mes + 1, 1, $any)));
                        }
                        // Me salto los dias del mes anteriores a la fecha de inicio y los posteriores a la de fin
                        if (($dia < $day_ini && $mes == $month_ini && $any == $any_ini) || ($dia > $day_fin && $mes == $month_fin && $any == $any_fin)) {
                            break;
                        }
                        $f_recurrencias[] = "$any-$mes-$dia";
                        break;
                    case "semana":
                        $dias_del_mes = date("d", mktime(0, 0, 0, $mes + 1, 0, $any));
                        for ($dia = 1; $dia <= $dias_del_mes; $dia++) {
                            $letras = strtoupper(substr(date("D", mktime(0, 0, 0, $mes, $dia, $any)), 0, 2));
                            if (in_array($letras, $dias_w_db)) {
                                // Me salto los dias del mes anteriores a la fecha de inicio y los posteriores a la de fin
                                if (($dia < $day_ini && $mes == $month_ini && $any == $any_ini) || ($dia > $day_fin && $mes == $month_fin && $any == $any_fin)) {
                                    break;
                                }
                                $f_recurrencias[] = "$any-$mes-$dia";
                            }
                        }
                        break;
                    case "todos":
                        $dias_del_mes = date("d", mktime(0, 0, 0, $mes + 1, 0, $any));
                        for ($dia = 1; $dia <= $dias_del_mes; $dia++) {
                            // Me salto los dias del mes anteriores a la fecha de inicio y los posteriores a la de fin
                            if (($dia < $day_ini && $mes == $month_ini && $any == $any_ini) || ($dia > $day_fin && $mes == $month_fin && $any == $any_fin)) {
                                continue;
                            }
                            $f_recurrencias[] = "$any-$mes-$dia";
                        }
                        break;
                    default:
                        $err_switch = sprintf(_("opción no definida en switch en %s, linea %s"), __FILE__, __LINE__);
                        exit ($err_switch);
                }
            }
        }
        /*********** Ordenar  **********************/
        if (count($f_recurrencias)) {
            // ordenar por f_plazo:
            $a_fechas = [];
            foreach ($f_recurrencias as $f_iso) {
                $oFecha = new DateTimeLocal($f_iso);
                $f_sin_separador = $oFecha->format('Ymd');
                $a_fechas[$f_sin_separador] = $f_iso;
            }
            ksort($a_fechas);
            return $a_fechas;
        } else {
            return array();
        }
    }


    /**
     *
     * Deshago un RRULE del calendario y develvo un vector con los
     *  valores necesarios para dar las opciones en el formulario web.
     *
     */
    public static function desmontar_rule($rrule)
    {
        $rta = [];
        $error = 0;
        $meses = '';
        $dias = '';
        $dia_semana = '';
        $freq = '';
        $interval = '';
        $reglas = explode(";", $rrule);
        foreach ($reglas as $regla) {
            if (empty($regla)) {
                continue;
            }
            list($opcion, $param) = explode("=", $regla);
            switch ($opcion) {
                case "FREQ":
                    $freq = $param;
                    break;
                case "INTERVAL":
                    $interval = $param;
                    break;
                case "BYMONTHDAY":
                    $dias = $param;
                    break;
                case "BYMONTH":
                    $meses = $param;
                    break;
                case "BYDAY":
                    $dia_semana = $param;
                    break;
                case "UNTIL":
                    $f_until = $param;
                    break;
                default:
                    $err_switch = sprintf(_("opción no definida en switch en %s, linea %s"), __FILE__, __LINE__);
                    exit ($err_switch);
            }
        }
        if (!empty($f_until)) {
            $rta['until'] = $f_until;
        }

        if (!$error && $freq == "YEARLY" && !$dias && !$meses && !$dia_semana) {
            $rta['tipo'] = "d_a";
            $rta['tipo_dia'] = "num_ini";
            $rta['dias'] = "";
        }
        if (!$error && $freq == "YEARLY" && $dias && $meses && !$dia_semana) {
            $rta['tipo'] = "d_a";
            $rta['tipo_dia'] = "num";
            $rta['meses'] = $meses;
            $rta['dias'] = $dias;
        }
        if (!$error && $freq == "YEARLY" && $dia_semana && !$dias && $meses) {
            $rta['tipo'] = "d_a";
            $rta['tipo_dia'] = "ref";
            $matches = [];
            preg_match('/([\+\-]*)(\d)(\w\w)/', $dia_semana, $matches);
            //Array ( [0] => -1SU [1] => - [2] => 1 [3] => SU )
            $signo = $matches[1];
            $rta['ordinal'] = $signo . $matches[2];
            $rta['dia_semana'] = $matches[3];
            $rta['meses'] = $meses;
        }
        //if (!$error && $freq=="DAILY" && $dias && $meses) {
        if (!$error && $freq == "YEARLY" && $dias && $meses) {
            $rta['tipo'] = "d_a";
            $rta['tipo_dia'] = "num_dm";
            $rta['meses'] = $meses;
            $rta['dias'] = $dias;
            $rta['interval'] = $interval;
        }
        if (!$error && $freq == "MONTHLY" && !$dias && !$meses && !$dia_semana) {
            $rta['tipo'] = "d_m";
            $rta['tipo_dia'] = "num_ini";
            $rta['dias'] = "";
        }
        if (!$error && $freq == "MONTHLY" && $dias && !$meses) {
            $rta['tipo'] = "d_m";
            $rta['tipo_dia'] = "num";
            $rta['dias'] = $dias;
        }
        if (!$error && $freq == "MONTHLY" && $dia_semana && !$dias && !$meses) {
            $rta['tipo'] = "d_m";
            $rta['tipo_dia'] = "ref";
            preg_match('/([\+\-]*)(\d)(\w\w)/', $dia_semana, $matches);
            //Array ( [0] => -1SU [1] => - [2] => 1 [3] => SU )
            $signo = $matches[1];
            $rta['ordinal'] = $signo . $matches[2];
            $rta['dia_semana'] = $matches[3];
        }
        if (!$error && $freq == "WEEKLY" && !$dia_semana && !$meses) {
            $rta['tipo'] = "d_s";
            $rta['tipo_dia'] = "num_ini";
        }
        if (!$error && $freq == "WEEKLY" && $dia_semana && !$meses) {
            $rta['tipo'] = "d_s";
            $rta['tipo_dia'] = "ref";
            $rta['dias'] = $dia_semana;
        }
        if (!$error && $freq == "DAILY" && !$dias && !$meses) {
            $rta['tipo'] = "d_d";
            $rta['meses'] = "";
            $rta['dias'] = "";
        }
        return $rta;
    }

    /**
     *
     * Genero la RRULE para el calendario, a partir de un vector con los
     *  valores del formulario web.
     *
     */
    public static function montar_rrule($request)
    {
        switch ($request['tipo']) {
            case "d_a":
                switch ($request['tipo_dia']) {
                    case "num_ini":
                        $rrule = "FREQ=YEARLY";
                        break;
                    case "num":
                        $meses = $request['meses'];
                        if ($request['dias'] && $meses) {
                            $rrule = "FREQ=YEARLY;BYMONTHDAY={$request['dias']};BYMONTH=$meses";
                        } else {
                            $rrule = "";
                        }
                        break;
                    case "ref":
                        $meses = $request['meses'];
                        $ordinal = $request['ordinal'];
                        if ($ordinal > 0) {
                            $ordinal = "+" . $ordinal;
                        } else {
                            $ordinal = "-" . $ordinal;
                        }
                        $dia_semana = $request['dia_semana'];
                        if ($dia_semana && $meses) {
                            $rrule = "FREQ=YEARLY;BYDAY=$ordinal$dia_semana;BYMONTH=$meses";
                        } else {
                            $rrule = "";
                        }
                        break;
                    case "num_dm":
                        if (!empty($request['interval'])) {
                            $rrule = "FREQ=YEARLY;INTERVAL={$request['interval']}";
                        } else {
                            $rrule = "FREQ=YEARLY";
                        }
                        $dias = implode(",", $request['dias']);
                        $meses = implode(",", $request['meses']);
                        if ($dias || $meses) {
                            $rrule .= ";BYMONTH=$meses;BYMONTHDAY=$dias";
                        } else {
                            $rrule = "";
                        }
                        break;
                    default:
                        $err_switch = sprintf(_("opción no definida en switch en %s, linea %s"), __FILE__, __LINE__);
                        exit ($err_switch);
                }
                break;
            case "d_m":
                switch ($request['tipo_dia']) {
                    case "num_ini":
                    case "num":
                        if (!empty($request['dias'])) {
                            $rrule = "FREQ=MONTHLY;BYMONTHDAY={$request['dias']}";
                        } else {
                            $rrule = "";
                        }
                        break;
                    case "ref":
                        $ordinal = $request['ordinal'];
                        if ($ordinal > 0) {
                            $ordinal = "+" . $ordinal;
                        } else {
                            $ordinal = "-" . $ordinal;
                        }
                        $dia_semana = $request['dia_semana'];
                        if (!empty($dia_semana)) {
                            $rrule = "FREQ=MONTHLY;BYDAY=$ordinal$dia_semana";
                        } else {
                            $rrule = "";
                        }
                        break;
                    default:
                        $err_switch = sprintf(_("opción no definida en switch en %s, linea %s"), __FILE__, __LINE__);
                        exit ($err_switch);
                }
                break;
            case "d_s":
                switch ($request['tipo_dia']) {
                    case "num_ini":
                        $rrule = "FREQ=WEEKLY";
                        break;
                    case "ref":
                        $dias = implode(",", $request['dias']);
                        if (!empty($dias)) {
                            $rrule = "FREQ=WEEKLY;BYDAY=$dias";
                        } else {
                            $rrule = "";
                        }
                        break;
                    default:
                        $err_switch = sprintf(_("opción no definida en switch en %s, linea %s"), __FILE__, __LINE__);
                        exit ($err_switch);
                }
                break;
            case "d_d":
                $rrule = "FREQ=DAILY";
                break;
            default:
                $err_switch = sprintf(_("opción no definida en switch en %s, linea %s"), __FILE__, __LINE__);
                exit ($err_switch);
        }
        if (!empty($request['until'])) {
            list($d_f_until, $m_f_until, $a_f_until) = preg_split('/[\.\/-]/', $request['until']);
            $f_cal_until = date("Ymd", mktime(0, 0, 0, $m_f_until, $d_f_until, $a_f_until));
            $rrule .= ";UNTIL=$f_cal_until";
        }

        return $rrule;
    }


}