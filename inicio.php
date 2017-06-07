<?php
include("lib/functions.php");
session_start();
if(isset($_GET['lang']))
	$_SESSION['lang'] = $_GET['lang'];
if(!isset($_SESSION['lang']))
	$_SESSION['lang'] = '1';
$db = database();
$sec_pk = 1;
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

// 5 noticias
$pk = 2;
$module2 = $db->fetch_array($db->select('orden, fecha, archivo', 'modulo_noticias', "pk='$pk'"));
$numRows2 = 0;
if($db->num_rows($db->select('not_pk', 'noticia', "pk='$pk'"))>0)
{
	$info2 = array();
	if($module2['orden']=='2')
		$order = 'not_fecha DESC, noticia.not_pk DESC';
	elseif($module2['orden']=='3')
		$order = 'not_fecha ASC, noticia.not_pk ASC';
	else  // $module2['orden']=='1'
		$order = 'ntx_titulo ASC';
	$condi = "pk='$pk' AND noticia.not_pk=noticia_txt.not_pk AND idi_pk='$_SESSION[lang]' ORDER BY $order LIMIT 5 OFFSET 0";
	$result = $db->select('noticia.not_pk AS id, not_fecha AS date, ntx_titulo AS title', 'noticia, noticia_txt', $condi);
	$numRows2 = $db->num_rows($result);
	for($i=0; $i<$numRows2; $i++)
	{
		$info2[$i] = $db->fetch_array($result);
		if($module2['fecha']=='1')
		{
			$date = explode('-', $info2[$i]['date']);
			$info2[$i]['date'] = date('F j, Y', mktime(0, 0, 0, intval($date[1]), intval($date[2]), intval($date[0])));
			if($_SESSION['lang']=='1')
				$info2[$i]['date'] = date_to_spanish($info2[$i]['date']);
		}
	}
}

// Primeros productos (4)
$numRows1 = 0;
if($db->num_rows($db->select('not_pk', 'noticia', 'pk=1'))>0)
{
	$module1 = $db->fetch_array($db->select('tamano_img', 'modulo_noticias', "pk=1"));
	$dimensions = explode(',', str_replace(' ', '', $module1['tamano_img']));
	$info1 = $imgInfo1 = $imgFile1 = array();  // Vectores para datos básicos e imágenes del registro.
	$condi = "pk=1 AND noticia.not_pk=noticia_txt.not_pk AND idi_pk='$_SESSION[lang]' AND noticia.not_pk IN (6,10,13,8)";
	$result = $db->select('noticia.not_pk AS id, ntx_titulo AS title, ntx_resumen AS summary', 'noticia, noticia_txt', $condi);
	$numRows1 = $db->num_rows($result);
	for($i=0; $i<$numRows1; $i++)
	{
		$info1[$i] = $db->fetch_array($result);
		$condi = 'not_pk='.$info1[$i]['id'].' AND noi_archivo!=\'\' AND ni.noi_pk=nit.noi_pk AND idi_pk='.$_SESSION['lang'].' ORDER BY ni.noi_pk LIMIT 1';
		$imgInfo1[$i] = $db->fetch_array($db->select('noi_archivo AS filename, titulo AS title, descripcion AS description', 'noticia_img AS ni, noi_txt AS nit', $condi));
		if($imgInfo1[$i])  // El registro tiene por lo menos una imagen asociada
		{
			$pos = mb_strrpos($imgInfo1[$i]['filename'], '.');
			$ini = mb_substr($imgInfo1[$i]['filename'], 0, $pos);
			$ext = mb_substr($imgInfo1[$i]['filename'], $pos);
			foreach($dimensions as $key=>$value)  // Un archivo por cada tamaño de imagen configurado
			{
				$imgFile1[$i][$key] = $ini.'--'.$value.$ext;
			}
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
 * $subTitle[$i]: título de la subpágina $i.
 * $subFile[$i]: nombre de archivo de la subpágina $i.
 */
$pageTitle = $info['title']; ?> <?
$currentPage = 1;
include("header.php"); ?>
<div class="whiteBg">
	<section id="home" class="centerContent">
		<div id="recentNews">
			<div id="rnHolder">
				<div id="sNews"> <?
					for($i=0; $i<$numRows2; $i++)
					{ ?>
						<div class="newsTitle"><a href="<?= $module2['archivo'].'-single.php?id='.$info2[$i]['id'] ?>"><?= $info2[$i]['title'] ?></a></div> <?
					} ?>
				</div>
				<div class="clear"></div>
			</div>
			<div id="newsNav">
				<a id="rnPrev" class="nDisabled">&lt;</a>
				<a id="rnNext">&gt;</a>					
			</div>
			<a href="noticias-y-eventos.php" id="viewAll"><?= VER_NOTICIAS ?></a>
			<div class="clear"></div>
		</div>
		<div class="shadow"></div>
		<div id="homeProducts"> <?
			for($i=0; $i<$numRows1; $i++)
			{ ?>
				<div class="hProduct" id="hProduct<?= $i+1;?>" data-url="productos-single.php?id=<?= $info1[$i]['id'] ?>">
					<h2><?= $info1[$i]['title'] ?></h2>
					<div class="picHolder" style="background-image:url(<?=  'uploads/news/'.$info1[$i]['id'].'/'.$imgFile1[$i][2] ?>)"></div>					
					<div class="clear"></div>
				</div> <?
			} ?>
			<div class="clear"></div>
		</div>
	</section>
</div>
<script type="text/javascript">
	$(document).ready(function(){
		$('#homeProducts .hProduct:last').addClass('noMargin');	
		
		$('.hProduct').click(function(){
			$(location).attr('href', $(this).attr('data-url'));		
		});
		
	});
</script>
<script type="text/javascript">
<!--
	$(window).load(function(){
			
		/* Slider de noticias */

		slideWidth = $('#sNews').width();
		var currentNav = 0;
		var pages = $('#sNews .newsTitle').length;

		function moveAnimation(currentDot)
		{
			$('#sNews').animate({left:-(currentDot*slideWidth)},600);
			currentNav = currentDot;
			if(currentNav>0)
				$('#rnPrev').removeClass('nDisabled');
			else
				$('#rnPrev').addClass('nDisabled');

			if(currentNav==pages-1)
				$('#rnNext').addClass('nDisabled');
			else
				$('#rnNext').removeClass('nDisabled');
		}

		function autoAnimate()
		{
			intervalId=setInterval(function(){
				if(currentNav < pages - 1)
				{
					currentNav++;
				}
				else
				{
					currentNav = 0;
				}
				moveAnimation(currentNav);
			},7000);
		}

		$('#sNews').css({width:pages*slideWidth});		
		if(pages==1)
			$('#rnNext').addClass('nDisabled');
		
		autoAnimate();
		
		// Flechas
		$('#rnNext').click(function(){
			if($(this).hasClass('nDisabled'))
			{
				e.preventDefault();
			}
			else
			{
				clearInterval(intervalId);
				moveAnimation(currentNav+1);
				autoAnimate();
			}
		});

		$('#rnPrev').click(function(e){
			if($(this).hasClass('nDisabled'))
			{
				e.preventDefault();
			}
			else
			{
				clearInterval(intervalId);
				moveAnimation(currentNav-1);
				autoAnimate();
			};
		});
		
		$('.newsTitle a').hover(function(){
			clearInterval(intervalId);
		},function(){
			autoAnimate();
		});

	});
-->
</script>
<? include("footer.php"); ?>