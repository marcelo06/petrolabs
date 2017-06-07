<?php
include_once("lib/functions.php");
$conf = config();
$db = database();
if(!$db->selected_db)  // No se ha configurado la base de datos
{
	header("Location: cms/set-database.php");
	exit();
}
if($_SERVER['REQUEST_METHOD']=='POST')
{
	//$row = $db->fetch_array($db->select('correo_contacto', 'configuracion LIMIT 0,1'));

	if($_POST['form']=='contest')
	{
		/*include('lib/ReCaptcha.php');
		$recaptcha = new ReCaptcha($_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);
		$resp = $recaptcha->verificar();
		if($resp['success'] != true)
		{
			$msgContest = 'Error validando el código captcha. Por favor, intente nuevamente.';
		}
		else
		{*/
			if($db->insert('pedido', 'asesor, nombre, telefono, sc4x1, sc4x1p, op2, dp4o, dp2, mp, observaciones', "'".$_POST['asesor']."', '".$_POST['nombre']."', '".$_POST['telefono']."', '".$_POST['sc4x1']."', '".$_POST['sc4x1p']."', '".$_POST['op2']."', '".$_POST['dp4o']."', '".$_POST['dp2']."', '".$_POST['mp']."', '".$_POST['observaciones']."'"))
			{
				$ped_pk = $db->last_insert_id();
				// Envío correo de notificación al administrador.
				$body = '<p>Este es un mensaje enviado a trav&eacute;s del formulario de pedido de productos.</p>'.
					'<table border="0" cellpadding="5">'.
					'<tr style="background-color:#EEE;"><td><strong>Asesor:</strong></td><td align="center">'.$_POST['asesor'].'</td></tr>'.
					'<tr><td><strong>Ciudad / Nombre de la EDS:</strong></td><td align="center">'.$_POST['nombre'].'</td></tr>'.
					'<tr style="background-color:#EEE;"><td><strong>Número de teléfono:</strong></td><td align="center">'.$_POST['telefono'].'</td></tr>'.
					'<tr><td><strong>Super Concentrado 4x1:</strong></td><td align="center">'.$_POST['sc4x1'].'</td></tr>'.
					'<tr style="background-color:#EEE;"><td><strong>Super Concentrado 4x1 Plus:</strong></td><td align="center">'.$_POST['sc4x1p'].'</td></tr>'.
					'<tr><td><strong>Octane Power 2:</strong></td><td align="center">'.$_POST['op2'].'</td></tr>'.
					'<tr style="background-color:#EEE;"><td><strong>Diesel Power 4 onzas:</strong></td><td align="center">'.$_POST['dp4o'].'</td></tr>'.
					'<tr><td><strong>Diesel Power 2:</strong></td><td align="center">'.$_POST['dp2'].'</td></tr>'.
					'<tr style="background-color:#EEE;"><td><strong>Moto Power:</strong></td><td align="center">'.$_POST['mp'].'</td></tr>'.
					'<tr><td><strong>Observaciones:</strong></td><td align="center">'.$_POST['observaciones'].'</td></tr>'.
					'</table>';
				$header = 'MIME-Version: 1.0'."\r\n";
				$header .= 'Content-type: text/html; charset=iso-8859-1'."\r\n";
				$header .= 'From: Aditivos Petrolabs <miguel@aditivospetrolabs.com>'."\r\n";
				$header .= 'Reply-To: miguel@aditivospetrolabs.com'."\r\n";
				$header .= 'Cc: petrolabsdecolombia@gmail.com' . "\r\n";

				mail('mercadeo@aditivospetrolabs.com', 'Solicitud de pedido No. '.$ped_pk.' desde '.$_POST['nombre'], $body, $header);  // No me interesa si envía o no el correo.
				$msgContest = 'Gracias por registrar el pedido No. '.$ped_pk.'.';
				unset($_POST);
			}
			else
				$msgContest = 'Ocurrió un error y no se pudo registrar su pedido.';
		//}
	}
}
?>
<!doctype html>
<html>
	<head>
		<meta charset="utf-8">
		<title>Pedidos | Petrolabs de Colombia</title>
		<meta name="keywords" content="<?= $seo['keywords'];?>">
		<meta name="description" content="<?= $seo['description'];?>">
		<link rel="stylesheet" type="text/css" href="css/validationEngine.jquery.css">
		<link href="//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css" rel="stylesheet">
		<link href='http://fonts.googleapis.com/css?family=Lato:400,700' rel='stylesheet' type='text/css'>
		<link href='http://fonts.googleapis.com/css?family=Roboto+Condensed:400,300' rel='stylesheet' type='text/css'>
		<link rel="stylesheet" type="text/css" href="http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css">
		<link rel="stylesheet" type="text/css" href="css/style.css?v=6">
		<link rel="icon" href="http://www.aditivospetrolabs.com/images/petrolabs-icon.png">
		<meta name="viewport" content="width=device-width">
		<meta name="format-detection" content="telephone=no">		
		<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
		<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script>
		<!--script type="text/javascript" src='https://www.google.com/recaptcha/api.js?hl=es-419'></script-->
		<script type="text/javascript">
			// html5 fix
			document.createElement("nav");
			document.createElement("header");
			document.createElement("footer");
			document.createElement("section");
			document.createElement("article");
			document.createElement("aside");
			document.createElement("hgroup");
		</script>
	</head>
	<body><?
		/* Código Google Analytics */
		echo $pageSettings['googleAnalytics'];?>
		<div class="mobileContainer">			
			<div id="titleArea">
			</div>
			<div class="whiteBg">
				<section>
					<div class="shadow"></div>
					<div class="clear20"></div>
					<div class="editorText">
						Solicite aquí los productos que ofrece Petrolabs de Colombia.
					</div>
					<form id="contest" name="contest" method="post" action="<? $_SERVER['PHP_SELF']; ?>" class="pageContact">
						<input type="hidden" name="form" value="contest">
						<table class="contactTable" cellpadding="0" cellspacing="10">
							<tr>
								<td>
									Asesor: <span class="req">*</span>
									<div class="clear"></div>
									<select id="asesor" name="asesor" class="validate[required]">
										<option value="Otro asesor / EDS">Otro asesor / EDS</option>
										<option value="Claudia Ximena Moreno">Claudia Ximena Moreno</option>
										<option value="Diana Lorena Lerma Toro">Diana Lorena Lerma Toro</option>
										<option value="Gabriel Paez Cortes">Gabriel Paez Cortes</option>
										<option value="Herminia Matiz Galvis">Herminia Matiz Galvis</option>
										<option value="Jorge Heberth López Ávila">Jorge Heberth López Ávila</option>
										<option value="José Fabián Riaño">José Fabián Riaño</option>
										<option value="Luz Marina Tobón Marín">Luz Marina Tobón Marín</option>
										<option value="María del Cármen Pulecio">María del Cármen Pulecio</option>
										<option value="Sandra Milena Gil">Sandra Milena Gil</option>
										<option value="Wilmer Antonio Burgos">Wilmer Antonio Burgos</option>
										<option value="Yecid Obando Bedoya">Yecid Obando Bedoya</option>
									</select>
								</td>
							</tr>
							<tr>
								<td>
									Ciudad / Nombre de la EDS: <span class="req">*</span>
									<div class="clear"></div>
									<input type="text" id="nombre" name="nombre" size="40" maxlenght="80" class="validate[required]">
								</td>
							</tr>
							<tr>
								<td>
									Número de teléfono:
									<div class="clear"></div>
									<input type="text" id="telefono" name="telefono" size="20" maxlenght="40">
								</td>
							</tr>
							<tr>
								<td>
									Super Concentrado 4x1:
									<div class="clear"></div>
									<input type="text" id="sc4x1" name="sc4x1" size="20" maxlenght="5" value="0" class="validate[required,custom[number]]">
								</td>
							</tr>
							<tr>
								<td>
									Super Concentrado 4x1 Plus:
									<div class="clear"></div>
									<input type="text" id="sc4x1p" name="sc4x1p" size="20" maxlenght="5" value="0" class="validate[required,custom[number]]">
								</td>
							</tr>
							<tr>
								<td>
									Octane Power 2:
									<div class="clear"></div>
									<input type="text" id="op2" name="op2" size="20" maxlenght="5" value="0" class="validate[required,custom[number]]">
								</td>
							</tr>
							<tr>
								<td>
									Diesel Power 4 onzas:
									<div class="clear"></div>
									<input type="text" id="dp4o" name="dp4o" size="20" maxlenght="5" value="0" class="validate[required,custom[number]]">
								</td>
							</tr>
							<tr>
								<td>
									Diesel Power 2:
									<div class="clear"></div>
									<input type="text" id="dp2" name="dp2" size="20" maxlenght="5" value="0" class="validate[required,custom[number]]">
								</td>
							</tr>
							<tr>
								<td>
									Moto Power:
									<div class="clear"></div>
									<input type="text" id="mp" name="mp" size="20" maxlenght="5" value="0" class="validate[required,custom[number]]">
								</td>
							</tr>
							<tr>
								<td>
									Observaciones:
									<div class="clear"></div>
									<textarea id="observaciones" name="observaciones"></textarea>
								</td>
							</tr>
							<!--tr>
								<td><div class="g-recaptcha" data-sitekey="6LfK_BUTAAAAAGhBNCdr7Ps5daC3Q0NxTgurExoc"></div></td>
							</tr-->
							<tr>
								<td><input type="submit" value="Enviar Pedido"></td>
							</tr>
							<tr>
								<td> <?
									if(isset($msgContest))
									{ ?>
										<script type="text/javascript"> alert('<?= $msgContest; ?>'); </script>
										<span class="req"><?= $msgContest; ?></span> <?
									} ?>
								</td>
							</tr>
						</table>
					</form>
				</section>
			</div>
		</div>
		<script type="text/javascript" src="js/jquery.validationEngine-es.js"></script>
		<script type="text/javascript" src="js/jquery.validationEngine.js"></script>
		<script type="text/javascript">
			$(document).ready(function(){
				$('#contest').validationEngine('attach', {promptPosition : "topLeft", scroll: false});
			});
		</script>
	</body>
</html>