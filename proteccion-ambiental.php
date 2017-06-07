<?php
include("lib/functions.php");
session_start();
if(isset($_GET['lang']))
	$_SESSION['lang'] = $_GET['lang'];
if(!isset($_SESSION['lang']))
	$_SESSION['lang'] = '1';
$db = database();
$sec_pk = 5;
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
$currentPage = 6;
include("header.php"); ?>
<div id="titleArea" class="centerContent">
	<h1><?= $pageTitle; ?></h1>
</div>
<div class=" whiteBg">
	<section class="centerContent">
		<div id="breadCrumbs">
			<div id="breadNav"><a href="index.php"><?= INICIO ?></a> &raquo; <?= $pageTitle;?></div>
			<a href="contactenos.php" id="breadContact"><?= CONTACTO_MAS_INFO ?></a>
		</div>		
		<div class="shadow"></div>
		<div class="clear"></div>
		<div class="contentWithSidebar editorText">
			<div class="clear20"></div>
			<?= $text[0];?>
			<div class="clear20"></div>
			<h2 class="iconSubtitle"><i class="fa fa-comments"></i> <?= COMENTARIOS ?></h2>
			<div id="fb-root" style="width:90%"></div>
			<script>(function(d, s, id) {
			  var js, fjs = d.getElementsByTagName(s)[0];
			  if (d.getElementById(id)) return;
			  js = d.createElement(s); js.id = id;
			  js.src = "//connect.facebook.net/<?= $_SESSION['lang']=='2' ? 'en_US' : 'es_LA' ?>/all.js#xfbml=1";
			  fjs.parentNode.insertBefore(js, fjs);
			}(document, 'script', 'facebook-jssdk'));</script>	
			<div class="fb-comments" data-href="http://aditivospetrolabs.com<?= $_SERVER['REQUEST_URI'];?>"></div>	
			<style>.fb_iframe_widget span[style]{width:100% !important;}</style>		
			
		</div>
		<aside>
			<h3><?= GALERIA ?></h3><?
			if($info['sec_lim_img']>0 && $numImg>0)
			{ ?>
				<div class="sideGallery"><?
					for($i=0; $i<$numImg; $i++)
					{ ?>
						<a href="<?= 'uploads/pages/'.$sec_pk.'/'.$imgFile[$i][0]; ?>" title="<?= $imgInfo[$i]['title']; ?>" class="fancybox" data-fancybox-group="pageGal"><img src="<?= 'uploads/pages/'.$sec_pk.'/'.$imgFile[$i][1]; ?>" alt="<?= $imgInfo[$i]['title']; ?>" class="galleryPic" width="90"></a>
						<?			
					}?>
				</div><?
			} ?>		
			<div class="clear"></div>
			<h3><?= COMPARTIR ?></h3>
			<!-- AddThis Button BEGIN -->
			<div class="addthis_toolbox addthis_default_style addthis_32x32_style">
			<a class="addthis_button_preferred_1"></a>
			<a class="addthis_button_preferred_2"></a>
			<a class="addthis_button_preferred_3"></a>
			<a class="addthis_button_preferred_4"></a>
			<a class="addthis_button_compact"></a>
			<a class="addthis_counter addthis_bubble_style"></a>
			</div>			
			<script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js"></script>
			<!-- AddThis Button END -->
		</aside>
		<div class="clear50"></div>		 
	</section>
</div>
<?
include("footer.php");
?>