<?php
if($_SERVER['REQUEST_METHOD']=='POST')
{
	include("lib/class.phpmailer.php");
	$mail = new PHPMailer();
	$mail->IsSMTP();
	$mail->Host = 'mail.'.$conf['settings']->domain;
	$mail->Port = 465;
	$mail->SMTPSecure = "ssl";
	$mail->SMTPAuth = true;
	$mail->From = $_POST['email'];
	$mail->FromName = utf8_decode($_POST['nombre']);
	$mail->Username = $conf['settings']->sendersEmail;
	$mail->Password = $conf['settings']->pw;
	$mail->Mailer = 'smtp';
	$mail->WordWrap = 65;
	$mail->Subject = 'Mensaje de contacto enviado desde '.$_SERVER['HTTP_HOST'];
	$body = '<p>Este es un mensaje enviado a trav&eacute;s del formulario de contacto del sitio web.</p><table border="0"><tr><td><strong>Nombre:</strong></td><td>'.$_POST['nombre'].'</td>'.
		'</tr><tr><td><strong>Tel&eacute;fono:</strong></td><td>'.$_POST['telefono'].'</td></tr><tr><td><strong>Ciudad:</strong></td><td>'.$_POST['ciudad'].'</td></tr><tr><td><strong>E-mail:'.
		'</strong></td><td>'.$_POST['email'].'</td></tr><tr><td><strong>Mensaje:</strong></td><td>'.nl2br($_POST['mensaje']).'</td></tr></table>';
	$mail->AltBody = 'Para ver el mensaje, por favor use un visor de e-mail compatible con HTML.';
	$mail->ClearAddresses();
	$mail->AddReplyTo($_POST['email'], utf8_decode($_POST['nombre']));
	$mail->MsgHTML($body);
	$row = $db->fetch_array($db->select('correo_contacto', 'configuracion LIMIT 0,1'));
	$mail->AddAddress($row['correo_contacto']);
	$msg = $mail->Send() ? CONTACT_SUCCESS : CONTACT_FAILURE;
	$db->insert('contacto', 'ciudad, nombre, correo, telefono, mensaje', "'".$_POST['ciudad']."', '".$_POST['nombre']."', '".$_POST['email']."', '".$_POST['telefono']."', '".nl2br($_POST['mensaje'])."'");
} ?>
<div>
	<form id="contactForm" name="contactForm" action="<?= $_SERVER['PHP_SELF']; ?>" method="post" class="pageContact">
		<table class="contactTable" cellpadding="0" cellspacing="10">
			<tr>
				<th><span class="req">*</span> <?= CONTACT_NAME; ?>:</th>
				<td><input type="text" id="nombre" name="nombre" size="40" maxlenght="60" class="validate[required]"></td>
			</tr>
			<tr>
				<th><?= CONTACT_PHONE; ?>:</th>
				<td><input type="text" id="telefono" name="telefono" size="40" maxlenght="25"></td>
			</tr>
			<tr>
				<th><?= CONTACT_CITY; ?>:</th>
				<td><input type="text" id="ciudad" name="ciudad" size="40" maxlenght="60"></td>
			</tr>
			<tr>
				<th><span class="req">*</span> E-Mail:</th>
				<td><input type="text" id="email" name="email" size="40" maxlenght="60" class="validate[required,custom[email]]"></td>
			</tr>
			<tr>
				<th valign="top"><span class="req">*</span> <?= CONTACT_MESSAGE; ?>:</th>
				<td><textarea id="mensaje" name="mensaje" rows="3" cols="80" class="validate[required]"></textarea></td>
			</tr>
			<tr>
				<th>&nbsp;</th>
				<td><input type="submit" value="<?= CONTACT_SEND; ?>"></td>
			</tr>
		</table>
	</form> <?
	if(isset($msg))
	{ ?>
		<p><?= $msg; ?></p> <?
	} ?>
</div>
<script type="text/javascript" src="js/jquery.validationEngine-<?= language($db); ?>.js"></script>
<script type="text/javascript" src="js/jquery.validationEngine.js"></script>
<script type="text/javascript">
	$(document).ready(function(){
		$("#contactForm").validationEngine();
	});
</script>
