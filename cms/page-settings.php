<?php
$pageTitle = "Configuración de la página";
$menuActive = 5; ?>
<? include("header.php"); ?> <?
if(!permit($db, $_SESSION['per_pk'], '4'))
{
	echo "<script type=\"text/javascript\"> alert('Acceso denegado.'); window.history.back(); </script>";
	exit();
}
if($_SERVER['REQUEST_METHOD']=='POST' && isset($_POST['siteName']))
{
	$_POST['analytics'] = str_replace("'", "\\'", $_POST['analytics']);
	if($db->update('configuracion', "nombre_sitio='".$_POST['siteName']."', correo_contacto='".$_POST['recipientsEmail']."', twitter='".$_POST['twitter']."', facebook='".$_POST['facebook']."', analytics='".$_POST['analytics']."'", '1=1 LIMIT 1'))
	{
		echo '<script type="text/javascript"> alert(\'La información ha sido actualizada.\'); </script>';
	}
	else
	{
		echo '<script type="text/javascript"> alert(\'No fue posible actualizar toda la información.\'); </script>';
	}
}
$row = $db->fetch_array($db->select('*', 'configuracion LIMIT 0,1'));
?>
<header>
	<h1>Configuración de la página</h1>
</header>
<div class="contentPane">
	<form id="pageSetForm" name="pageSetForm" action="<?= $_SERVER['PHP_SELF']; ?>" method="post">
		<table class="formTable">
			<tr>
				<th>Nombre de la página:</th>
				<td><input type="text" id="siteName" name="siteName" size="45" class="validate[required]" value="<?= $row['nombre_sitio']; ?>"></td>
			</tr>
			<tr>
				<th><span class="required">*</span> Correo de contacto:</th>
				<td><input type="text" id="recipientsEmail" name="recipientsEmail" size="45" class="validate[required,email]" value="<?= $row['correo_contacto']; ?>"></td>
			</tr>
			<tr>
				<th>Usuario de Twitter:</th>
				<td><input type="text" id="twitter" name="twitter" size="45" value="<?= $row['twitter']; ?>"></td>
			</tr>
			<tr>
				<th>Página de Facebook:</th>
				<td><input type="text" id="facebook" name="facebook" size="45" value="<?= $row['facebook']; ?>"></td>
			</tr>
			<tr>
				<th>Google Analytics:</th>
				<td><textarea id="analytics" name="analytics" cols="100" rows="8"><?= $row['analytics']; ?></textarea></td>
			</tr>
			<tr>
				<th>&nbsp;</th>
				<td>
					<input type="submit" value="Aceptar">
				</td>
			</tr>
		</table>
	</form>
</div>
<script type="text/javascript" src="js/jquery.validationEngine-es.js"></script>
<script type="text/javascript" src="js/jquery.validationEngine.js"></script>
<script type="text/javascript">
	$(document).ready(function(){
		$("#pageSetForm").validationEngine();
	});
</script>
<? include("footer.php"); ?>