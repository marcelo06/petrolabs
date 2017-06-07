<?
define('ROOT_FOLDER', '../uploads/images/');
chdir('../');
$updir = getcwd().DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR;
chdir('cms');
$msg = '';
$pageTitle = "Gestión de imágenes";
$menuActive = 4; ?>
<? include("header.php"); ?> <?
function friendly_url($url)
{
	$url = html_entity_decode($url);
	$url = mb_strtolower($url);
	$find = array('á', 'é', 'í', 'ó', 'ú', 'à', 'è', 'ì', 'ò', 'ù', 'ñ');
	$repl = array('a', 'e', 'i', 'o', 'u', 'a', 'e', 'i', 'o', 'u', 'n');
	$url = str_replace($find, $repl, $url);
	$find = array(' ', '&', '\r\n', '\n', '+');
	$url = str_replace($find, '-', $url);
	$find = array('/[^a-z0-9\-<>._]/', '/[\-]+/', '/<[^>]*>/');
	$repl = array('', '-', '');
	$url = preg_replace($find, $repl, $url);
	return $url;
}
function thumbnail($image, $ext, $height=60)
{
	if(file_exists($image))
	{
		switch($ext)
		{
			case '.gif':
				$im = @imagecreatefromgif($image);
				break;
			case '.jpeg':
			case '.jpg':
				$im = @imagecreatefromjpeg($image);
				break;
			case '.png':
				$im = @imagecreatefrompng($image);
				break;
			default:
				$im = NULL;
		}
	}
	$i = strrpos($image, '.');  // Devuelve la última aparición de "." en el nombre de la foto.
	$name_small = substr($image, 0, $i).'_small'.substr($image, $i);
	if(!$im)
	{
		// Comprobar si ha fallado.
		$h = $height;
		$im  = imagecreate(100, $h); // Crear una imagen en blanco.
		$bgc = imagecolorallocate($im, 225, 225, 225);
		$tc  = imagecolorallocate($im, 90, 90, 90);
		imagefilledrectangle($im, 0, 0, 160, $h, $bgc);
		// Mostrar un mensaje de error.
		imagestring($im, 2, 21, $h / 2 - 23, "Formato de", $tc);
		imagestring($im, 2, 22, $h / 2 - 9, "imagen no", $tc);
		imagestring($im, 2, 22, $h / 2 + 5, "soportado", $tc);
		imagepng($im, $name_small);
	}
	else
	{
		$size = getimagesize($image);  // [0]:ancho; [1]:alto.
		if(isset($height) && $size[1] <= $height)
		{
			$w = $size[0];
			$h = $size[1];
		}
		else
		{
			$w = $height / $size[1] * $size[0];
			$h = $height;
		}
		$thumb_im = imagecreatetruecolor($w, $h);
		imagecopyresampled($thumb_im, $im, 0, 0, 0, 0, $w, $h, $size[0], $size[1]);
		imagejpeg($thumb_im, $name_small);
		imagedestroy($thumb_im);
	}
	imagedestroy($im);
}
if(isset($_POST['send']))
{
	if($_FILES['photo']['tmp_name'] != '' && is_uploaded_file($_FILES['photo']['tmp_name']))
	{
		$ok = true;
		$ext_ok = false;
		$i = strrpos($_FILES['photo']['name'], '.');
		$ext = strtolower(substr($_FILES['photo']['name'], $i));
		$exts = array('.jpg', '.jpeg', '.png', '.gif');
		$limit = count($exts);
		for($i=0; $i<$limit && !$ext_ok; $i++)
			if($exts[$i] == $ext)
				$ext_ok = true;
		if($ext_ok)
		{
			$s = getimagesize($_FILES['photo']['tmp_name']);
			if($s)
			{
				$src = friendly_url($_FILES['photo']['name']);
				if(copy($_FILES['photo']['tmp_name'], ROOT_FOLDER.$src))
				{
					$msg = 'La imagen se cargó correctamente.';
					thumbnail(ROOT_FOLDER.$src, $ext);
				}
				else
					$msg = 'Ocurrió un error cargando la imagen.';
			}
			else
				$msg = 'Ocurrió un error cargando la imagen.';
		}
		else
			$msg = 'La imagen seleccionada no tiene una extensión de archivo válida. Sólo se admiten .jpg, .jpeg, .png, .gif.';
	}
}
elseif(isset($_GET['task']) && $_GET['task'] == 'delete')
{
	// Eliminar imágenes.
	$k = strrpos($_GET['delFile'], '.');
	$ini = substr($_GET['delFile'], 0, $k);
	$ext = substr($_GET['delFile'], $k);
	@unlink($updir.$_GET['delFile']);
	@unlink($updir.$ini.'_small'.$ext);
	echo '<script type="text/javascript"> alert(\'La imagen se eliminó correctamente.\'); window.location=\''.$_SERVER['HTTP_REFERER'].'\'; </script>';
	exit();
}
if($msg!='')
	echo '<script type="text/javascript"> alert(\''.$msg.'\'); </script>'; ?>
<header>
	<div class="fLeft"><h1>Subir nueva imagen</h1></div>
	<div class="clear"></div>
</header>
<div class="contentPane">
	<form name="imagen" action="<?= $_SERVER['REQUEST_URI']; ?>" enctype="multipart/form-data" method="post">
		<table class="formTable">
			<tr>
				<th>Seleccione el archivo (.jpg, .jpeg, .png, .gif):</th>
				<td><input type="file" name="photo"></td>
			</tr>
			<tr>
				<th>&nbsp;</th>
				<td><input type="submit" name="send" value="Subir imagen"></td>
			</tr>
		</table>
	</form>
</div>
<div class="clear50"></div>
<header>
	<div class="fLeft"><h1>Imágenes disponibles</h1></div>
	<div class="clear"></div>
</header>
<div class="contentPane">
	<table width="100% "cellspacing="0" cellpadding="0" class="dataTable">
		<tr>
			<th>Imagen</th>
			<th>Ruta</th>
			<th>Tamaño</th>
			<th>Opciones</th>
		</tr><?
		$op3 = opendir(ROOT_FOLDER);
		$i = 1;
		while(FALSE!==($file = readdir($op3)))
		{
			if($file!='.' && $file!='..' && $file!=basename($_SERVER['SCRIPT_NAME']) && $file!='Thumbs.db' && strrpos($file, '_small.')===FALSE)
			{
				$s = getimagesize($updir.$file);
				if($s)
				{
					$j = strrpos($file, '.');  // Devuelve la última aparición de "." en el nombre de la foto.
					$fileSmall = substr($file, 0, $j).'_small'.substr($file, $j);
					if(file_exists(ROOT_FOLDER.$fileSmall))
					{
						$src = ROOT_FOLDER.$fileSmall;
					}
					else
					{
						$src = ROOT_FOLDER.$file.'" height="60';
						thumbnail(ROOT_FOLDER.$file, substr($file, $j));  // Genero la vista previa.
					} ?>
					<tr>
						<td><a name="<?= $i ?>" href="<?= ROOT_FOLDER.$file; ?>" target="_blank"><img src="<?= $src; ?>" alt=""></a></td>
						<td><a href="<?= ROOT_FOLDER.$file; ?>" target="_blank"><?= 'http://'.$_SERVER['SERVER_NAME'].'/uploads/images/'.$file; ?></a></td>
						<td><?= number_format(filesize($updir.$file)/1024, 1, ',', '.')." KB / $s[0] x $s[1]"; ?></td>
						<td>
							<a class="redLink" href="#<?= $i ?>" onclick="confirmation('¿Confirma la eliminación de esta imagen?', '<?= $_SERVER['PHP_SELF']; ?>?task=delete&amp;delFile=<?= $file; ?>');"><span class="icon">X</span> Borrar</a>
						</td>
					</tr> <?
					$i++;
				}
				else
				{
					//@unlink($updir.$file);  // No es una imagen, así que se borra por si es un script malicioso.
				}
			}
		} ?>
	</table>
</div>
<script type="text/javascript">
<!--
	$(document).ready(function(){
		// Intercalar colores de las filas del listado
		$('.dataTable tr:odd td').addClass('whiteTd');
	});

	function confirmation(message, location)
	{
		if(confirm(message))
			window.location = location;
	}
-->
</script>
<? include("footer.php"); ?>