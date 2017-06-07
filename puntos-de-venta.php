<?php
include("lib/functions.php");
session_start();
if(isset($_GET['lang']))
	$_SESSION['lang'] = $_GET['lang'];
if(!isset($_SESSION['lang']))
	$_SESSION['lang'] = '1';
$db = database();
$sec_pk = 3;
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
$currentPage = 3;
include("header.php"); ?>
<div id="titleArea" class="centerContent">
	<h1><?= $pageTitle; ?></h1>
</div>
<div class=" whiteBg">
	<section class="centerContent">
		<div id="breadCrumbs">
			<div id="breadNav"><a href="index.php"><?= INICIO ?></a> &raquo; <?= $info['title']; ?></div>
			<a href="contactenos.php" id="breadContact"><?= CONTACTO_MAS_INFO ?></a>
		</div>		
		<div class="shadow"></div>
		<div class="clear20"></div>
		<div class="editorText">
			<?= $text[0]; ?>
			<div class="clear"></div>
			<div class="mapContainer">
				<iframe src="https://mapsengine.google.com/map/u/0/embed?mid=z6S6wrlMSPGE.kurFK0hXX5Es" width="60%" height="100%"></iframe>
				<img src="http://www.aditivospetrolabs.com/uploads/images/estacion-de-servicio-mapa.jpg" height="350" width="379" style="float:right;" class="hideOnMovil" />
				<!--iframe width="100%" height="100%" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="https://maps.google.com/maps/ms?msa=0&amp;msid=214518832937204133436.00050105aa460bc06d82c&amp;ie=UTF8&amp;t=m&amp;ll=4.313546,-72.026367&amp;spn=3.833612,9.887695&amp;z=7&amp;output=embed"></iframe-->			
			</div>
			<div class="clear"></div>
			<div id="accordion">
				<h3>Aguachica</h3>
				<div><?= $text[41]; ?></div>
				<h3>Apartadó</h3>
				<div><?= $text[25]; ?></div>
				<h3>Armenia</h3>
				<div><?= $text[1]; ?></div>
				<h3>Barrancabermeja</h3>
				<div><?= $text[42]; ?></div>
				<h3>Barranquilla</h3>
				<div><?= $text[24]; ?></div>
				<h3>Bello</h3>
				<div><?= $text[35]; ?></div>
				<h3>Bogotá</h3>
				<div><?= $text[2]; ?></div>
				<h3>Bucaramanga</h3>
				<div><?= $text[43]; ?></div>
				<h3>Buenaventura</h3>
				<div><?= $text[3]; ?></div>
				<h3>Buga</h3>
				<div><?= $text[4]; ?></div>
				<h3>Caldas</h3>
				<div><?= $text[26]; ?></div>
				<h3>Candelaria</h3>
				<div><?= $text[44]; ?></div>
				<h3>Cali</h3>
				<div><?= $text[5]; ?></div>
				<h3>Carmen de Bolívar</h3>
				<div><?= $text[27]; ?></div>
				<h3>Cartagena</h3>
				<div><?= $text[6]; ?></div>
				<h3>Cereté</h3>
				<div><?= $text[7]; ?></div>
				<h3>Chía</h3>
				<div><?= $text[45]; ?></div>
				<h3>Chigorodó</h3>
				<div><?= $text[28]; ?></div>
				<h3>Coello</h3>
				<div><?= $text[46]; ?></div>
				<h3>Cogua</h3>
				<div><?= $text[47]; ?></div>
				<h3>Cota</h3>
				<div><?= $text[48]; ?></div>
				<h3>Cúcuta</h3>
				<div><?= $text[49]; ?></div>
				<h3>Dosquebradas</h3>
				<div><?= $text[8]; ?></div>
				<h3>El Cerrito</h3>
				<div><?= $text[50]; ?></div>
				<h3>El Guamo</h3>
				<div><?= $text[51]; ?></div>
				<h3>Envigado</h3>
				<div><?= $text[36]; ?></div>
				<h3>Facatativá</h3>
				<div><?= $text[52]; ?></div>
				<h3>Floridablanca</h3>
				<div><?= $text[53]; ?></div>
				<h3>Funza</h3>
				<div><?= $text[38]; ?></div>
				<h3>Galapa</h3>
				<div><?= $text[39]; ?></div>
				<h3>Girardot</h3>
				<div><?= $text[21]; ?></div>
				<h3>Girón</h3>
				<div><?= $text[55]; ?></div>
				<h3>Guarne</h3>
				<div><?= $text[56]; ?></div>
				<h3>Ibagué</h3>
				<div><?= $text[20]; ?></div>
				<h3>Itagüí</h3>
				<div><?= $text[57]; ?></div>
				<h3>La Calera</h3>
				<div><?= $text[58]; ?></div>
				<h3>La Dorada</h3>
				<div><?= $text[59]; ?></div>
				<h3>La Tebaida</h3>
				<div><?= $text[9]; ?></div>
				<h3>Magangué</h3>
				<div><?= $text[40]; ?></div>
				<h3>Mahates</h3>
				<div><?= $text[29]; ?></div>
				<h3>Manizales</h3>
				<div><?= $text[54]; ?></div>
				<h3>Marinilla</h3>
				<div><?= $text[10]; ?></div>
				<h3>Medellín</h3>
				<div><?= $text[11]; ?></div>
				<h3>Melgar</h3>
				<div><?= $text[60]; ?></div>
				<h3>Montería</h3>
				<div><?= $text[12]; ?></div>
				<h3>Mosquera</h3>
				<div><?= $text[19]; ?></div>
				<h3>Neiva</h3>
				<div><?= $text[13]; ?></div>
				<h3>Palmira</h3>
				<div><?= $text[22]; ?></div>
				<h3>Pasto</h3>
				<div><?= $text[14]; ?></div>
				<h3>Pelaya (Cesar)</h3>
				<div><?= $text[61]; ?></div>
				<h3>Pereira</h3>
				<div><?= $text[15]; ?></div>
				<h3>Pitalito</h3>
				<div><?= $text[16]; ?></div>
				<h3>Planeta Rica</h3>
				<div><?= $text[17]; ?></div>
				<h3>Ponedera</h3>
				<div><?= $text[62]; ?></div>
				<h3>Popayán</h3>
				<div><?= $text[63]; ?></div>
				<h3>Puerto Parra (Santander)</h3>
				<div><?= $text[64]; ?></div>
				<h3>Rionegro</h3>
				<div><?= $text[65]; ?></div>
				<h3>Sabaneta</h3>
				<div><?= $text[66]; ?></div>
				<h3>San Alberto (Cesar)</h3>
				<div><?= $text[67]; ?></div>
				<h3>Santa Marta</h3>
				<div><?= $text[68]; ?></div>
				<h3>Santafé de Antioquia</h3>
				<div><?= $text[69]; ?></div>
				<h3>Santander de Quilichao</h3>
				<div><?= $text[70]; ?></div>
				<h3>Santuario</h3>
				<div><?= $text[30]; ?></div>
				<h3>Silvania</h3>
				<div><?= $text[71]; ?></div>
				<h3>Sincelejo</h3>
				<div><?= $text[37]; ?></div>
				<h3>Soacha</h3>
				<div><?= $text[72]; ?></div>
				<h3>Sogamoso</h3>
				<div><?= $text[73]; ?></div>
				<h3>Sopetrán</h3>
				<div><?= $text[31]; ?></div>
				<h3>Tenjo</h3>
				<div><?= $text[74]; ?></div>
				<h3>Turbo</h3>
				<div><?= $text[32]; ?></div>
				<h3>Valledupar</h3>
				<div><?= $text[18]; ?></div>
				<h3>Valparaiso</h3>
				<div><?= $text[33]; ?></div>
				<h3>Villa Rica</h3>
				<div><?= $text[75]; ?></div>
				<h3>Villamaría</h3>
				<div><?= $text[76]; ?></div>
				<h3>Villavicencio</h3>
				<div><?= $text[77]; ?></div>
				<h3>Yopal</h3>
				<div><?= $text[78]; ?></div>
				<h3>Yotoco</h3>
				<div><?= $text[34]; ?></div>
				<h3>Yumbo</h3>
				<div><?= $text[23]; ?></div>
			</div>				
		</div>
		<div class="clear50"></div>
	</div>
</section>
<script type="text/javascript">
	$(document).ready(function(){
		$('.editorText table tr:odd td').addClass('grayTd');	
	});
</script>
 <script>
	$(function() {
		$( "#accordion" ).accordion({
			'collapsible':true,
			'active':false,
			'heightStyle':'content'	
		});
	});
</script>
<? include("footer.php"); ?>