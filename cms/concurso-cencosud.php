<?
$pageTitle = "Concurso Cencosud";
$menuActive = 3; ?>
<? include("header.php"); ?> <?

// Array de idiomas
$result = $db->select('idi_pk, idi_locale, idi_txt', 'idioma', "idi_estado='1' ORDER BY idi_pk");
$numLanguages = $db->num_rows($result);
while($row=$db->fetch_array($result))
{
	$id = $row['idi_pk'];
	$languages[$id] = array('name'=>$row['idi_txt'], 'abbreviation'=>$row['idi_locale']);
	$titText[$id] = $numLanguages>1 ? "Título ($row[idi_locale])" : 'Título';
	$descText[$id] = $numLanguages>1 ? "Descripción ($row[idi_locale])" : 'Descripción';
}

$thumbnail = '120x90';

if($_SERVER['REQUEST_METHOD']=='POST')
{
	if(isset($_POST['edsList']))
	{
		$edsList = explode("\n", $_POST['eds']);
		$eds = array();
		foreach($edsList as $e)
		{
			$e = trim($e);
			if($e!='')
				$eds[] = $e;  // Elimino líneas en blanco.
		}
		if($db->update('configuracion', 'eds_cencosud="'.implode("\n", $eds).'"', '1=1 LIMIT 1'))
			echo "<script type=\"text/javascript\" language=\"javascript\"> alert('La información ha sido actualizada.') </script>";
		else
			echo "<script type=\"text/javascript\" language=\"javascript\"> alert('No fue posible actualizar toda la información.') </script>";
	}
	elseif(isset($_POST['form']) && $_POST['form']=='update')
	{
		$values = "estado='".$_POST['estado']."'";
		if($_POST['premio']!='')
			$values .= ", premio='".$_POST['premio']."'";
		if($db->update('concurso_cencosud', $values, 'con_pk="'.$_POST['con_pk'].'"'))
			echo "<script type=\"text/javascript\" language=\"javascript\"> alert('La información ha sido actualizada.') </script>";
		else
			echo "<script type=\"text/javascript\" language=\"javascript\"> alert('No fue posible actualizar toda la información.') </script>";
	}
}

$rowConf = $db->fetch_array($db->select('eds_cencosud', 'configuracion LIMIT 0,1'));
$rowCon = $db->fetch_all($db->select('*', 'concurso_cencosud ORDER BY fecha DESC'));

$pozo = 2400000;
if(count($rowCon)>0)
{
	foreach($rowCon as $r)
	{
		if($r['tipo']=='v' && $r['estado']=='a')
		{
			$pozo -= (int)$r['premio'];
		}
	}
} ?>

<header>
	<h1>Concurso Cencosud - Pozo disponible: $<?= number_format($pozo, 0, '', '.'); ?></h1>
</header> <?
if($_SERVER['REQUEST_METHOD']=='GET' && isset($_GET['interfaz']))
{
	$rowPk = $db->fetch_array($db->select('*', 'concurso_cencosud', 'con_pk="'.$_GET['con_pk'].'"')); ?>
	<div class="contentPane">
		<form id="editContest" name="editContest" action="<?= $_SERVER['PHP_SELF']; ?>" method="post">
			<input type="hidden" name="form" value="update">
			<input type="hidden" name="con_pk" value="<?= $rowPk['con_pk']; ?>">
			<table class="formTable">
				<tr>
					<td></td>
					<td><strong>GESTIONAR SOLICITUD DE PREMIO</strong></td>
				</tr>
				<tr>
					<th>Fecha y hora:</th>
					<td><strong><?= $rowPk['fecha']; ?></strong></td>
				</tr>
				<tr>
					<th>EDS:</th>
					<td><strong><?= $rowPk['eds']; ?></strong></td>
				</tr>
				<tr>
					<th>Registrado por:</th>
					<td><strong><?= $rowPk['nombre']; ?></strong></td>
				</tr>
				<tr>
					<th>Ventas:</th>
					<td><strong><?= $rowPk['ventas']; ?> Unds.</strong></td>
				</tr>
				<tr>
					<th>Estado:</th>
					<td>
						<label><input type="radio" name="estado" value="a"<? if($rowPk['estado']=='a') echo ' checked="checked"'; ?>> <span style="color:#090;">Aprobado</span></label>
						&nbsp; &nbsp; &nbsp;
						<label><input type="radio" name="estado" value="p"<? if($rowPk['estado']=='p') echo ' checked="checked"'; ?>> <span style="color:#F5821D;">Pendiente</span></label>
						&nbsp; &nbsp; &nbsp;
						<label><input type="radio" name="estado" value="r"<? if($rowPk['estado']=='r') echo ' checked="checked"'; ?>> <span style="color:#F00;">Rechazado</span></label>
					</td>
				</tr>
				<tr>
					<th>Premio:</th>
					<td><input type="text" id="premio" name="premio" size="45" maxlength="8" class="validate[required]" value="<?= $rowPk['premio']; ?>"></td>
				</tr>
				<tr>
					<th>&nbsp;</th>
					<td>
						<input type="submit" value="Guardar" onclick="return validate(this.form);">
					</td>
				</tr>
			</table>
		</form>
		<script type="text/javascript">
			function validate(form) {
				if(form.estado.value=='a' && (form.premio.value=='' || form.premio.value=='0')){
					alert('Debe ingresar un valor de premio si desea aprobar la solicitud.');
					form.premio.focus();
					return(false);
				}
				if(form.estado.value!='a' && form.premio.value!=''){
					alert('Solo las solicitudes aprobadas pueden llevar un valor de premio asociado.');
					form.premio.focus();
					return(false);
				}
				return(true);
			}
		</script>
	</div>
	<div class="clear10"></div> <?
} ?>
<div class="clear10"></div>
<div class="contentPane">
	<form name="edsList" action="<?= $_SERVER['PHP_SELF']; ?>" method="post">
		<table class="formTable">
			<tr>
				<th>
					EDS aprobadas:<br>
					<em>Poner una EDS por cada línea.</em>
				</th>
				<td>
					<textarea id="eds" name="eds" cols="70" rows="8"><?= $rowConf['eds_cencosud']; ?></textarea>
				</td>
			</tr>
			<tr>
				<td></td>
				<td>
					<input type="submit" id="edsList" name="edsList" value="Actualizar lista">
				</td>
			</tr>
		</table>
	</form>
</div>
<div class="clear10"></div>
<div class="contentPane">
	<h1>Solicitudes de premio</h1>
	<table width="100%" cellspacing="0" cellpadding="0" class="dataTable">
		<tr>
			<th><strong>Fecha y hora</strong></th>
			<td><strong>EDS</strong></td>
			<th style="text-align: center;"><strong>Registrado por</strong></th>
			<td style="text-align: center;"><strong>Correo</strong></td>
			<th style="text-align: center;"><strong>Ventas</strong></th>
			<td style="text-align: center;"><strong>Estado</strong></td>
			<th style="text-align: center;"><strong>Premio</strong></th>
			<td style="text-align: center;"><strong>Acciones</strong></td>
		</tr> <?
		if(count($rowCon)>0)
		{
			foreach($rowCon as $r)
			{
				if($r['tipo']=='v')
				{ ?>
					<tr>
						<td><?= $r['fecha']; ?></td>
						<td><?= $r['eds']; ?></td>
						<td><?= $r['nombre']; ?></td>
						<td><?= $r['correo']; ?></td>
						<td style="text-align: center;"><?= $r['ventas']; ?> Unds.</td>
						<td><?
							switch($r['estado'])
							{
								case 'a':
									echo '<span style="color:#090;">Aprobado</span>'; break;
								case 'r':
									echo '<span style="color:#F00;">Rechazado</span>'; break;
								default:
									echo '<span style="color:#F5821D;">Pendiente</span>';
							} ?>
						</td>
						<td style="text-align: right;">$<?= number_format($r['premio'], 0, '', '.'); ?></td>
						<td><a href="?interfaz=update&amp;con_pk=<?= $r['con_pk']; ?>" class="blueLink"><span class="icon">V</span> Editar</a></td>
					</tr> <?
				}
			}
		}
		else
		{ ?>
			<tr><td colspan="8"><div align="center">No se han registrado solicitudes de inscripción.</div></td></tr> <?
		} ?>
	</table>
</div>
<script type="text/javascript">
<!--
	$(document).ready(function(){
	});
-->
</script>
<? include("footer.php"); ?>