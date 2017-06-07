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
$pk = 3;
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
$pageTitle = TESTIMONIOS; ?><?
$currentPage = 2;
include("header.php"); ?>
<div id="titleArea" class="centerContent">
	<h1><?= $pageTitle; ?></h1>
</div>
<div class=" whiteBg">
	<section class="centerContent">
		<div id="breadCrumbs">
			<div id="breadNav"><a href="index.php"><?= INICIO ?></a> &raquo; <a href="testimonios.php"><?= $pageTitle;?></a> &raquo; <?= $info['title'] ?></div>
			<a href="contactenos.php" id="breadContact"><?= CONTACTO_MAS_INFO ?></a>
		</div>		
		<div class="shadow"></div>
		<div class="clear20"></div>
		<div class="contentWithSidebar">
			<div class="testiTitle">
				<i class="fa fa-quote-right"></i>
				<h2><?= $info['title']; ?></h2>
			</div>
			<div class="clear20"></div>
			<div class="editorText">
				<?= $info['content']; ?>
			</div><?
			if($numImg>0)
			{?>					
				<h2 class="iconSubtitle"><i class="fa fa-camera-retro"></i> <?= IMAGENES ?></h2><?
				for($i=0; $i<$numImg; $i++)
				{ ?>
					<a href="<?= 'uploads/news/'.$_GET['id'].'/'.$imgFile[$i][0] ?>" title="<?= $imgInfo[$i]['title']; ?>" class="fancybox" rel="ibtestimonial<?=$_GET['id'];?>"><img src="<?= 'uploads/news/'.$_GET['id'].'/'.$imgFile[$i][1]; ?>" alt="<?= $imgInfo[$i]['title']; ?>" class="galleryPic"></a><?
				} 
			}?>	<?
			if($module['adjuntos']=='1' && $module['limite_adj']>0 && count($file)>0)
			{?>
				<h2 class="iconSubtitle"><i class="fa fa-file"></i> <?= ADJUNTOS ?></h2><?
				foreach($file as $key=>$value)
				{ ?>
					<i class="fa fa-file"></i>&nbsp;&nbsp;<a href="<?= 'uploads/news/'.$not_pk.'/'.$value['filename']; ?>" target="_blank" title="<?= $value['description']; ?>"><?= empty($value['title']) ? $value['filename'] : $value['title']; ?></a><div class="clear10"></div><?
				}
			} ?>
			<div class="clear50"></div>
		</div>
		<aside><? include('aside.php'); ?></aside>
		<div class="clear"></div>
	</section>
</div>
<? include("footer.php"); ?>