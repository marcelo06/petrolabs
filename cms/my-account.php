<?php
$pageTitle = 'Mi cuenta';
$menuActive = 0; ?>
<? include("header.php"); ?> <?
$row = $db->fetch_array($db->select('*', 'usuario', 'usu_pk="'.$_SESSION['usu_pk'].'" LIMIT 0,1'));
if($_SERVER['REQUEST_METHOD']=='POST' && isset($_POST['usuNombre']))
{
	$values = '';
	$error = FALSE;
	if($_POST['usuNombre']!=$row['usu_nombre'])
	{
		$values .= "usu_nombre='".$_POST['usuNombre']."', ";
	}
	if($_POST['usuEmail']!=$row['usu_email'])
	{
		$values .= "usu_email='".$_POST['usuEmail']."', ";
	}
	if($values!='')
	{
		$values = mb_substr($values, 0, -2);  // Quito la última coma.
		if($db->update('usuario', $values, 'usu_pk='.$_SESSION['usu_pk']))
		{
			$row['usu_nombre'] = $_SESSION['user'] = $_POST['usuNombre'];  // Actualizo la información a mostrar, así no se haya cambiado esta variable en particular.
			$row['usu_email'] = $_POST['usuEmail'];
		}
		else
		{
			$error = TRUE;  // Sólo registro si hay errores.
		}
	}
	if(!$error)
	{
		// Si no hubo errores actualizando la información general, determino si hay que cambiar la clave.
		if($_POST['usuClave']!='' && $_POST['usuClaveNueva']!='')
		{
			$userpw = strstr($row['usu_clave'], '&')==FALSE ? md5($_POST['usuClave']) : '&'.$_POST['usuClave'];
			if($userpw!=$row['usu_clave'])
			{
				echo '<script type="text/javascript"> alert(\'';
				echo 'Has escrito la contraseña incorrectamente, por favor vuelve a intentarlo.';
				if($values!='' && !$error)
				{
					echo ' Sin embargo, la demás información de la cuenta ha sido actualizada correctamente.';
				}
				echo '\'); </script>';
			}
			else
			{
				$newuserpw = md5($_POST['usuClaveNueva']);
				if($db->update('usuario', "usu_clave='$newuserpw'", 'usu_pk="'.$_SESSION['usu_pk'].'"'))
				{
					echo '<script type="text/javascript"> alert(\'La información de la cuenta ha sido actualizada correctamente.\'); </script>';
				}
				else
				{
					echo '<script type="text/javascript"> alert(\'Ocurrió un error y no fue posible actualizar la contraseña.\'); </script>';
				}
			}
		}
		else
		{
			echo '<script type="text/javascript"> alert(\'';
			echo $values!='' ? 'La información de la cuenta ha sido actualizada correctamente.' : 'No realizaste cambios en la información de la cuenta.';
			echo '\'); </script>';
		}
	}
	else
	{
		echo '<script type="text/javascript">
			alert(\'Ocurrió un error y no fue posible actualizar la información, por favor intenta con otro nombre de usuario.\');
			window.location.replace(\''.$_SERVER['PHP_SELF'].'\'); </script>';
	}
}
?>
<header>
	<div class="fLeft">
		<h1>Mi cuenta</h1>
	</div>
	<div class="clear"></div>
</header>
<div class="contentPane">
	<form id="myAccountForm" name="myAccountForm" action="<?= $_SERVER['PHP_SELF']; ?>" method="post">
		<table class="formTable">
			<tr>
				<th style="width:150px">Usuario:</th>
				<td><strong> &nbsp;<?= $row['usu_login']; ?></strong></td>
			</tr>
			<tr>
				<th style="width:150px"><span class="required">*</span> Nombre:</th>
				<td><input type="text" id="usuNombre" name="usuNombre" size="45" class="validate[required]" value="<?= $row['usu_nombre']; ?>"></td>
			</tr>
			<tr>
				<th><span class="required">*</span> E-mail:</th>
				<td><input type="text" id="usuEmail" name="usuEmail" size="45" class="validate[required,custom[email]]" value="<?= $row['usu_email']; ?>"></td>
			</tr>
			<tr>
				<th>&nbsp;</th>
				<td><a id="aNewPass">Cambiar contraseña</a></td>
			</tr>
			<tr class="newPass" style="display:none">
				<th><span class="required">*</span> Contraseña actual:</th>
				<td><input type="password" id="usuClave" name="usuClave" size="45" class="validate[required]"></td>
			</tr>
			<tr class="newPass" style="display:none">
				<th valign="top" style="padding-top:10px"><span class="required">*</span> Contraseña nueva:</th>
				<td>
					<div class="pwdwidgetdiv" id="userPassDiv"></div>
					<noscript><input type="password" id="usuClaveNueva" name="usuClaveNueva" size="45" class="validate[required]"></noscript>
				</td>
			</tr>
			<tr>
				<th>&nbsp;</th>
				<td>
					<input type="submit" value="Guardar">
				</td>
			</tr>
		</table>
	</form>
</div>
<script type="text/javascript" src="js/jquery.validationEngine-es.js"></script>
<script type="text/javascript" src="js/jquery.validationEngine.js"></script>
<script type="text/javascript" src="js/pwdwidget.js"></script>
<script type="text/javascript">
<!--
	$(document).ready(function(){
		$("#myAccountForm").validationEngine();
		// Intercalar colores de las filas del listado
		//$('.dataTable tr:odd td').addClass('whiteTd');
		var pwdwidget = new PasswordWidget('userPassDiv','usuClaveNueva');
		pwdwidget.MakePWDWidget();

		$('#aNewPass').click(function(){
			$(this).parents('tr').hide();
			$('.newPass').show();
		});
	});
-->
</script>
<? include("footer.php"); ?>