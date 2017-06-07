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
include("lib/class.phpmailer.php");
$pk = 4;
$not_pk = $_GET['id'];
$module = $db->fetch_array($db->select('*', 'modulo_noticias', "pk='$pk'"));
$dimensions = explode(',', str_replace(' ', '', $module['tamano_img']));
$info = $imgInfo = $imgFile = $file = array();  // Vectores para datos básicos del registro, imágenes y archivos adjuntos.
$condi = "noticia.not_pk='$not_pk' AND noticia.not_pk=noticia_txt.not_pk AND idi_pk='$_SESSION[lang]'";
$info = $db->fetch_array($db->select('not_fecha AS date, categoria, subcategoria, ntx_titulo AS title, ntx_resumen AS summary, ntx_contenido AS content, canje', 'noticia, noticia_txt', $condi));
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

/*
 * $info['date']: fecha.
 * $info['categoria']: categoría.
 * $info['subcategoria']: subcategoría.
 * $info['title']: título.
 * $info['canje']: canje equivalente para cuatro productos.
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
 
$categorias = array(
	'tecnologia' => 'Tecnología',
	'electrohogar' => 'Electrohogar',
	'muebles' => 'Muebles',
	'deportes' => 'Deportes y entretenimiento',
	'hogar' => 'Hogar y decoración',
	'bebe' => 'Bebé'
);
$subcategorias = array(
	'celular' => 'Celulares',
	'tablet' => 'Tablets',
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
	'plancha-cabello' => 'Planchas para el cabello',
	'comedor' => 'Comedores',
	'sala' => 'Salas',
	'sofa' => 'Sofás',
	'colchon' => 'Colchones',
	'cocina' => 'Cocinas',
	'mueble-cocina' => 'Muebles de cocina',
	'puerta' => 'Puertas',
	'closet' => 'Closet',
	'mueble' => 'Muebles',
	'mueble-entretenimiento' => 'Muebles de entretenimiento',
	'bicicleta' => 'Bicicletas',
	'balon' => 'Balones',
	'moto' => 'Motos',
	'casco' => 'Cascos para moto',
	'impermeable' => 'Impermeables para moto',
	'guante' => 'Guantes para moto',
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
	'pintura' => 'Pinturas',
	'articulo-bebe' => 'Artículos para bebé'
);

$categoria = empty($info['categoria']) ? '' : $categorias[$info['categoria']];
$subcategoria = empty($info['subcategoria']) ? '' : $subcategorias[$info['subcategoria']];



$pageTitle = ''; 

$canje_id = $info['canje'];
$ventas_col= $db->select('*', 'ventas_adt');

	$p1=$db->getFieldName($ventas_col, 3);
	$p2=$db->getFieldName($ventas_col, 4);
	$p3=$db->getFieldName($ventas_col, 5);
	$p4=$db->getFieldName($ventas_col, 6);

	$col= "SUM(`".$p1."`) ,".
		"SUM(`".$p2."`) ,".
		"SUM(`".$p3."`) ,".
		"SUM(`".$p4."`) ";

if ($canje_id>0) {
	$canjes = array();
	$condi= "id = ".$canje_id;

	
	$numRows = $db->num_rows($db->select('*', 'productos'));

	$canjes= $db->query('*','canje', $condi);
	$producto= $db->fetch_row($canjes);

	$val1 = $producto[1];
	$val2 = $producto[2];
	$val3 = $producto[3];
	$val4 = $producto[4];
	

}else{
	$val1=0;
	$val2=0;
	$val3=0;
	$val4=0;
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
if (!empty($_SESSION['nombre'])) {
	
	$condi= "id_eds_user = ".$_SESSION['id'];

	$ventas_col= $db->select('*', 'ventas_adt');
	$numRows = $db->num_rows($db->select('*', 'productos'));
	$sesion=TRUE;

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

if (isset($_POST['redimir']))
{

	if (empty($_SESSION['nombre'])) {
		echo "<script>alert('Primero debe iniciar sesion');</script>";
		echo "<script>document.location.href='login-catalogo-deseos.php?p=$not_pk';</script>";
	}


	$flag=0;

	if ($_SESSION[$p1]>=$val1) {
		
	}else{
		$flag++;
	}

	if ($_SESSION[$p2]>=$val2) {
		
	}else{
		$flag++;
	}
	if ($_SESSION[$p3]>=$val3) {
		
	}else{
		$flag++;
	}
	if ($_SESSION[$p4]>=$val4) {
		
	}else{
		$flag++;
	}

	if ($flag==0) {
		$_SESSION[$p1] =$_SESSION[$p1]-$val1;
		$_SESSION[$p2] =$_SESSION[$p2]-$val2;
		$_SESSION[$p3] =$_SESSION[$p3]-$val3;
		$_SESSION[$p4] =$_SESSION[$p4]-$val4;

		$id_eds_user = $_SESSION['id'];

		
		$columnas = "`id_eds_user`, ";
		$date = date("Y-m-d");	
		$columnas.= "`fecha`, ";

		$ventas= $db->select('*', 'ventas_adt');
		$numRows = $db->num_rows($db->select('*', 'productos'));
		$newVal1= $val1*(-1);
		$newVal2= $val2*(-1);
		$newVal3= $val3*(-1);
		$newVal4= $val4*(-1);
		$values = "'$id_eds_user', '$date', '$newVal1', '$newVal2', '$newVal3', '$newVal4'";


		$columnas.= "`$p1`,`$p2`,`$p3`,`$p4` ";

		if($db->insert('ventas_adt', $columnas, $values))
		{ //Insertar el descuento de los valores
			$lastid = $db->last_insert_id();
			$columnas = "`fecha`,`eds_user_id`,`noticia_txt_notpk`,`estado`, `ref_ventas_adt`";
			$values = "'$date','$id_eds_user','$not_pk', 'Por entregar', '$lastid'";
			if ($db->insert('redenciones', $columnas, $values))
			{
			
			$db->update('eds_users',"redencion_pendiente = '0'","id_eds_user=".$id_eds_user);
						
			$conf = config();
			$host= 'mail.'.$conf['settings']->domain;
			$origen = $conf['settings']->sendersEmail;        
			$password = $conf['settings']->pw;
			$destinatario = $_SESSION['email']; 
			$usuario = $_SESSION['nombre'];
							
			$mail = new PHPMailer();
			$mail->Mailer = 'smtp';
			$mail->Host = $host ;
			$mail->Port = 465;
			$mail->SMTPSecure = "ssl";
			$mail->SMTPAuth = true;
			$mail->From = $origen;
			$mail->FromName = 'Petrolabs';
			$mail->Username = $origen;
			$mail->Password = $password;
			$mail->WordWrap = 65;
			$mail->Timeout=30;
			$mail->Subject = 'Redencion de producto desde '.$_SERVER['HTTP_HOST'];
			 	$body = ' 
			 	<!DOCTYPE html>
			 	<html>
			 	<head>
			 		<meta charset="utf-8">
			 		<meta http-equiv="X-UA-Compatible" content="IE=edge">
			 		<title> Redenci&oacuten de producto</title>
			 		<style type="text/css" media="screen">

			 		section{margin: 0 auto; width: 500px;}
		 			img{display: block;float: right;}
		 			tr{text-align: left;}
		 			tbody{font-weight: lighter;}
			 			

			 		</style>
			 	</head>
			 	<body>
			 		<section>
			 			<div id="titulo">
			 				<h3>Redenci&oacute;n de producto</h3>
			 				<img src="http://www.aditivospetrolabs.com/images/Aditivos-Petrolabs.png" >

			 			</div>
			 			<div id="content">
			 				<h4>Hola, '.htmlentities($SESSION['nombre']).'</h4>
			 				<p>Usted a solicitado el siguiente producto.</p>

			 				<h4>'.$info['title'].'</h1>
			 				<div class="clear10"></div>
			 				<div class="editorText">
			 					<p><strong>'.htmlentities($categoria).' / '.htmlentities($subcategoria).'</strong></p>
			 					'.$info['content'].'
			 				</div>
			 				<br>
			 				<p>El producto queda por entregar.</p>
			 			</div>
			 		</section>
			 	</body>
			 	</html>
			 	';	
		 		
		 		$mail->AltBody = 'Para ver el mensaje, por favor use un visor de e-mail compatible con HTML.';
		 		$mail->AddAddress($destinatario);
		 		$mail->MsgHTML($body);

		 			 		
		 		$exito = $mail->Send();
		 		$intentos =1;

		 		while ((!$exito) && ($intentos < 5))
		 		{
		 			$exito = $mail->Send();
		 			$intentos ++;
		 		}
		 		
		 		if(!$exito)
		 		{
		 			echo "<script>alert('No se logró enviar la solicitud del producto al corrreo : ".$destinatario."');</script>";
		 			echo "<script>document.location.href='catalogo-de-deseos.php';</script>";
	 				
		 		}
		 		else
		 		{

		 			echo "<script>alert('Su solicitud será procesada. \\nSe ha enviado toda la informacion de la solicitud del producto al corrreo : ".$destinatario."');</script>";
		 			echo "<script>document.location.href='catalogo-de-deseos.php';</script>";
		 		}	
			}
			else
			{
			echo "<script>alert('No se logró registrar la redención.');</script>";
			echo "<script>document.location.href='catalogo-de-deseos.php';</script>";
			}

				
		}else
		{
		echo "<script>alert('No se logró descontar los puntos');</script>";
		echo "<script>document.location.href='catalogo-de-deseos.php';</script>";
		}


	 
	}else
	{
	 	?>
	 				<script>
	 					alert("No tiene los puntos necesarios para solicitar este producto. ");
	 					document.location.href="catalogo-de-deseos.php";				
	 				</script>		
	 				<?
	}
		
	
}


$db->disconnect();
?> <?
include("header.php"); ?>
<div id="titleArea" class="centerContent">
	<h1><?= $info['title']; ?></h1>
</div>
<div class=" whiteBg catDeseosPage">
	<section class="centerContent" id="productPage">	
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
		<div class="clear20"></div>
		<div id="fullProdPic">
			<div class="firstPhoto">
			
			</div>		
			<div class="pagePhotos"><?
				if($numImg>0)
				{
					for($i=0; $i<$numImg; $i++)
					{ ?>
						<a data-url="<?= 'uploads/news/'.$_GET['id'].'/'.$imgFile[$i][2] ?>" title="<?= $imgInfo[$i]['title']; ?>">
							<img src="<?= 'uploads/news/'.$_GET['id'].'/'.$imgFile[$i][1]; ?>" alt="<?= $imgInfo[$i]['title']; ?>">
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
				<p><strong><?= $categoria ?> / <?= $subcategoria ?></strong></p>
				<?= $info['content']; ?>
			</div>
			<table class="prod-ficha tab-list">
				<thead>
					<tr class="box-atrib">
						<th></th>
						<th>Unidades</th>
						<th>Venta diaria x 6 meses</th>
						<th>Venta diaria x 3 meses</th>
					</tr>
				</thead>
				<tbody>
					
				
					<tr class="box-atrib">
						<td class="atributo"><?= $p1 ?></td>
						<td><?= $val1 ?></td>
						<td class="atributo">

							<?php if ($val1==0): ?>
								0
							<?php else: ?>
								<?=  ceil(180/$val1) ?>
							<?php endif ?>
					
						</td>
						<td class="atributo">

							<?php if ($val1==0): ?>
								0
							<?php else: ?>
								<?=  ceil(90/$val1) ?>
							<?php endif ?>
					
						</td>
					</tr>
					<tr class="box-atrib">
						<td class="atributo"><?= $p2 ?></td>
						<td><?= $val2 ?></td>
						<td class="atributo">

							<?php if ($val2==0): ?>
								0
							<?php else: ?>
								<?=  ceil(180/$val2) ?>
							<?php endif ?>
					
						</td>
						<td class="atributo">

							<?php if ($val2==0): ?>
								0
							<?php else: ?>
								<?=  ceil(90/$val2) ?>
							<?php endif ?>
					
						</td>
					</tr>
					<tr class="box-atrib">
						<td class="atributo"><?= $p3 ?></td>
						<td><?= $val3 ?></td>
						<td class="atributo">

							<?php if ($val3==0): ?>
								0
							<?php else: ?>
								<?=  ceil(180/$val3) ?>
							<?php endif ?>
					
						</td>
						<td class="atributo">

							<?php if ($val3==0): ?>
								0
							<?php else: ?>
								<?=  ceil(90/$val3) ?>
							<?php endif ?>
					
						</td>
					</tr>
					<tr class="box-atrib">
						<td class="atributo"><?= $p4 ?></td>
						<td><?= $val4 ?></td>
						<td class="atributo">

							<?php if ($val4==0): ?>
								0
							<?php else: ?>
								<?=  ceil(180/$val4) ?>
							<?php endif ?>
					
						</td>
						<td class="atributo">

							<?php if ($val4==0): ?>
								0
							<?php else: ?>
								<?=  ceil(90/$val4) ?>
							<?php endif ?>
					
						</td>
					</tr>
				
					
				</tbody>
			</table>	

			<form id="newsForm" name="newsForm" action="<?= $_SERVER['PHP_SELF'].'?id='.$not_pk; ?>" enctype="multipart/form-data" method="post" onsubmit="return validar()">
				<input type="hidden" name="redimir" value="redimir">
				<div class="clear10"></div>
				<div align="center">
				<input type="submit" value="Redimir producto" class="rProdButton"></div>
			</form>			
			
		</div>
		<div class="clear20"></div>
		
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