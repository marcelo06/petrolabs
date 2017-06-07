<?php
include("lib/functions.php");
session_start();
if(isset($_GET['lang']))
	$_SESSION['lang'] = $_GET['lang'];
if(!isset($_SESSION['lang']))
	$_SESSION['lang'] = '1';
$db = database();
include("lang/".language($db).".php");
$pk = newsId;
$module = $db->fetch_array($db->select('*', 'modulo_noticias', "pk='$pk'"));
$dateCondi = isset($_GET['date']) ? "AND not_fecha='$_GET[date]'" : '';  // Viene del calendario o no.
$totalRows = $db->num_rows($db->select('not_pk', 'noticia', "pk='$pk' $dateCondi"));
$numRows = 0;
if($totalRows>0)
{
	$dimensions = explode(',', str_replace(' ', '', $module['tamano_img']));
	$info = $imgInfo = $imgFile = array();  // Vectores para datos básicos e imágenes del registro.
	$offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
	if($module['orden']=='2')
		$order = 'not_fecha DESC, noticia.not_pk DESC';
	elseif($module['orden']=='3')
		$order = 'not_fecha ASC, noticia.not_pk ASC';
	else  // $module['orden']=='1'
		$order = 'ntx_titulo ASC';
	$condi = "pk='$pk' $dateCondi AND noticia.not_pk=noticia_txt.not_pk AND idi_pk='$_SESSION[lang]' ORDER BY $order LIMIT $module[paginador] OFFSET $offset";
	$result = $db->select('noticia.not_pk AS id, not_fecha AS date, ntx_titulo AS title, ntx_resumen AS summary', 'noticia, noticia_txt', $condi);
	$numRows = $db->num_rows($result);
	for($i=0; $i<$numRows; $i++)
	{
		$info[$i] = $db->fetch_array($result);
		if($module['fecha']=='1')
		{
			$date = explode('-', $info[$i]['date']);
			$info[$i]['date'] = "$date[2]/$date[1]/$date[0]";
		}
		if($module['limite_img']>0)
		{
			$condi = 'not_pk='.$info[$i]['id'].' AND noi_archivo!=\'\' AND ni.noi_pk=nit.noi_pk AND idi_pk='.$_SESSION['lang'].' ORDER BY ni.noi_pk LIMIT 1';
			$imgInfo[$i] = $db->fetch_array($db->select('noi_archivo AS filename, titulo AS title, descripcion AS description', 'noticia_img AS ni, noi_txt AS nit', $condi));
			if($imgInfo[$i])  // El registro tiene por lo menos una imagen asociada
			{
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
}
$db->disconnect();
/*
 * $numRows: número total de registros.
 * $info[$i]['id']: llave primaria del registro $i.
 * $info[$i]['date']: fecha del registro $i.
 * $info[$i]['title']: título del registro $i.
 * $info[$i]['summary']: resumen del registro $i.
 * $imgInfo[$i]['filename']: nombre de archivo de la primera imagen del registro $i.
 * $imgInfo[$i]['title']: título de la primera imagen del registro $i.
 * $imgInfo[$i]['description']: descripción de la primera imagen del registro $i.
 * $imgFile[$i][$j]: primera imagen del registro $i en los diferentes tamaños; $j es un valor entre 0 y la cantidad de tamaños configurada menos 1.
 */
$pageTitle = ''; ?> <?
include("header.php"); ?>
<h1><?= $pageTitle; ?></h1> <?
for($i=0; $i<$numRows; $i++)
{ ?>
	<table border="1"> <?
		if($module['fecha']=='1')
		{ ?>
			<tr>
				<td>Fecha:</td>
				<td><?= $info[$i]['date']; ?></td>
			</tr> <?
		} ?>
		<tr>
			<td>Título:</td>
			<td><?= $info[$i]['title']; ?></td>
		</tr> <?
		if($module['resumen']=='1')
		{ ?>
			<tr>
				<td>Resumen:</td>
				<td><?= $info[$i]['summary']; ?></td>
			</tr> <?
		}
		if($module['limite_img']>0)
		{ ?>
			<tr>
				<td>Primera imagen:</td>
				<td> <?
					if($imgInfo[$i])  // El registro tiene por lo menos una imagen asociada
					{
						foreach($imgFile[$i] as $value)  // Un archivo por cada tamaño de imagen configurado
						{ ?>
							<img src="<?= 'uploads/news/'.$info[$i]['id'].'/'.$value; ?>" alt="" title="<?= $imgInfo[$i]['title']; ?>"> <?
						}
					}
					else
					{
						echo 'sin imagen';
					} ?>
				</td>
			</tr> <?
		} ?>
		<tr>
			<td>Información detallada:</td>
			<td><a href="<?= $module['archivo'].'-single.php?id='.$info[$i]['id']; ?>">Ver más</a></td>
		</tr>
	</table>
	<br> <?
}
if($pag=pager($totalRows, $module['paginador'], $offset))  // Si es necesario paginar
{
	$varDate = isset($_GET['date']) ? "date=$_GET[date]&amp;" : '';  // Viene del calendario o no.
	$prev = is_null($pag['prevOffset']) ? PAGER_PREVIOUS : "<a href=\"$_SERVER[PHP_SELF]?".$varDate."offset=$pag[prevOffset]\">".PAGER_PREVIOUS."</a>";
	$next = is_null($pag['nextOffset']) ? PAGER_NEXT : "<a href=\"$_SERVER[PHP_SELF]?".$varDate."offset=$pag[nextOffset]\">".PAGER_NEXT."</a>"; ?>
	<div>
		<div><?= PAGER_FROM." $pag[from] ".PAGER_TO." $pag[to] ".PAGER_OF." $totalRows ".PAGER_RECORDS; ?></div>
		<div>
			<?= $prev; ?>
			<select onchange="javascript:window.location='<?= "$_SERVER[PHP_SELF]?".$varDate."offset="; ?>'+this.value"> <?
				foreach($pag['pages'] as $num=>$val)
				{
					$selected = $val['selected'] ? ' selected' : ''; ?>
					<option value="<?= $val['offset']; ?>"<?= $selected; ?>><?= $num; ?></option> <?
				} ?>
			</select>
			<?= $next; ?>
		</div>
		<br>
	</div> <?
}
include("footer.php");
?>