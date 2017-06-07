<?php
include("lib/functions.php");
$conf = config();
$db = database();
if(!$db->selected_db)  // No se ha configurado la base de datos
{
	header("Location: set-database.php");
	exit();
}
if(isset($_GET['logout']) && isset($_SESSION['login']))  // Cerrar sesión
{
	unset($_SESSION['login']);
	unset($_SESSION['usu_pk']);
	unset($_SESSION['per_pk']);
	unset($_SESSION['user']);
}
if(isset($_SESSION['login']))
{
	header("Location: index.php");
	exit();
}
if($_SERVER['REQUEST_METHOD']=='POST')
{
	$success = FALSE;
	$row = $db->fetch_array($db->select("usu_pk, per_pk, usu_clave, usu_estado, usu_nombre", "usuario", "usu_login='".$_POST['username']."'"));
	if($row)
	{
		if(strstr($row['usu_clave'], '&')==false)
		{
			$pw = md5($_POST['password']);
			$changePw = FALSE;
		}
		else
		{
			$pw = "&".$_POST['password'];
			$changePw = TRUE;
		}
		if($row['usu_clave']==$pw)
		{
			if($row['usu_estado']=='1')
			{
				$rowPer = $db->fetch_array($db->select("per_estado", "perfil", "per_pk='".$row['per_pk']."'"));
				if($rowPer['per_estado']=='1')
				{
					$_SESSION['login'] = $_POST['username'];
					$_SESSION['usu_pk'] = $row['usu_pk'];
					$_SESSION['per_pk'] = $row['per_pk'];
					$_SESSION['user'] = $row['usu_nombre'];
					if($changePw)
					{
						$msg = 'Recuerda cambiar tu contraseña.';
					}
					$success = TRUE;
				}
				else
				{
					$msg = 'Perfil de usuario inactivo: acceso denegado.';
				}
			}
			else
			{
				$msg = 'Usuario inactivo: acceso denegado.';
			}
		}
		else
		{
			$msg = 'Error en nombre de usuario y/o contraseña.';
		}
	}
	else
	{
		$msg = 'Error en nombre de usuario y/o contraseña.';
	}
}
$db->disconnect(); ?>
<!doctype html>
<html>
	<head>
		<meta charset="utf-8">
		<title>Iniciar sesi&oacute;n - Easy website</title>
		<link href='http://fonts.googleapis.com/css?family=Cuprum' rel='stylesheet' type='text/css'>
		<link rel="stylesheet" type="text/css" href="css/validationEngine.jquery.css">
		<link rel="stylesheet" type="text/css" href="css/style.css">
		<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
		<script type="text/javascript" src="js/jquery.validationEngine-es.js"></script>
		<script type="text/javascript" src="js/jquery.validationEngine.js"></script>
		<script type="text/javascript">
			$(document).ready(function(){
				$("#loginForm").validationEngine();
			});
		</script> <?
		if($_SERVER['REQUEST_METHOD']=='POST')
		{
			if($success)
			{
				if($changePw)
				{
					echo "<script type=\"text/javascript\"> alert('".$msg."'); </script>";
				}
				echo "<script type=\"text/javascript\"> window.location = 'index.php'; </script>";
				exit();
			}
			echo "<script type=\"text/javascript\"> alert('".$msg."'); </script>";
		} ?>
	</head>
	<body>
		<div id="loginPanel">
			<div id="lContainer">
				<header><img src="images/easy-website.png" alt="" /></header>
				<section id="cLogin">
					<h1>Iniciar sesi&oacute;n en easyWebsite</h1>
					<form id="loginForm" name="loginForm" action="<?= $_SERVER['PHP_SELF']; ?>" method="post">
						<table align="center">
							<tr>
								<th><span class="required">*</span> Nombre de usuario:</th>
								<td><input type="text" id="username" name="username" size="50" class="validate[required]"></td>
							</tr>
							<tr>
								<th><span class="required">*</span> Contrase&ntilde;a:</th>
								<td><input type="password" id="password" name="password" size="50" class="validate[required]"></td>
							</tr>
							<tr>
								<th class="noPadding">&nbsp;</th>
								<td class="noPadding">
									<a href="password-recovery.php">¿Olvidaste la contraseña?</a>
									<div class="clear10"></div>
								</td>
							</tr>
							<tr>
								<th>&nbsp;</th>
								<td><input type="submit" value="Iniciar Sesi&oacute;n"></td>
							</tr>
						</table>
					</form>
				</section>
			</div>
			<div id="pushFooter"></div>
		</div>
		<footer>
			<div class="footerContent">
				<div class="fLeft paddingL10px">
					<a href="#">Preguntas Frecuentes</a> |
					<a href="#">Soporte</a> |
					<a href="#">Planes y Precios</a> |
					<a href="#">Facebook</a> |
					<a href="#">Twitter</a> |
					<a href="#">Sitio Web</a>
				</div>
				<div class="fRight paddingR10px">
					<a href="#">T&eacute;rminos del servicio</a> |
					<a href="#">Pol&iacute;tica de privacidad</a>
				</div>
				<div class="separator"></div>
				<div class="credits">
					Copyright <?= date('Y'); ?> - Todos los derechos reservados<br />
					easyWebsite es un producto de <a href="http://www.haggen-it.com" target="_blank" title="">www.haggen-it.com</a>
				</div>
				<div class="clear"></div>
			</div>
		</footer>
	</body>
</html>