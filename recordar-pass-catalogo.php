<?php
include("lib/functions.php");
session_start();
if(isset($_GET['lang']))
	$_SESSION['lang'] = $_GET['lang'];
if(!isset($_SESSION['lang']))
	$_SESSION['lang'] = '1';
$db = database();
include("lang/".language($db).".php");
$db->disconnect();
$pageTitle = 'Recordar contraseña' ?> <?
include("header.php");
include("lib/class.phpmailer.php"); ?>

<script type="text/javascript">
	$(document).ready(function(e) {
		$('.whiteBg').addClass('bgCatalogo');
	});
</script>
<? 
	function generaPass()
	{
	    //Se define una cadena de caractares. Te recomiendo que uses esta.
	    $cadena = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890';
	    //Obtenemos la longitud de la cadena de caracteres
	    $longitudCadena=strlen($cadena);
	     
	    //Se define la variable que va a contener la contraseña
	    $pass = "";
	    //Se define la longitud de la contraseña, en mi caso 10, pero puedes poner la longitud que quieras
	    $longitudPass=10;
	     
	    //Creamos la contraseña
	    for($i=1 ; $i<=$longitudPass ; $i++){
	        //Definimos numero aleatorio entre 0 y la longitud de la cadena de caracteres-1
	        $pos=rand(0,$longitudCadena-1);
	     
	        //Vamos formando la contraseña en cada iteraccion del bucle, añadiendo a la cadena $pass la letra correspondiente a la posicion $pos en la cadena de caracteres definida.
	        $pass .= substr($cadena,$pos,1);
	    }
	    return $pass;
	}


	if($_SERVER['REQUEST_METHOD']=='POST') 
	{
		$conf = config();
		$newpass = generaPass();

		$host= 'mail.'.$conf['settings']->domain;
		$origen = $conf['settings']->sendersEmail;
		$password = $conf['settings']->pw;
		$destinatario = $_POST['username']; 
		$user = array();
		$condi = "email = '".$destinatario."'";

		
		$numRows = $db->num_rows($db->select('*','eds_users', $condi));

		if ($numRows>0) 
		{
			$user = $db->fetch_assoc($db->select('*','eds_users', $condi));

			
				$mail = new PHPMailer();
				$mail->Mailer = 'smtp';
				$mail->Host = $host ;
				$mail->Port = 465;
				$mail->SMTPSecure = "ssl";
				$mail->SMTPAuth = true;
				$mail->From = $origen;
				$mail->FromName = 'Petrolabs';
				$mail->Username = $origen;
				$mail->Password = $password;
				
				$mail->WordWrap = 65;
				$mail->Timeout=30;
				$mail->Subject = 'Mensaje de restauracion enviado desde '.$_SERVER['HTTP_HOST'];
				$body = ' 
			<!DOCTYPE html>
			<html>
			<head>
				<meta charset="utf-8">
				<meta http-equiv="X-UA-Compatible" content="IE=edge">
				<title> Recuperaci&oacuten de Contrase&ntildea</title>
				<style type="text/css" media="screen">

					section{margin: 0 auto; width: 500px;}
					img{display: block;float: right;}
					

				</style>
			</head>
			<body>
				<section>
					<div id="titulo">
						<h3>Recuperaci&oacute;n de Contrase&ntilde;a</h3>
						<img src="http://www.aditivospetrolabs.com/images/Aditivos-Petrolabs.png" >

					</div>
					<div id="content">
						<h4>Hola, '.htmlentities($user['nombre_completo']).'</h4>
						<p>Cambiamos tu Contrase&ntilde;a tal como lo solicitaste.</p>
						<p>Tu nueva Contrase&ntilde;a es: <b> '.$newpass.'</b> </p>
					</div>
				</section>
			</body>
			</html>
			';	
				
				$mail->AltBody = 'Para ver el mensaje, por favor use un visor de e-mail compatible con HTML.';
				$mail->AddAddress($destinatario);
				$mail->MsgHTML($body);

				$colums = "contrasena ='".$newpass."'";
				if ($db->update('eds_users', $colums, $condi )) 
				{
				
					$exito = $mail->Send();
					$intentos =1;
					
					while ((!$exito) && ($intentos < 5))
					{
						$exito = $mail->Send();
						$intentos ++;
					}
					
					if(!$exito)
					{
						
						echo "<script>alert('No se logró enviar la nueva contraseña al correo : ".$destinatario."');</script>";
						echo "<script>document.location.href='login-catalogo-deseos.php';</script>";
						
					}
					else
					{
						echo "<script>alert('La nueva contraseña ha sido enviada al correo: ".$destinatario."');</script>";
						echo "<script>document.location.href='login-catalogo-deseos.php';</script>";
						
					}	
				}else
				{
					echo "<script>alert('No se logro cambiar la contraseña actual');</script>";
					echo "<script>document.location.href='login-catalogo-deseos.php';</script>";
				}



				 
		}
		else
		{
			?>
				<script>
					alert("Los datos que ha ingresado no son correctos. Por favor intente de nuevo.");
					document.location.href="recordar-pass-catalogo.php";				
				</script>		
			<?
		}

		
	}
	




 ?>

<div id="titleArea" class="centerContent">
	<h1><?= $pageTitle; ?></h1>
</div>
<div class=" whiteBg">
	<section class="centerContent">
		<div id="breadCrumbs">
			<div id="breadNav">¿Preguntas?</div> 
			<a href="contactenos.php" id="breadContact">Contáctenos</a>
		</div>		
		<div class="shadow"></div>
		<div class="clear"></div>
		<div id="productList">			
			<div class="clear20"></div>
			<div class="testiTitle">
				<i class="fa fa-star"></i>
				<h2><?= $pageTitle ?></h2>			
			</div>
			<div class="clear20"></div>
			<div class="iLogin">
				<form action="<?= $_SERVER['PHP_SELF'] ?>" method="post">
					<input type="hidden" name="form" value="recuperar">
					<label><span class="req">*</span>Correo electrónico:</label>
					<input id="username" name="username" size="50" class="validate[required]" type="text"></td>
					<a href="login-catalogo-deseos.php">&laquo; Iniciar sesión</a>
					<div class="clear20"></div>
					<input value="Aceptar" type="submit">
				</form>
			</div>
			<div class="clear20"></div>
	</section>
	<div class="clear50"></div>		
</div>
<? include("footer.php"); ?>