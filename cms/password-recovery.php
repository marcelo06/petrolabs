<?php
include_once("lib/functions.php");
$conf = config(); ?>
<!doctype html>
<html>
	<head>
		<meta charset="utf-8">
		<title>Recuperar contraseña - Easy website</title>
		<link rel="stylesheet" type="text/css" href="css/validationEngine.jquery.css">
		<link rel="stylesheet" type="text/css" href="css/style.css">
		<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
		<script type="text/javascript" src="js/jquery.validationEngine-es.js"></script>
		<script type="text/javascript" src="js/jquery.validationEngine.js"></script>
	</head>
	<body> <?php
		if($_SERVER['REQUEST_METHOD']=='POST' && isset($_POST['login']) && isset($_POST['email']))
		{
			if($_POST['login']!='' || $_POST['email']!='')
			{
				$db = database();  // Instancio la bd ahora que la necesito.
				if($_POST['login']!='')
				{
					$_POST['login'] = mysql_real_escape_string($_POST['login']);
					$row = $db->fetch_array($db->select('usu_pk,usu_nombre,usu_email', 'usuario', 'usu_login="'.$_POST['login'].'" AND usu_estado="1" LIMIT 0,1'));
					if(!$row)
					{
						echo '<script type="text/javascript"> alert(\'No encontramos tu nombre de usuario en nuestros registros.\'); </script>';
					}
				}
				else
				{
					$_POST['email'] = mysql_real_escape_string($_POST['email']);
					$row = $db->fetch_array($db->select('usu_pk,usu_nombre,usu_email', 'usuario', 'usu_email="'.$_POST['email'].'" AND usu_estado="1" LIMIT 0,1'));
					if(!$row)
					{
						echo '<script type="text/javascript"> alert(\'No encontramos tu e-mail en nuestros registros.\'); </script>';
					}
				}
				if($row)
				{
					// Genero la contraseña de forma aleatoria.
					$chars = "abcdefghijkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXY23456789";
					$password = '';
					for($i=0; $i<8; $i++)
					{
						srand((double)microtime()*1000000);
						$password .= $chars[rand(0, mb_strlen($chars)-1)];
					}
					if($db->update('usuario', 'usu_clave="&'.$password.'"', 'usu_pk="'.$row['usu_pk'].'"'))
					{
						// Cargo la plantilla del correo.
						$body = file_get_contents('email-password-recovery.php');
						$body = str_replace('[USU_NOMBRE]', $row['usu_nombre'], $body);
						$body = str_replace('[DOMAIN]', $conf['settings']->domain, $body);
						$body = str_replace('[PASSWORD]', $password, $body);
						// Preparo el envío del correo.
						$rowConf = $db->fetch_array($db->select('correo_contacto', 'configuracion LIMIT 0,1'));
						$headers ='X-Mailer:PHP/'.phpversion().'\n MIME-Version: 1.0'."\r\n".'Content-type: text/html;'."\r\n";
						if(mail($row['usu_email'], utf8_decode('Respuesta a solicitud de contraseña.'), $body, "From:".$rowConf['correo_contacto']."\r\n".$headers))  // Correo para el cliente.
						{
							echo '<script type="text/javascript">
								alert(\'Una contraseña nueva ha sido enviada a tu e-mail. Por favor revisa tu correo no deseado si no encuentras el mensaje en la bandeja de entrada.\');
								window.location.replace(\'login.php\'); </script>';
						}
						else
						{
							echo '<script type="text/javascript"> alert(\'Ocurrió un error y no pudimos enviarte la contraseña a tu e-mail. Por favor inténtalo nuevamente.\'); </script>';
						}
					}
					else
					{
						echo '<script type="text/javascript"> alert(\'Falló el proceso para obtener la contraseña. Por favor inténtalo nuevamente.\'); </script>';
					}
				}
			}
			else
			{
				echo '<script type="text/javascript"> alert(\'Debes digitar tu nombre de usuario o tu e-mail.\'); </script>';
			}
		} ?>
		<div id="loginPanel">
			<div id="lContainer">
				<header><img src="images/easy-website.png" alt="" /></header>
				<section id="cLogin">
					<h1>Recordar contraseña de easyWebsite</h1>
					<form id="resetForm" name="resetForm" action="<?= $_SERVER['PHP_SELF']; ?>" method="post">
						<table align="center">
							<tr>
								<th>Nombre de usuario:</th>
								<td><input type="text" id="login" name="login" size="50" class="validate[groupRequired[resetpw],custom[onlyLetterNumber]]"></td>
							</tr>
							<tr>
								<th>E-mail:</th>
								<td><input type="text" id="email" name="email" size="50" class="validate[groupRequired[resetpw],custom[email]]"></td>
							</tr>
							<tr>
								<th>&nbsp;</th>
								<td>
									<input type="submit" value="Aceptar">
									<a href="login.php"><span class="icon" style="font-size:16px"><</span> Iniciar sesión</a>
								</td>
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
					easyWebsite es un producto de <a href="http://www.haggen-it.com" target="_blank" title="Haggen IT">www.haggen-it.com</a>
				</div>
				<div class="clear"></div>
			</div>
		</footer>
		<script type="text/javascript">
		<!--
			$(document).ready(function(){
				$('#resetForm').validationEngine();
			});
		-->
		</script>
	</body>
</html>