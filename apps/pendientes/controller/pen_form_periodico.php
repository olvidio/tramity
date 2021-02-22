<?php
use web\DateTimeLocal;
use pendientes\model\Rrule;

/***
*  En $rta tengo los parametros que me sirven para ver-no ver las pestañas del periodico
*
*
*/
$rta='';
$chk_m_ref='';
$chk_m_num='';
$chk_m_num_ini='';
$chk_a_num_ini="";
$chk_a_num="";
$chk_a_ref="";
$chk_a_num_dm="";
$dia_w_a_db='';
$chk_s_num_ini='';
$chk_s_ref='';
$chk_d_num_ini='';
$dias_w_db='';
$ordinal_db='';
$dia_w_db='';
$dia_num_db='';
$mes_num_db='';
$ordinal_a_db='';
$mes_num_ref_db='';
if (empty($rrule)) {
	$f_inicio='';
	$f_until='';
	$exdates='';
	$periodico_tipo='';
} else {
	$rta = Rrule::desmontar_rule($rrule);
	if (!empty($rta['until'])) {
	    $f_until_iso = $rta['until']; //(esta en iso)
	    $oF_ini = new DateTimeLocal($f_until_iso);
	    $f_until = $oF_ini->getFromLocal();
	} else {
        $f_until='';
	}
	switch ($rta['tipo']) {
		case "d_a":
			$display_d_a="display:in-line;";
			$periodico_tipo="periodico_d_a";
			switch ($rta['tipo_dia']) {
				case "num_ini":
					$chk_a_num_ini="checked";
					$chk_a_num="";
					$chk_a_ref="";
					$chk_a_num_dm="";
				break;
				case "num":
					$chk_a_num_ini="";
					$chk_a_num="checked";
					$chk_a_ref="";
					$chk_a_num_dm="";
					$mes_num_db=empty($rta['meses'])? '' : $rta['meses'];
					$dia_num_db=empty($rta['dias'])? '' : $rta['dias'];
				break;
				case "ref":
					$chk_a_num_ini="";
					$chk_a_num="";
					$chk_a_ref="checked";
					$chk_a_num_dm="";
					$dia_w_a_db=empty($rta['dia_semana'])? '' : $rta['dia_semana'];
					$ordinal_a_db=empty($rta['ordinal'])? '' : $rta['ordinal'];
					$mes_num_ref_db=empty($rta['meses'])? '' : $rta['meses'];
				break;
				case "num_dm":
					$chk_a_num_ini="";
					$chk_a_num="";
					$chk_a_ref="";
					$chk_a_num_dm="checked";
					$meses_db=empty($rta['meses'])? '' : preg_split('/,/',$rta['meses']);
					$dias_db=empty($rta['dias'])? '' : preg_split('/,/',$rta['dias']);
				break;
			}
		break;
		case "d_m":
			$meses_db=empty($rta['meses'])? '' : preg_split('/,/',$rta['meses']);
			$dias_db=empty($rta['dias'])? '' : preg_split('/,/',$rta['dias']);
			$display_d_m="display:in-line;";
			$periodico_tipo="periodico_d_m";
			switch ($rta['tipo_dia']) {
				case "num_ini":
					$chk_m_num_ini="checked";
					$chk_m_num="";
					$chk_m_ref="";
				break;
				case "num":
					$chk_m_num_ini="";
					$chk_m_num="checked";
					$chk_m_ref="";
					$dia_num_db=$rta['dias'];
				break;
				case "ref":
					$chk_m_num_ini="";
					$chk_m_num="";
					$chk_m_ref="checked";
					$dia_w_db=empty($rta['dia_semana'])? '' : $rta['dia_semana'];
					$ordinal_db=empty($rta['ordinal'])? '' : $rta['ordinal'];
				break;
			}
		break;
		case "d_s":
			$display_d_s="display:in-line;";
			$periodico_tipo="periodico_d_s";
			switch ($rta['tipo_dia']) {
				case "num_ini":
					$chk_s_num_ini="checked";
					$chk_s_num="";
					$chk_s_ref="";
				break;
				case "ref":
					$chk_s_num_ini="";
					$chk_s_num="";
					$chk_s_ref="checked";
					$dias_w_db=empty($rta['dias'])? '' : preg_split('/,/',$rta['dias']);
				break;
			}
		break;
		case "d_d":
			$display_d_d="display:in-line;";
			$periodico_tipo="periodico_d_d";
			$chk_d_num_ini="checked";
		break;
	}
}
if (empty($display_d_a)) $display_d_a="display:none;";
if (empty($display_d_m)) $display_d_m="display:none;";
if (empty($display_d_s)) $display_d_s="display:none;";
if (empty($display_d_d)) $display_d_d="display:none;";


?>
<script>
$(function() { $( "#f_inicio" ).datepicker(); });
$(function() { $( "#f_until" ).datepicker(); });
fnjs_marcar=function(id){
	$(id).prop("checked",true);
}

fnjs_ver_tab=function(elemento){
	var tabs=[ '#periodico_d_a', '#periodico_d_m', '#periodico_d_s', '#periodico_d_d'];
	$.each(tabs,function(i,item){
			$(item).hide()
			});
	$(elemento).show();
	var el=elemento.substr(1);
	$('#periodico_tipo').val(el);
}
</script>
<!-- *********************************** PERIODICOS *************************************** -->
<div id=periodico style="<?= $display_periodico ?>">
<input type='hidden' id='periodico_tipo' name='periodico_tipo' value='<?= $periodico_tipo ?>'>


	<br><h2>Repetición</h2>
	<?php echo ucfirst(_("desde")); ?>:
		<input tabindex="100" id="f_inicio" name="f_inicio" size="12" value="<?php echo $f_inicio; ?>" class="fecha" onchange="fnjs_cambiar_estado()">
	<?php echo _("hasta"); ?>:
		<input tabindex="10" id="f_until" name="f_until" size="12" value="<?php echo $f_until; ?>" class="fecha" onchange="fnjs_cambiar_estado()">

	<table cellpadding="7" cellspacing="0" border="1" frame="box" rules="cols">
		<tr class=tab>
			<td onclick=fnjs_ver_tab('#periodico_d_a')><?= _("anual") ?></td>
			<td onclick=fnjs_ver_tab('#periodico_d_m')><?= _("mensual") ?></td>
			<td onclick=fnjs_ver_tab('#periodico_d_s')><?= _("semanal") ?></td>
			<td onclick=fnjs_ver_tab('#periodico_d_d')><?= _("diaria") ?></td>
		</tr>
	</table><br>

<!-- *********************************** Peridico dia del año *************************************** -->
<table id=periodico_d_a border=1 style="<?= $display_d_a ?>">
<col width=10%><col width=15%><col width=15%><col width=15%><col width=15%>
<tbody>
<tr><td colspan=5><input type="radio" id="id_a_radio_1" name="tipo_dia" value="num_ini" <?= $chk_a_num_ini?>><?= _("anualmente") ?>
<tr><td colspan=5><input type="radio" id="id_a_radio_2" name="tipo_dia" value="num" <?= $chk_a_num?>><?= _("El/los día/s (separados por comas)") ?>
	<input type="text" size=6 name=a_dia_num value="<?= $dia_num_db ?>" onclick="fnjs_marcar('#id_a_radio_2');"> <?= _("de cada") ?>
	<select name=mes_num onclick="fnjs_marcar('#id_a_radio_2');">
	<?php
	$nom_meses=array_meses();
	foreach ($nom_meses as $mes => $mes_txt) {
		if ($mes==$mes_num_db) {  $chk="selected"; } else { $chk=""; }
		echo "<option value=$mes $chk>$mes_txt</option>";
	}
	?>
	</select>
</td><tr>
<tr><td colspan=5><input type="radio" id="id_a_radio_3" name="tipo_dia" value="ref" <?= $chk_a_ref?>><?= _("El") ?>
	<select name=ordinal_a onclick="fnjs_marcar('#id_a_radio_3');">
		<option value=1 <?php if ($ordinal_a_db==1) echo "selected"; ?>><?= _("primer") ?></option>	
		<option value=2 <?php if ($ordinal_a_db==2) echo "selected"; ?>><?= _("segundo") ?></option>	
		<option value=3 <?php if ($ordinal_a_db==3) echo "selected"; ?>><?= _("tercer") ?></option>	
		<option value=4 <?php if ($ordinal_a_db==4) echo "selected"; ?>><?= _("cuarto") ?></option>	
		<option value="-1" <?php if ($ordinal_a_db==-1) echo "selected"; ?>><?= _("último") ?></option>	
	</select>
	<select name=dia_semana_a onclick="fnjs_marcar('#id_a_radio_3');">
	<?php
	$dias_semana=array_dias_semana();
	foreach ($dias_semana as $dia_w => $dia_txt) {
		if ($dia_w==$dia_w_a_db) {  $chk="selected"; } else { $chk=""; }
		echo "<option value=$dia_w $chk>$dia_txt</option>";
	}
		?>
	</select><?= _("de cada") ?>
	<select name=mes_num_ref onclick="fnjs_marcar('#id_a_radio_3');">
	<?php
	$nom_meses=array_meses();
	foreach ($nom_meses as $mes => $mes_txt) {
		if ($mes==$mes_num_ref_db) {  $chk="selected"; } else { $chk=""; }
		echo "<option value=$mes $chk>$mes_txt</option>";
	}
	?>
	</select>
</td><tr>
<tr>
<tr><td colspan=5><input type="radio" id="id_a_radio_4" name="tipo_dia" value="num_dm" <?= $chk_a_num_dm ?>><?php echo _("por dias de meses"); ?>:</td>
</tr>
<tr><td></td>
<td colspan=5 class=etiqueta><?php echo _("introducir los meses"); ?>:</td>
</tr>
	<?php 
		$nom_meses=array_meses();
		for ($mes=1;$mes<=6;$mes++) {
			if (empty($meses_db) || !in_array($mes, $meses_db)) {  $chk=""; } else { $chk="checked"; }
			echo "<tr><td></td><td></td><td><input type=checkbox id=meses[$mes] name=meses[$mes] value=$mes $chk onclick=\"fnjs_marcar('#id_a_radio_4');\">$nom_meses[$mes]";
			$mes_2=$mes+6;
			if (empty($meses_db) || !in_array($mes_2, $meses_db)) {  $chk=""; } else { $chk="checked"; }
			echo "</td><td><input type=checkbox id=meses[$mes_2] name=meses[$mes_2] value=$mes_2 $chk onclick=\"fnjs_marcar('#id_a_radio_4');\">$nom_meses[$mes_2]</td><td></td><td></td></tr>";
		}
		?>
</tr>
<tr><td></td>
<td colspan=5 class=etiqueta><?php echo _("introducir los días"); ?>:</td>
</tr>
	<?php 
		for ($dia=1;$dia<=10;$dia++) {
			if (empty($dias_db) || !in_array($dia, $dias_db)) {  $chk=""; } else { $chk="checked"; }
			echo "<tr><td></td><td></td><td><input type=checkbox id=dias[$dia] name=dias[$dia] value=$dia $chk onclick=\"fnjs_marcar('#id_a_radio_4');\">$dia";
			$dia_2=$dia+10;
			if (empty($dias_db) || !in_array($dia_2, $dias_db)) {  $chk=""; } else { $chk="checked"; }
			echo "</td><td><input type=checkbox id=dias[$dia_2] name=dias[$dia_2] value=$dia_2 $chk onclick=\"fnjs_marcar('#id_a_radio_4');\">$dia_2</td>";
			$dia_3=$dia+20;
			if (empty($dias_db) || !in_array($dia_3, $dias_db)) {  $chk=""; } else { $chk="checked"; }
			echo "</td><td><input type=checkbox id=dias[$dia_3] name=dias[$dia_3] value=$dia_3 $chk onclick=\"fnjs_marcar('#id_a_radio_4');\">$dia_3</td>";
			$dia_4=$dia+30;
			if (empty($dias_db) || !in_array($dia_4, $dias_db)) {  $chk=""; } else { $chk="checked"; }
			if ($dia_4 <= 31) echo "</td><td><input type=checkbox id=dias[$dia_4] name=dias[$dia_4] value=$dia_4 $chk onclick=\"fnjs_marcar('#id_a_radio_4');\">$dia_4</td></tr>";
		}
		?>
</tr>
</tbody></table>
<!-- *********************************** Periodico dia del mes *************************************** -->
<table id=periodico_d_m border=1 style="<?= $display_d_m ?>">
<col width="10%"><col width="90%">
<tbody>
<tr><td colspan=5><input type="radio" id="id_radio_1" name="tipo_dia" value="num_ini" <?= $chk_m_num_ini?>><?= _("mensualmente") ?>
<tr><td colspan=5><input type="radio" id="id_radio_2" name="tipo_dia" value="num" <?= $chk_m_num?>><?= _("El/los día/s (separados por comas)") ?>
	<input type="text" size=6 name=dia_num value="<?= $dia_num_db ?>" onclick="fnjs_marcar('#id_radio_2');"> <?= _("de cada mes") ?></td><tr>
<tr><td colspan=5><input type="radio" id="id_radio_3" name="tipo_dia" value="ref" <?= $chk_m_ref?>><?= _("El") ?>
	<select name=ordinal onclick="fnjs_marcar('#id_radio_3');">
		<option value=1 <?php if ($ordinal_db==1) echo "selected"; ?>><?= _("primer") ?></option>	
		<option value=2 <?php if ($ordinal_db==2) echo "selected"; ?>><?= _("segundo") ?></option>	
		<option value=3 <?php if ($ordinal_db==3) echo "selected"; ?>><?= _("tercer") ?></option>	
		<option value=4 <?php if ($ordinal_db==4) echo "selected"; ?>><?= _("cuarto") ?></option>	
		<option value="-1" <?php if ($ordinal_db==-1) echo "selected"; ?>><?= _("último") ?></option>	
	</select>
	<select name=dia_semana onclick="fnjs_marcar('#id_radio_3');">
	<?php
	$dias_semana=array_dias_semana();
	foreach ($dias_semana as $dia_w => $dia_txt) {
		if ($dia_w==$dia_w_db) {  $chk="selected"; } else { $chk=""; }
		echo "<option value=$dia_w $chk>$dia_txt</option>";
	}
		?>
	</select><?= _("de cada mes") ?>
</td><tr>
</tbody></table>
<!-- *********************************** Periodico dia de la semana *************************************** -->
<table id=periodico_d_s border=1 style="<?= $display_d_s ?>">
<col width="10%"><col width="90%">
<tbody>
<tr><td colspan=2><input type="radio" id="id_s_radio_1" name="tipo_dia" value="num_ini" <?= $chk_s_num_ini?>><?= _("semanalmente") ?>
<tr><td colspan=2><input type="radio" id="id_s_radio_2" name="tipo_dia" value="ref" <?= $chk_s_ref?>><?php echo _("periodicidad día de la semana"); ?>:</td>
</tr>
<tr><td></td>
<td>
<?php
$dias_semana=array_dias_semana();
foreach ($dias_semana as $dia_w => $dia_txt) {
	if (empty($dias_w_db) ||  sizeof($dias_w_db)==0 || !in_array($dia_w, $dias_w_db)) {  $chk=""; } else { $chk="checked"; }
	echo "<input type='checkbox' name='dias_w[]' value=$dia_w $chk onclick=fnjs_marcar('#id_s_radio_2'); >$dia_txt    ";
}
?>
</td>
</tbody></table>
<!-- *********************************** Periodico diario *************************************** -->
<table id=periodico_d_d border=1 style="<?= $display_d_d ?>">
<col width="10%"><col width="90%">
<tbody>
<tr><td colspan=2><input type="radio" id="id_d_radio_1" name="tipo_dia" value="num_ini" <?= $chk_d_num_ini?>><?= _("diariamente") ?>
</tr>
</tbody></table>
<!-- *********************************** Excepciones *************************************** -->
<?php
if (is_array($exdates)) {
	$a_total=array();
	foreach ($exdates as $icalprop) {
		// si hay más de uno separados por coma
		$a_fechas=preg_split('/,/',$icalprop->content);
		array_walk($a_fechas, 'fecha_sin_time'); //quito la THHMMSSZ
		$a_total=array_merge($a_total,$a_fechas);
	}
?>
	<br>
	<table><tr><td title="<?= _("Son los marcados como contestados o eliminados") ?>">
	<?= _("Excepciones") ?>: 
 	<?php 
	foreach( $a_total as $fecha) {
	?>
	<input type='text' size=10 tabindex=290 name='exdates[]' VALUE="<?= $fecha ?>" title="<?= _("formato: aaaammdd[,aaaammdd,...]") ?>">
	<?php
	}
	?>
	<input type='text' size=10 tabindex=350 name='exdates[]'>
	</td></tr></table>


<?php
}
?>
</div>
