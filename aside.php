<?php
// Cinco productos al azar
$numRows1 = 0;
if($db->num_rows($db->select('not_pk', 'noticia', 'pk=1'))>0)
{
	$module1 = $db->fetch_array($db->select('tamano_img, archivo', 'modulo_noticias', "pk=1"));
	$dimensions = explode(',', str_replace(' ', '', $module1['tamano_img']));
	$info1 = $imgInfo1 = $imgFile1 = array();  // Vectores para datos básicos e imágenes del registro.
	$condi = "pk=1 AND noticia.not_pk=noticia_txt.not_pk AND idi_pk='$_SESSION[lang]' ORDER BY RAND() LIMIT 5";
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

// Tres noticias/eventos
$info2 = array();
$module2 = $db->fetch_array($db->select('orden, archivo', 'modulo_noticias', "pk=2"));
if($module2['orden']=='2')
	$order = 'not_fecha DESC, noticia.not_pk DESC';
elseif($module2['orden']=='3')
	$order = 'not_fecha ASC, noticia.not_pk ASC';
else  // $module['orden']=='1'
	$order = 'ntx_titulo ASC';
$condi = "pk=2 AND noticia.not_pk=noticia_txt.not_pk AND idi_pk='$_SESSION[lang]' ORDER BY $order LIMIT 3";
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
?>
<div class="productsSidebar">
	<h3 style="margin-top:0"><?= PRODUCTOS ?></h3><?
	for($i=0; $i<$numRows1; $i++)
	{?>
		<div class="sideNews">
			<h4><a href="<?= $module1['archivo'].'-single.php?id='.$info1[$i]['id']; ?>">&raquo; <?= $info1[$i]['title'] ?></a></h4>
		</div><?
	}?>
</div>
<div class="clear50"></div>
<h3><?= ULTIMAS_NOTICIAS ?></h3><?
for($i=0; $i<$numRows2; $i++)
{?>
	<div class="sideNews">
		<h4><a href="<?= $module2['archivo'].'-single.php?id='.$info2[$i]['id']; ?>"><?= $info2[$i]['title'] ?></a></h4>
		<span><?= $info2[$i]['date'] ?> / <span><a href="<?= $module2['archivo'].'-single.php?id='.$info2[$i]['id']; ?>"><?= VER_MAS ?></a></span>
	</div><?
}?>
