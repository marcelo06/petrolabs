<?php
include("lib/functions.php");
session_start();
if(isset($_GET['lang']))
	$_SESSION['lang'] = $_GET['lang'];
if(!isset($_SESSION['lang']))
	$_SESSION['lang'] = '1';
$db = database();
include("lang/".language($db).".php");
$pk = 4;
$module = $db->fetch_array($db->select('*', 'modulo_noticias', "pk='$pk'"));
$dateCondi = isset($_GET['date']) ? "AND not_fecha='$_GET[date]'" : '';  // Viene del calendario o no.
$categCondi = isset($_GET['id']) ? "AND (categoria='$_GET[id]' OR subcategoria='$_GET[id]')" : '';  // Obtener solamente los productos de la categoría o subcategoría dada.
$totalRows = $db->num_rows($db->select('not_pk', 'noticia', "pk='$pk' $categCondi $dateCondi"));
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
	$condi = "pk='$pk' $categCondi $dateCondi AND noticia.not_pk=noticia_txt.not_pk AND idi_pk='$_SESSION[lang]' ORDER BY $order LIMIT $module[paginador] OFFSET $offset";
	$result = $db->select('noticia.not_pk AS id, not_fecha AS date, categoria, subcategoria, ntx_titulo AS title, ntx_resumen AS summary', 'noticia, noticia_txt', $condi);
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

	$sesion = FALSE;
		if ($_GET['login']=='out') 
		{
			unset($_POST['form']);
			unset($_SESSION['nombre']);
			unset($_SESSION['email']);
			unset($_SESSION['id']);
			unset($_SESSION['eds']);
		

?>
					<script>
						document.location.href="login-catalogo-deseos.php";				
					</script>		
<?php 
		}
			
			
		if (isset($_POST['form'])) 
		{
					
			$email = $_POST['username'];
			$password = $_POST['password'];

			$condi = "email = '$email' AND contrasena = '$password'";
			
			$filas = $db->num_rows($db->select('*','eds_users', $condi));	
			
			if ($filas > 0) 
			{
				unset($_POST['form']);
				$user= array();
				$user = $db->fetch_assoc($db->select('*','eds_users', $condi));
				$_SESSION['nombre']= $user['nombre_completo'];
				$_SESSION['email'] = $user['email'];
				$_SESSION['id']= $user['id_eds_user'];
				$_SESSION['eds'] = $user['eds_id'];

				$sesion=TRUE;
	
			}
			else
			{

?>
			<script>
				alert("Los datos que ha ingresado no son correctos. Por favor intente de nuevo");
					document.location.href="login-catalogo-deseos.php";				
			</script>		
<?php 
			}
			
		}elseif (!empty($_SESSION['nombre'])) {
			
			$nombre = $_SESSION['nombre'];
			$condi = "nombre_completo = '$nombre'";
			
			$filas = $db->num_rows($db->select('*','eds_users', $condi));	
			
				if ($filas > 0) {
					unset($_POST['form']);
					$user= array();
					$user = $db->fetch_assoc($db->select('*','eds_users', $condi));
					$_SESSION['nombre']= $user['nombre_completo'];
					$_SESSION['email'] = $user['email'];
					$_SESSION['id']= $user['id_eds_user'];
					$_SESSION['eds'] = $user['eds_id'];
					$sesion=TRUE;
					
					
					$condi= "id_eds_user = ".$_SESSION['id'];

					$ventas_col= $db->select('*', 'ventas_adt');
					$numRows = $db->num_rows($db->select('*', 'productos'));

					$p1=$db->getFieldName($ventas_col, 3);
					$p2=$db->getFieldName($ventas_col, 4);
					$p3=$db->getFieldName($ventas_col, 5);
					$p4=$db->getFieldName($ventas_col, 6);
					
					$col= "SUM(`".$p1."`) ,".
						"SUM(`".$p2."`) ,".
						"SUM(`".$p3."`) ,".
						"SUM(`".$p4."`) ";
						 
					$ventas= array();
					$ventas= $db->query($col,'ventas_adt', $condi);

					while($row=$db->fetch_array($ventas)){

						for ($i=0; $i < $numRows; $i++) {
							
							$c = $db->getFieldName($ventas_col, $i+3);
							$p = "SUM(`".$c."`)";
							$v = $row["$p"];
							$_SESSION[$c] = $v;


						}
					}				
				}


}
$db->disconnect();
/*
 * $numRows: número total de registros.
 * $info[$i]['id']: llave primaria del registro $i.
 * $info[$i]['date']: fecha del registro $i.
 * $info[$i]['categoria']: categoría.
 * $info[$i]['subcategoria']: subcategoría.
 * $info[$i]['title']: título del registro $i.
 * $info[$i]['summary']: resumen del registro $i.
 * $imgInfo[$i]['filename']: nombre de archivo de la primera imagen del registro $i.
 * $imgInfo[$i]['title']: título de la primera imagen del registro $i.
 * $imgInfo[$i]['description']: descripción de la primera imagen del registro $i.
 * $imgFile[$i][$j]: primera imagen del registro $i en los diferentes tamaños; $j es un valor entre 0 y la cantidad de tamaños configurada menos 1.
 */

$categorias = array(
	'tecnologia' => array(
		'etiqueta' => 'Tecnología',
		'subcategorias' => array(
			'celular' => 'Celulares',
			'tablet' => 'Tablets'
		)
	),
	'electrohogar' => array(
		'etiqueta' => 'Electrohogar',
		'subcategorias' => array(
			'televisor' => 'Televisores',
			'lavadora' => 'Lavadoras',
			'nevera' => 'Neveras',
			'minicomponente' => 'Minicomponentes',
			'ventilador' => 'Ventiladores',
			'arrocera' => 'Ollas arroceras',
			'licuadora' => 'Licuadoras',
			'maquina-coser' => 'Máquinas de coser',
			'plancha' => 'Planchas',
			'microondas' => 'Horno microondas',
			'tostador' => 'Horno tostador',
			'plancha-cabello' => 'Planchas para el cabello'
		)
	),
	'muebles' => array(
		'etiqueta' => 'Muebles',
		'subcategorias' => array(
			'comedor' => 'Comedores',
			'sala' => 'Salas',
			'sofa' => 'Sofás',
			'colchon' => 'Colchones',
			'cocina' => 'Cocinas',
			'mueble-cocina' => 'Muebles de cocina',
			'puerta' => 'Puertas',
			'closet' => 'Closet',
			'mueble' => 'Muebles',
			'mueble-entretenimiento' => 'Muebles de entretenimiento'
		)
	),
	'deportes' => array(
		'etiqueta' => 'Deportes y entretenimiento',
		'subcategorias' => array(
			'bicicleta' => 'Bicicletas',
			'balon' => 'Balones',
			'moto' => 'Motos',
			'casco' => 'Cascos para moto',
			'impermeable' => 'Impermeables para moto',
			'guante' => 'Guantes para moto'
		)
	),
	'hogar' => array(
		'etiqueta' => 'Hogar y decoración',
		'subcategorias' => array(
			'estufa' => 'Estufa a gas',
			'varios-cocina' => 'Varios cocina',
			'olla-presión' => 'Ollas a presión',
			'combo-bano' => 'Combos de baño',
			'sanitario' => 'Sanitarios',
			'lavamanos' => 'Lavamanos',
			'teja-fibrocemento' => 'Tejas de fibrocemento',
			'teja-plastica' => 'Tejas plásticas',
			'cemento' => 'Cemento gris',
			'bloque-ladrillo' => 'Bloques y ladrillos',
			'bloque-concreto' => 'Bloque de concreto',
			'drywall' => 'Drywall',
			'perfil' => 'Perfiles',
			'pintura' => 'Pinturas'
		)
	),
	'bebe' => array(
		'etiqueta' => 'Bebé',
		'subcategorias' => array(
			'articulo-bebe' => 'Artículos para bebé'
		)
	)
);

$pageTitle = CATALOGO_DESOS; ?> <?
include("header.php"); ?>
<style>.menu_button{ display: none;}</style>
<script type="text/javascript">
	$(document).ready(function(e) {
		$('.whiteBg').addClass('bgCatalogo');
		$('.prodPic').click(function(){
			$(location).attr('href', $(this).attr('data-url'));
		});

		$('.catMenuN1').click(function(event){
			event.stopPropagation();
			event.preventDefault();
			$(this).next('ul').slideToggle('fast');
		});

		$('.menu_buttonCat, .labelMenu').click(function(){
			if($(this).hasClass('active'))
			{
				$('.menu_buttonCat, .labelMenu').removeClass('active');
				$('.phoneCategories').slideUp(300);
			}
			else
			{
				$('.menu_buttonCat, .labelMenu').addClass('active');
				$('.phoneCategories').slideDown(300);
			}
		});
	});
</script>
<div id="titleArea" class="centerContent">
	<h1><?= $pageTitle; ?></h1>
</div>
<div class="whiteBg catDeseosPage">
	<section class="centerContent">
		<?php if ($sesion): ?>
			<div id="breadCrumbs">
							<div id="breadNav">Hola, <?= $_SESSION['nombre'] ?> . <a href="mi-cuenta-catalogo-deseos.php" class="cAcLink">Mi cuenta</a></div> 
							<a href="<?= $_SERVER['PHP_SELF'].'?login=out'?>" id="breadContact">Cerrar sesión</a>
						</div>

		<?php else: ?>
			<div id="breadCrumbs">
				<div id="breadNav">Es asesor de una EDS aditivada?</div>
				<a href="login-catalogo-deseos.php" id="breadContact">Iniciar sesión</a>
			</div>	
		<?php endif ?>
		<div class="shadow"></div>
		<div class="clear"></div>
			<div class="cdCategories">
				<ul><?
					foreach($categorias as $key => $value)
					{?>
						<li>
							<a href="<?= $_SERVER['PHP_SELF'].'?id='.$key ?>">
								<?= $value['etiqueta'] ?>
								<div class="subCatIcon"><i class="fa fa-caret-down" aria-hidden="true"></i></div>
							</a>
							<ul><?
								foreach($value['subcategorias'] as $key => $value)
								{?>
									<li><a href="<?= $_SERVER['PHP_SELF'].'?id='.$key ?>"><?= $value ?></a></li><?
								}?>
							</ul>
						</li><?
					}?>
				</ul>
			</div>
			<div class="cPhoneCatMenu" style="position:relative">
				<div class="labelMenu">Catálogo de premios</div>
				<div class="menu_buttonCat">
					<div class="line first"></div>
					<div class="line"></div>
					<div class="line last"></div>
				</div>
				<div class="phoneCategories">
					<ul>
						<li><a href="catalogo-de-deseos.php">Todos</a></li></li><?
						foreach($categorias as $key => $value)
						{?>
							<li>
								<a class="catMenuN1"><?= $value['etiqueta'] ?></a>
								<ul><?
									foreach($value['subcategorias'] as $key => $value)
									{?>
										<li><a href="<?= $_SERVER['PHP_SELF'].'?id='.$key ?>"><?= $value ?></a></li><?
									}?>
								</ul>
							</li><?
						}?>
					</ul>
				</div>
			</div>
			<div class="clear"></div>
			<div id="productList">
				<div class="clear20"></div>
				<div class="testiTitle">
					<i class="fa fa-star"></i>
					<h2><?= $pageTitle ?></h2>
				</div>
				<div class="clear20"></div>
				<p><?= TXT_CATALOGODESEOS ?></p>
				<div class="clear20"></div><?
					for($i=0; $i<$numRows; $i++)
					{ ?>
						<div class="prodItem">
							<div class="prodPic wishPic" data-url="<?= $module['archivo'].'-single.php?id='.$info[$i]['id']; ?>" style="background-image:url(<?= 'uploads/news/'.$info[$i]['id'].'/'.$imgFile[$i][1] ?>)"></div>
							<div class="prodName" style="line-height:18px; margin-top:8px"><a href="<?= $module['archivo'].'-single.php?id='.$info[$i]['id']; ?>"><?= $info[$i]['title']; ?></a></div>
						</div><?
					} ?>
					<div class="clear50"></div><?

			if($pag=pager($totalRows, $module['paginador'], $offset))  // Si es necesario paginar
			{
				$varDate = isset($_GET['date']) ? "date=$_GET[date]&amp;" : '';  // Viene del calendario o no.
				$prev = is_null($pag['prevOffset']) ? PAGER_PREVIOUS : "<a href=\"$_SERVER[PHP_SELF]?".$varDate."offset=$pag[prevOffset]\">".PAGER_PREVIOUS."</a>";
				$next = is_null($pag['nextOffset']) ? PAGER_NEXT : "<a href=\"$_SERVER[PHP_SELF]?".$varDate."offset=$pag[nextOffset]\">".PAGER_NEXT."</a>"; ?>
				<div class="pager">
					<div class="fLeft"><?= PAGER_FROM." $pag[from] ".PAGER_TO." $pag[to] ".PAGER_OF." $totalRows ".PAGER_RECORDS; ?></div>
					<div class="fRight">
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
					<div class="clear"></div>
				</div>
			</div><?
		}?>
	</section>
	<div class="clear50"></div>
</div>
<? include("footer.php"); ?>
