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
$pk = 2;
$not_pk = $_GET['id'];
$module = $db->fetch_array($db->select('*', 'modulo_noticias', "pk='$pk'"));
$dimensions = explode(',', str_replace(' ', '', $module['tamano_img']));
$info = $imgInfo = $imgFile = $file = array();  // Vectores para datos básicos del registro, imágenes y archivos adjuntos.
$condi = "noticia.not_pk='$not_pk' AND noticia.not_pk=noticia_txt.not_pk AND idi_pk='$_SESSION[lang]'";
$info = $db->fetch_array($db->select('not_fecha AS date, ntx_titulo AS title, ntx_resumen AS summary, ntx_contenido AS content', 'noticia, noticia_txt', $condi));
if($module['fecha']=='1')
{
	$date = explode('-', $info['date']);
	$info['date'] = date('F j, Y', mktime(0, 0, 0, intval($date[1]), intval($date[2]), intval($date[0])));
	if($_SESSION['lang']=='1')
		$info['date'] = date_to_spanish($info['date']);
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

// Cinco noticias/eventos
$info2 = array();  // Vectores para datos básicos e imágenes del registro.
if($module['orden']=='2')
	$order = 'not_fecha DESC, noticia.not_pk DESC';
elseif($module['orden']=='3')
	$order = 'not_fecha ASC, noticia.not_pk ASC';
else  // $module['orden']=='1'
	$order = 'ntx_titulo ASC';
$condi = "pk='$pk' AND noticia.not_pk=noticia_txt.not_pk AND idi_pk='$_SESSION[lang]' ORDER BY $order LIMIT 5";
$result = $db->select('noticia.not_pk AS id, not_fecha AS date, ntx_titulo AS title', 'noticia, noticia_txt', $condi);
$numRows2 = $db->num_rows($result);
for($i=0; $i<$numRows2; $i++)
{
	$info2[$i] = $db->fetch_array($result);
	$date = explode('-', $info2[$i]['date']);
	$info2[$i]['date'] = date('F j, Y', mktime(0, 0, 0, $date[1], $date[2], $date[0]));
	if($_SESSION['lang']=='1')
		$info2[$i]['date'] = date_to_spanish($info2[$i]['date']);
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
include("header.php"); ?>
<div id="titleArea" class="centerContent">
	<h1><?= $info['title']; ?></h1>
</div>
<div class=" whiteBg">
	<section class="centerContent">	
		<div id="breadCrumbs">
			<div id="breadNav"><a href="index.php"><?= INICIO ?></a> &raquo; <a href="noticias-y-eventos.php"><?= NOTICIAS_EVENTOS ?></a> &raquo; <?= $info['title']; ?></div>
			<a href="contactenos.php" id="breadContact"><?= CONTACTO_MAS_INFO ?></a>
		</div>		
		<div class="shadow"></div>
		<div class="contentWithSidebar">		
			<div class="newsContent">
				<h1><?= $info['title']; ?></h1>
				<div class="ncDate"><i class="fa fa-calendar-o"></i> <?= $info['date']; ?></div>
				<div class="clear20"></div>
				<div class="editorText">
					<?= $info['content']; ?>
				</div><?
				if($numImg>0)
				{?>					
					<h2 class="iconSubtitle"><i class="fa fa-camera-retro"></i> <?= IMAGENES ?></h2><?
					for($i=0; $i<$numImg; $i++)
					{ ?>
						<a href="<?= 'uploads/news/'.$_GET['id'].'/'.$imgFile[$i][0] ?>" title="<?= $imgInfo[$i]['title']; ?>" class="fancybox" rel="ibnews"><img src="<?= 'uploads/news/'.$_GET['id'].'/'.$imgFile[$i][2]; ?>" alt="<?= $imgInfo[$i]['title']; ?>" class="galleryPic"></a><?
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
				<h2 class="iconSubtitle"><i class="fa fa-comments"></i> <?= COMENTARIOS ?></h2>
				<div id="fb-root"></div>
				<script>(function(d, s, id) {
				  var js, fjs = d.getElementsByTagName(s)[0];
				  if (d.getElementById(id)) return;
				  js = d.createElement(s); js.id = id;
				  js.src = "//connect.facebook.net/es_LA/all.js#xfbml=1";
				  fjs.parentNode.insertBefore(js, fjs);
				}(document, 'script', 'facebook-jssdk'));</script>	
				<div class="fb-comments" data-href="http://aditivospetrolabs.com<?= $_SERVER['REQUEST_URI'];?>" data-width="675"></div>			
			</div>
		</div>
		<aside>				
			<h3><?= NOTICIAS_EVENTOS ?></h3><?
			for($i=0; $i<$numRows2; $i++)
			{?>			
				<div class="sideNews">
					<h4><a href="<?= $module['archivo'].'-single.php?id='.$info2[$i]['id']; ?>"><?= $info2[$i]['title'] ?></a></h4>
					<span><?= $info2[$i]['date'] ?></span> / <span><a href="<?= $module['archivo'].'-single.php?id='.$info2[$i]['id']; ?>"><?= VER_MAS ?></a></span>
				</div><?
			}?>
			<?
			if($row = $db->fetch_array($db->select('pk, nombre, archivo', 'modulo_noticias', "calendario='1'")))
			{ ?>
				<div class="clear20"></div>
				<div align="center" style="text-align:center; margin:0 35px">
					<div id="datepicker"></div>						
				</div>
				<script type="text/javascript">
				<!--
					$(document).ready(function(){
						$.ajax({
							type: 'POST',
							url: 'calendar-ajax.php',
							data: 'pk=<?= $row['pk']; ?>',
							dataType: 'json',
							success: function(resp){
								var events = [];
								for(var k in resp){
									events[k] = {Date: new Date(resp[k])};
								} <?
								if($_SESSION['lang']=='1')
								{ ?>
									$.datepicker.setDefaults($.datepicker.regional["es"]); <?
								} ?>
								$("#datepicker").datepicker({
									beforeShowDay: function(date){
										var result = [true, '', null];
										var matching = $.grep(events, function(event){
											return event.Date.valueOf()===date.valueOf();
										});
										if(matching.length){
											result = [true, 'highlight', null];
										}
										return result;
									},
									onSelect: function(dateText){
										var date,
											selectedDate = new Date(dateText),
											i = 0,
											event = null;
	
										/* Determine if the user clicked an event: */
										while(i<events.length && !event){
											date = events[i].Date;
											if(selectedDate.valueOf()===date.valueOf()){
												event = events[i];
											}
											i++;
										}
										if(event){
											/* If the event is defined, perform some action here; show a tooltip, navigate to a URL, etc. */
											var eDate = $.datepicker.formatDate("yy-mm-dd", event.Date);
											$.ajax({
												type: 'POST',
												url: 'calendar-ajax.php',
												data: 'pk=<?= $row['pk']; ?>&not_fecha='+eDate,
												success: function(resp){
													if(resp){
														window.location = '<?= $row['archivo']; ?>-single.php?id='+resp;
													}
													else{
														window.location = '<?= $row['archivo']; ?>.php?date='+eDate;
													}
												}
											});
										}
									}
								});
							}
						});
					});
				-->
				</script> <?
			} ?>				
		</aside>
		<div class="clear"></div>
</table>
	</section>
</div>
<? include("footer.php"); ?>