<?php

$txt_err = '';
$sessionName = session_name();
if (isset($_COOKIE[$sessionName])) {
    $txt_err .= ''; // "active";
} else {
    $txt_err .= "no active";
}
if (empty($txt_err)) {
    $jsondata['success'] = true;
    $jsondata['mensaje'] = 'ok';
} else {
    $jsondata['success'] = false;
    $jsondata['mensaje'] = $txt_err;
}

//Aunque el content-type no sea un problema en la mayoría de casos, es recomendable especificarlo
header('Content-type: application/json; charset=utf-8');
echo json_encode($jsondata);
exit();