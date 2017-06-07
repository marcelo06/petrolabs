<?php
include("lib/functions.php");
/*session_start();
if(isset($_GET['lang']))
	$_SESSION['lang'] = $_GET['lang'];
if(!isset($_SESSION['lang']))
	$_SESSION['lang'] = '1';*/
$conf = config();
$db = database();
$sec_pk = 8;
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

	if($_POST['form']=='contest')
	{
		include('lib/ReCaptcha.php');
		$recaptcha = new ReCaptcha($_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);
		$resp = $recaptcha->verificar();
		if($resp['success'] != true)
		{
			$msgContest = 'Error validando el código captcha. Por favor, intente nuevamente.';
		}
		else
		{
			if($db->insert('concurso_cencosud', 'tipo, eds, nombre, correo, ventas, estado', "'v', '".$_POST['eds']."', '".$_POST['nombre']."', '".$_POST['correo']."', '".$_POST['ventas']."', 'p'"))
			{
				// Envío correo de notificación al administrador.
				$body = '<p>Este es un mensaje enviado a trav&eacute;s del formulario de solicitud de premio del sitio web.</p><table border="0">'.
					'<tr><td><strong>Concurso:</strong></td><td>Cencosud</td></tr>'.
					'<tr><td><strong>EDS:</strong></td><td>'.$_POST['eds'].'</td></tr>'.
					'<tr><td><strong>Su nombre:</strong></td><td>'.$_POST['nombre'].'</td></tr>'.
					'<tr><td><strong>Su correo:</strong></td><td>'.$_POST['correo'].'</td></tr>'.
					'<tr><td><strong>Ventas:</strong></td><td>'.$_POST['ventas'].'</td></tr></table>';
				$header = 'MIME-Version: 1.0'."\r\n";
				$header .= 'Content-type: text/html; charset=iso-8859-1'."\r\n";
				$header .= 'From: '.$_POST['nombre'].' <'.$_POST['correo'].'>'."\r\n";
				$header .= 'Reply-To: '.$_POST['correo']."\r\n";
				$header .= 'Cc: petrolabsdecolombia@gmail.com' . "\r\n";

				mail($row['correo_contacto'], 'Solicitud de premio de la EDS '.utf8_decode($_POST['eds']), $body, $header);  // No me interesa si envía o no el correo.
				$msgContest = 'Gracias por registrarse.';
				unset($_POST);
			}
			else
				$msgContest = 'Ocurrió un error y no se pudo registrar su solicitud.';
		}
	}
	elseif($_POST['form']=='inscription')
	{
		if($db->insert('concurso_cencosud', 'tipo, eds, nombre, correo, telefono, ventas, estado', "'i', '".$_POST['ieds']."', '".$_POST['inombre']."', '".$_POST['icorreo']."', '".$_POST['itelefono']."', '0', 'p'"))
		{
			$body = '<p>Este es un mensaje enviado a trav&eacute;s del formulario de inscripci&oacute;n al concurso del sitio web.</p><table border="0">'.
				'<tr><td><strong>Nombre EDS:</strong></td><td>'.$_POST['ieds'].'</td></tr>'.
				'<tr><td><strong>Su nombre:</strong></td><td>'.$_POST['inombre'].'</td></tr>'.
				'<tr><td><strong>Su correo:</strong></td><td>'.$_POST['icorreo'].'</td></tr>'.
				'<tr><td><strong>Su tel&eacute;fono:</strong></td><td>'.$_POST['itelefono'].'</td></tr></table>';
			$header = 'MIME-Version: 1.0'."\r\n";
			$header .= 'Content-type: text/html; charset=iso-8859-1'."\r\n";
			$header .= 'From: '.$_POST['inombre'].' <'.$_POST['icorreo'].'>'."\r\n";
			$header .= 'Reply-To: '.$_POST['icorreo']."\r\n";
			$header .= 'Cc: petrolabsdecolombia@gmail.com' . "\r\n";

			mail($row['correo_contacto'], utf8_decode('Solicitud de inscripción al concurso de la EDS '.$_POST['ieds']), $body, $header);  // No me interesa si envía o no el correo.
			$msgInscription = 'Gracias por inscribirse.';
			unset($_POST);
		}
		else
			$msgInscription = 'Ocurrió un error y no se pudo registrar su solicitud.';
	}
}

$rowConf = $db->fetch_array($db->select('eds_cencosud', 'configuracion LIMIT 0,1'));
$rowCon = $db->fetch_all($db->select('*', 'concurso_cencosud', 'tipo="v" AND estado!="r" ORDER BY fecha'));

$db->disconnect();

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
}

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
		<div class="clear20"></div>
		<div class="editorText">
			<h3>EDS inscritas</h3>
			<ol> <?
				$eds = explode("\n", $rowConf['eds_cencosud']);
				foreach($eds as $e)
				{ ?>
					<li><?= $e; ?></li><?
				} ?>
			</ol>
		</div>
		<div class="clear20"></div>
		<hr>
		<div class="editorText">
			<?= $text[0]; ?>
		</div>
		<form id="contest" name="contest" method="post" action="<? $_SERVER['PHP_SELF']; ?>" class="pageContact">
			<input type="hidden" name="form" value="contest">
			<table class="contactTable" cellpadding="0" cellspacing="10">
				<tr>
					<td><span class="req">*</span> EDS:</td>
					<td>
						<select id="eds" name="eds" class="validate[required]">
							<option value=""></option><?
							foreach($eds as $e)
							{ ?>
								<option value="<?= $e; ?>"><?= $e; ?></option><?
							} ?>
						</select>
					</td>
				</tr>
				<tr>
					<td><span class="req">*</span> Su nombre:</td>
					<td><input type="text" id="nombre" name="nombre" size="40" maxlenght="60" value="<? if(isset($_POST['nombre'])) echo $_POST['nombre']; ?>" class="validate[required]"></td>
				</tr>
				<tr>
					<td><span class="req">*</span> Su correo:</td>
					<td><input type="text" id="correo" name="correo" size="40" maxlenght="60" value="<? if(isset($_POST['correo'])) echo $_POST['correo']; ?>" class="validate[required,custom[email]]"></td>
				</tr>
				<tr>
					<td><span class="req">*</span> Ventas -<em>unidades</em>-:</td>
					<td><input type="text" id="ventas" name="ventas" size="40" maxlenght="10" value="<? if(isset($_POST['ventas'])) echo $_POST['ventas']; ?>" class="validate[required,custom[number]]"></td>
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
		<div class="editorText">
			<h3>Pozo disponible</h3>
			<h1 style="text-align: center;">$<?= number_format($pozo, 0, '', '.'); ?></h1>
			<p>&nbsp;</p>
			<h3>Ganadores</h3>
			<table style="width: 100%;">
				<tr>
					<td style="text-align: center;"><strong>Fecha y hora</strong></td>
					<td style="text-align: center;"><strong>EDS</strong></td>
					<td style="text-align: center;"><strong>Registrado por</strong></td>
					<td style="text-align: center;"><strong>Ventas</strong></td>
					<td style="text-align: center;"><strong>Estado</strong></td>
					<td style="text-align: center;"><strong>Premio</strong></td>
				</tr> <?
				if(count($rowCon)>0)
				{
					foreach($rowCon as $r)
					{ ?>
						<tr>
							<td><?= $r['fecha']; ?></td>
							<td><?= $r['eds']; ?></td>
							<td><?= $r['nombre']; ?></td>
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
						</tr> <?
					}
				}
				else
				{ ?>
					<tr><td colspan="6"><div align="center">No se han registrado solicitudes de premio.</div></td></tr> <?
				} ?>
			</table>
			<p>&nbsp;</p>
			<?= $text[1]; ?>
		</div>
	</section>
</div>
<script type="text/javascript" src="js/jquery.validationEngine-<?= language($db); ?>.js"></script>
<script type="text/javascript" src="js/jquery.validationEngine.js"></script>
<script type="text/javascript">
	$(document).ready(function(){
		$('#inscription, #contest').validationEngine();
	});
</script>
<?
include("footer.php");
?>