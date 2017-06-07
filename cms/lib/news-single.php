<?php
include("lib/functions.php");
session_start();
if(isset($_GET['lang']))
	$_SESSION['lang'] = $_GET['lang'];
if(!isset($_SESSION['lang']))
	$_SESSION['lang'] = '1';
if(!isset($_GET['id']) || empty($_GET['id']))
{
	include("header.php");
	include("footer.php");
	exit();
}
$db = database();
include("lang/".language($db).".php");
$pk = newsId;
$not_pk = $_GET['id'];
$module = $db->fetch_array($db->select('*', 'modulo_noticias', "pk='$pk'"));
$dimensions = explode(',', str_replace(' ', '', $module['tamano_img']));
$info = $imgInfo = $imgFile = $file = array();  // Vectores para datos básicos del registro, imágenes y archivos adjuntos.
$condi = "noticia.not_pk='$not_pk' AND noticia.not_pk=noticia_txt.not_pk AND idi_pk='$_SESSION[lang]'";
$info = $db->fetch_array($db->select('not_fecha AS date, ntx_titulo AS title, ntx_resumen AS summary, ntx_contenido AS content', 'noticia, noticia_txt', $condi));
if($module['fecha']=='1')
{
	$date = explode('-', $info['date']);
	$info['date'] = "$date[2]/$date[1]/$date[0]";
}

// Imágenes
if($module['limite_img']>0)
{
	$condi = "not_pk='$not_pk' AND noi_archivo!='' AND ni.noi_pk=nit.noi_pk AND idi_pk='$_SESSION[lang]' ORDER BY ni.noi_pk";
	$result = $db->select('noi_archivo AS filename, titulo AS title, descripcion AS description', 'noticia_img AS ni, noi_txt AS nit', $condi);
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

// Archivos adjuntos
if($module['adjuntos']=='1' && $module['limite_adj']>0)
{
	$condi = "not_pk='$not_pk' AND noa_archivo!='' AND na.noa_pk=nat.noa_pk AND idi_pk='$_SESSION[lang]' ORDER BY na.noa_pk";
	$result = $db->select('noa_archivo AS filename, titulo AS title, descripcion AS description', 'noticia_adjunto AS na, noa_txt AS nat', $condi);
	while($row=$db->fetch_array($result))
	{
		$file[] = $row;
	}
}

$db->disconnect();
/*
 * $info['date']: fecha.
 * $info['title']: título.
 * $info['summary']: resumen.
 * $info['content']: contenido.
 * $numImg: número de imágenes asociadas.
 * $imgInfo[$i]['filename']: nombre de archivo de la imagen $i.
 * $imgInfo[$i]['title']: título de la imagen $i.
 * $imgInfo[$i]['description']: descripción de la imagen $i.
 * $imgFile[$i][$j]: imagen $i en los diferentes tamaños; $j es un valor entre 0 y la cantidad de tamaños configurada menos 1.
 * $file[$i]['filename']: nombre del archivo adjunto $i.
 * $file[$i]['title']: título del archivo adjunto $i.
 * $file[$i]['description']: descripción del archivo adjunto $i.
 */
$pageTitle = ''; ?> <?
include("header.php"); ?>
<h1><?= $pageTitle; ?></h1>
<table border="1"> <?
	if($module['fecha']=='1')
	{ ?>
		<tr>
			<td>Fecha:</td>
			<td><?= $info['date']; ?></td>
		</tr> <?
	} ?>
	<tr>
		<td>Título:</td>
		<td><?= $info['title']; ?></td>
	</tr>
	<tr>
		<td>Contenido:</td>
		<td><?= $info['content']; ?></td>
	</tr> <?
	if($module['limite_img']>0)
	{
		for($i=0; $i<$numImg; $i++)
		{ ?>
			<tr>
				<td>Imagen <?= $i; ?>:</td>
				<td> <?
					foreach($imgFile[$i] as $value)  // Un archivo por cada tamaño de imagen configurado
					{ ?>
						<img src="<?= 'uploads/news/'.$not_pk.'/'.$value; ?>" alt="" title="<?= $imgInfo[$i]['title']; ?>"> <?
					} ?>
				</td>
			</tr> <?
		}
	}
	if($module['adjuntos']=='1' && $module['limite_adj']>0 && count($file)>0)
	{
		foreach($file as $key=>$value)
		{ ?>
			<tr>
				<td>Archivo adjunto <?= $key; ?>:</td>
				<td>
					<a href="<?= 'uploads/news/'.$not_pk.'/'.$value['filename']; ?>" target="_blank" title="<?= $value['description']; ?>"><?= empty($value['title']) ? $value['filename'] : $value['title']; ?></a>
				</td>
			</tr> <?
		}
	} ?>
</table> <?
include("footer.php");
?>