<?php
include("lib/functions.php");
/*session_start();
if(isset($_GET['lang']))
	$_SESSION['lang'] = $_GET['lang'];
if(!isset($_SESSION['lang']))
	$_SESSION['lang'] = '1';*/
$conf = config();
$db = database();
$sec_pk = 9;
$columns = 'sec_pk_origen, sec_index, sec_imgconf, sec_lim_img, sec_archivo, sec_contacto, stx_nombre AS title';
$condi = "sec.sec_pk='$sec_pk' AND sec.sec_pk=stx.sec_pk AND idi_pk='$_SESSION[lang]'";
$info = $db->fetch_array($db->select($columns, 'seccion AS sec, seccion_txt AS stx', $condi));  // Información general
$dimensions = explode(',', str_replace(' ', '', $info['sec_imgconf']));

// Textos
$condi = "sec_pk='$sec_pk' AND blo.blo_pk=btx.blo_pk AND idi_pk='$_SESSION[lang]' ORDER BY blo.blo_pk";
$result = $db->select('btx_contenido', 'bloque AS blo, bloque_txt AS btx', $condi);
$numTxt = $db->num_rows($result);
if($numTxt>0)
{
	while($row=$db->fetch_array($result))
	{
		$text[] = $row['btx_contenido'];
	}
}

// Imágenes
if($info['sec_lim_img']>0)
{
	$condi = "sec_pk='$sec_pk' AND sei_archivo!='' AND si.sei_pk=sit.sei_pk AND idi_pk='$_SESSION[lang]' ORDER BY si.sei_pk";
	$result = $db->select('sei_archivo AS filename, titulo AS title, descripcion AS description', 'seccion_img AS si, sei_txt AS sit', $condi);
	$numImg = $db->num_rows($result);
	if($numImg>0)
	{
		for($i=0; $i<$numImg; $i++)
		{
			$imgInfo[$i] = $db->fetch_array($result);
			$pos = mb_strrpos($imgInfo[$i]['filename'], '.');
			$ini = mb_substr($imgInfo[$i]['filename'], 0, $pos);
			$ext = mb_substr($imgInfo[$i]['filename'], $pos);
			foreach($dimensions as $key=>$value)  // Un archivo por cada tamaño de imagen configurado
			{
				$imgFile[$i][$key] = $ini.'--'.$value.$ext;
			}
		}
	}
}

// Subpáginas
$condi = "sec_pk_origen='$sec_pk' AND sec.sec_pk=stx.sec_pk AND idi_pk='$_SESSION[lang]' ORDER BY sec.sec_pk";
$result = $db->select('stx_nombre, sec_archivo', 'seccion AS sec, seccion_txt AS stx', $condi);
$numSub = $db->num_rows($result);
if($numSub>0)
{
	while($row=$db->fetch_array($result))
	{
		$subTitle[] = $row['stx_nombre'];
		$subFile[] = $row['sec_archivo'].'.php';
	}
}

if($_SERVER['REQUEST_METHOD']=='POST')
{
	$row = $db->fetch_array($db->select('correo_contacto', 'configuracion LIMIT 0,1'));

	if($_POST['form']=='inscription')
	{
		include('lib/ReCaptcha.php');
		$recaptcha = new ReCaptcha($_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);
		$resp = $recaptcha->verificar();
		if($resp['success'] != TRUE)
		{
			$msgContest = 'Error validando el código captcha. Por favor, intente nuevamente.';
		}
		else
		{
			if($db->insert('contacto', 'ciudad, nombre, correo, telefono, mensaje', "'".$_POST['iciudad']."', '".$_POST['inombre']."', '".$_POST['icorreo']."', '".$_POST['itelefono']."', 'EDS: ".$_POST['ieds']."'"))
			{
				// Envío correo de notificación al administrador.
				$body = '<p>Este es un mensaje enviado a trav&eacute;s del formulario de vinculaci&oacute;n del sitio web.</p><table border="0">'.
					'<tr><td><strong>Nombre EDS:</strong></td><td>'.$_POST['ieds'].'</td></tr>'.
					'<tr><td><strong>Ciudad:</strong></td><td>'.$_POST['iciudad'].'</td></tr>'.
					'<tr><td><strong>Nombre:</strong></td><td>'.$_POST['inombre'].'</td></tr>'.
					'<tr><td><strong>Correo:</strong></td><td>'.$_POST['icorreo'].'</td></tr>'.
					'<tr><td><strong>Tel&eacute;fono:</strong></td><td>'.$_POST['itelefono'].'</td></tr></table>';
				$header = 'MIME-Version: 1.0'."\r\n";
				$header .= 'Content-type: text/html; charset=iso-8859-1'."\r\n";
				$header .= 'From: '.$_POST['inombre'].' <'.$_POST['icorreo'].'>'."\r\n";
				$header .= 'Reply-To: '.$_POST['icorreo']."\r\n";
				$header .= 'Cc: petrolabsdecolombia@gmail.com' . "\r\n";

				mail($row['correo_contacto'], 'Solicitud de vinculación de la EDS '.utf8_decode($_POST['ieds']), $body, $header);  // No me interesa si envía o no el correo.
				$msgContest = 'Gracias por escribirnos. Un asesor lo contactará próximamente.';
				unset($_POST);
			}
			else
				$msgContest = 'Ocurrió un error y no se pudo registrar su solicitud.';
		}
	}
}

$db->disconnect();

/*
 * $info['title']: título.
 * $numTxt: número de textos.
 * $numImg: número de imágenes.
 * $numSub: número de subpáginas.
 * $text[$i]: texto $i.
 * $imgInfo[$i]['filename']: nombre de archivo de la imagen $i.
 * $imgInfo[$i]['title']: título de la imagen $i.
 * $imgInfo[$i]['description']: descripción de la imagen $i.
 * $imgFile[$i][$j]: imagen $i en los diferentes tamaños; $j es un valor entre 0 y la cantidad de tamaños configurada menos 1.
 */
$pageTitle = $info['title']; ?> <?
include("header.php"); ?>
<script type="text/javascript" src='https://www.google.com/recaptcha/api.js?hl=es-419'></script>
<div id="titleArea" class="centerContent">
	<h1><?= $pageTitle; ?></h1>
</div>
<div class="whiteBg">
	<section class="centerContent">
		<div id="breadCrumbs">
			<div id="breadNav"><a href="index.php"><?= INICIO ?></a> &raquo; <?= $info['title']; ?></div>
			<a href="contactenos.php" id="breadContact"><?= CONTACTO_MAS_INFO ?></a>
		</div>		
		<div class="shadow"></div>
		<div class="clear20"></div> <?
		if(is_file('uploads/pages/'.$sec_pk.'/'.$imgFile[0][0]))
		{ ?>
			<img src="<?= 'uploads/pages/'.$sec_pk.'/'.$imgFile[0][0]; ?>" alt="" title="<?= $imgInfo[0]['title']; ?>" style="float:right; margin-left:25px;"> <?
		} ?>
		<div class="editorText">
			<?= $text[0]; ?>
		</div>
		<form id="inscription" name="inscription" method="post" action="<? $_SERVER['PHP_SELF']; ?>" class="pageContact">
			<input type="hidden" name="form" value="inscription">
			<table class="contactTable" cellpadding="0" cellspacing="10">
				<tr>
					<td><span class="req">*</span> Nombre EDS:</td>
					<td><input type="text" id="ieds" name="ieds" size="40" maxlenght="60" value="<? if(isset($_POST['ieds'])) echo $_POST['ieds']; ?>" class="validate[required]"></td>
				</tr>
				<tr>
					<td><span class="req">*</span> Ciudad:</td>
					<td><input type="text" id="iciudad" name="iciudad" size="40" maxlenght="60" value="<? if(isset($_POST['iciudad'])) echo $_POST['iciudad']; ?>" class="validate[required]"></td>
				</tr>
				<tr>
					<td><span class="req">*</span> Su nombre:</td>
					<td><input type="text" id="inombre" name="inombre" size="40" maxlenght="60" value="<? if(isset($_POST['inombre'])) echo $_POST['inombre']; ?>" class="validate[required]"></td>
				</tr>
				<tr>
					<td><span class="req">*</span> Su correo:</td>
					<td><input type="text" id="icorreo" name="icorreo" size="40" maxlenght="60" value="<? if(isset($_POST['icorreo'])) echo $_POST['icorreo']; ?>" class="validate[required,custom[email]]"></td>
				</tr>
				<tr>
					<td><span class="req">*</span> Su teléfono:</td>
					<td><input type="text" id="itelefono" name="itelefono" size="40" maxlenght="25" value="<? if(isset($_POST['itelefono'])) echo $_POST['itelefono']; ?>" class="validate[required]"></td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td><input type="checkbox" id="iacepto" name="iacepto" class="validate[required]"> <label for="iacepto">Acepto los <a href="autorizacion-uso-datos-personales.php" target="_blank">términos de uso de imagen y de datos personales</a>.</label></td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td><div class="g-recaptcha" data-sitekey="6LfK_BUTAAAAAGhBNCdr7Ps5daC3Q0NxTgurExoc"></div></td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td><input type="submit" value="<?= CONTACT_SEND; ?>"></td>
				</tr>
				<tr>
					<td>&nbsp;</td>
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
<script type="text/javascript" src="js/jquery.validationEngine-<?= language($db); ?>.js"></script>
<script type="text/javascript" src="js/jquery.validationEngine.js"></script>
<script type="text/javascript">
	$(document).ready(function(){
		$('#inscription').validationEngine();
	});
</script>
<?
include("footer.php");
?>