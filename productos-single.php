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
$pk = 1;
$not_pk = $_GET['id'];
$module = $db->fetch_array($db->select('*', 'modulo_noticias', "pk='$pk'"));
$dimensions = explode(',', str_replace(' ', '', $module['tamano_img']));
$info = $imgInfo = $imgFile = $file = array();  // Vectores para datos básicos del registro, imágenes y archivos adjuntos.
$condi = "noticia.not_pk='$not_pk' AND noticia.not_pk=noticia_txt.not_pk AND idi_pk='$_SESSION[lang]'";
$info = $db->fetch_array($db->select('not_fecha AS date, ntx_titulo AS title, ntx_resumen AS summary, ntx_contenido AS content, uso, demo, ficha, sulfuro, info_tecnica, beneficios, usos', 'noticia, noticia_txt', $condi));
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
$pageTitle = $info['title']; ?> <?
$currentPage = 4;
include("header.php"); ?>
<div id="titleArea" class="centerContent">
	<h1><?= $info['title']; ?></h1>
</div>
<div class=" whiteBg">
	<section class="centerContent" id="productPage">	
		<div id="breadCrumbs">
			<div id="breadNav"><a href="index.php"><?= INICIO ?></a> &raquo; <a href="productos.php"><?= PRODUCTOS ?></a> &raquo; <?= $info['title']; ?></div>
			<a href="contactenos.php" id="breadContact"><?= CONTACTO_MAS_INFO ?></a>
		</div>		
		<div class="shadow"></div>
		<div class="clear20"></div>
		<div id="fullProdPic">
			<div class="firstPhoto">
			
			</div>		
			<div class="pagePhotos"><?
				if($numImg>0)
				{
					for($i=0; $i<$numImg; $i++)
					{ ?>
						<a data-url="<?= 'uploads/news/'.$_GET['id'].'/'.$imgFile[$i][3] ?>" title="<?= $imgInfo[$i]['title']; ?>">
							<img src="<?= 'uploads/news/'.$_GET['id'].'/'.$imgFile[$i][2]; ?>" alt="<?= $imgInfo[$i]['title']; ?>">
						</a><?
					}
				} ?>
				<div class="clear"></div>
			</div>			
		</div>
		<div id="fullProdOverview">
			<h1><?= $info['title']; ?></h1>
			<div class="clear10"></div>
			<? /*<span>Referencia: XYZ1234</span>*/?>
			<div class="editorText">
				<?= $info['summary']; ?>
			</div>
			<div class="pIndustry">
				<?= USO ?>: <?= $info['uso'] ?>
			</div>
			<!-- AddThis Button BEGIN -->
			<div class="addthis_toolbox addthis_default_style ">
			<a class="addthis_button_preferred_1"></a>
			<a class="addthis_button_preferred_2"></a>
			<a class="addthis_button_preferred_3"></a>
			<a class="addthis_button_preferred_4"></a>
			<a class="addthis_button_compact"></a>
			<a class="addthis_counter addthis_bubble_style"></a>
			</div>
			<script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-4e97b9c927e9cf8d"></script>
			<!-- AddThis Button END -->
			<div class="pLinkMore">
				<?= MAS_INFO ?> <a href="contactenos.php"><?= CONTACTO_PRODUCTO ?></a>
			</div> 
		</div>
		<div class="clear20"></div>
		<div id="tabs">
			<ul>
				<? if(!empty($info['content'])) { ?><li><a href="#tabs-1"><?= DESCRIPCION ?></a></li><? } ?>
				<? if(!empty($info['demo'])) { ?><li><a href="#tabs-2"><?= DEMO ?></a></li><? } ?>
				<? if(!empty($info['ficha'])) { ?><li><a href="#tabs-3"><?= FICHA ?></a></li><? } ?>
				<? if(!empty($info['beneficios'])) { ?><li><a href="#tabs-4"><?= BENEFICIOS ?></a></li><? } ?>
				<? if(!empty($info['usos'])) { ?><li><a href="#tabs-5"><?= USOS ?></a></li><? } ?>
				<? if(!empty($info['sulfuro'])) { ?><li><a href="#tabs-6"><?= SULFURO ?></a></li><? } ?>
				<? if(!empty($info['info_tecnica'])) { ?><li><a href="#tabs-7"><?= INFO_TECNICA ?></a></li><? } ?>
				<? if(count($file)>0) { ?><li><a href="#tabs-9"><?= ADJUNTOS ?></a></li><? } ?>
				<li><a href="#tabs-8"><?= COMENTARIOS ?></a></li>
			</ul> <?
			if(!empty($info['content']))
			{ ?>
				<div id="tabs-1">
					<div class="editorText"><? 
						if($_GET['id']==9)
						{ ?>
							<img src="images/petrokool_foto.jpg" alt="" style="float:right; margin:5px 0 0 12px"><?
						}?>					
						<?= $info['content']; ?>
						<div class="clear"></div>
					</div>				
				</div> <?
			}
			if(!empty($info['demo']))
			{ ?>
				<div id="tabs-2">
					<iframe width="915" height="514" src="//www.youtube.com/embed/<?= youtube_id_from_url("$info[demo]"); ?>?rel=0&autohide=1&showinfo=0" frameborder="0" allowfullscreen></iframe>
				</div> <?
			}
			if(!empty($info['ficha']))
			{ ?>
				<div id="tabs-3" class="editorText">
					<?= $info['ficha'] ?>
				</div> <?
			}
			if(!empty($info['beneficios']))
			{ ?>
				<div id="tabs-4" class="editorText">
					<?= $info['beneficios'] ?>
				</div> <?
			}
			if(!empty($info['usos']))
			{ ?>
				<div id="tabs-5" class="editorText">
					<?= $info['usos'] ?>
				</div> <?
			}
			if(!empty($info['sulfuro']))
			{ ?>
				<div id="tabs-6" class="editorText">
					<?= $info['sulfuro'] ?>
				</div> <?
			}
			if(!empty($info['info_tecnica']))
			{ ?>
				<div id="tabs-7" class="editorText">
					<?= $info['info_tecnica'] ?>
				</div> <?
			}
			if(count($file)>0)
			{ ?>
				<div id="tabs-9" class="editorText">
					<ul><?
						foreach($file as $f)
						{ ?>
							<li><a href="/uploads/news/<?= $_GET['id']; ?>/<?= $f['filename']; ?>" target="_blank" title="<?= $f['title']; ?>"><?= $f['title']!='' ? $f['title'] : $f['filename']; ?></a></li><?
						} ?>
					</ul>
				</div> <?
			} ?>
			<div id="tabs-8">
				<div id="fb-root"></div>
				<script>(function(d, s, id) {
				  var js, fjs = d.getElementsByTagName(s)[0];
				  if (d.getElementById(id)) return;
				  js = d.createElement(s); js.id = id;
				  js.src = "//connect.facebook.net/<?= $_SESSION['lang']=='2' ? 'en_US' : 'es_LA' ?>/all.js#xfbml=1";
				  fjs.parentNode.insertBefore(js, fjs);
				}(document, 'script', 'facebook-jssdk'));</script>	
				<div class="fb-comments" data-href="http://aditivospetrolabs.com<?= $_SERVER['REQUEST_URI'];?>"></div>			
				<style>.fb_iframe_widget span[style]{width:95% !important;}</style>
			</div>
		</div>
		<div class="clear50"></div>				
	</section>
</div>
<script type="text/javascript">
<!--
	$(window).load(function(){ 
		$(".firstPhoto").html($('.pagePhotos').find('a:eq(0)').clone());
		$(".firstPhoto img").fadeIn(400);
		$('.firstPhoto a').addClass('fancybox');
		$('.firstPhoto a').attr('href',$('.firstPhoto a').attr('data-url')); <?
		if($numImg>1)
		{ ?>
			$('.pagePhotos').show();<?
		}?>		
	});
	
	$(document).ready(function(){
		
		$( "#tabs" ).tabs();
				 
		$('.pagePhotos a').click(function(){
			$(".firstPhoto a img").fadeOut(400);
			$(".firstPhoto").html("");			
			$(".firstPhoto").html($('.pagePhotos').find('a:eq('+$('.pagePhotos a').index($(this))+')').clone());
			$(".firstPhoto img").fadeIn(400);
			$('.firstPhoto a').addClass('fancybox');
			$('.firstPhoto a').attr('href',$('.firstPhoto a').attr('data-url'));			
		});				
	});
	
-->
</script>
<? include("footer.php"); ?>