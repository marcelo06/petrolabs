<?php
include("lib/functions.php");
session_start();
if(isset($_GET['lang']))
	$_SESSION['lang'] = $_GET['lang'];
if(!isset($_SESSION['lang']))
	$_SESSION['lang'] = '1';
$db = database();
include("lang/".language($db).".php");
$db->disconnect();
$pageTitle = 'Iniciar sesión' ?> <?
include("header.php"); ?> 
<script type="text/javascript">
	$(document).ready(function(e) {
		$('.whiteBg').addClass('bgCatalogo');
	});
</script>
<?
	if (isset($_GET['p'])) {
		$destino = "catalogo-de-deseos-single.php?id=".$_GET['p'];
	}else{
		$destino = "catalogo-de-deseos.php";
	}

 ?>



<div id="titleArea" class="centerContent">
	<h1><?= $pageTitle; ?></h1>
</div>
<div class=" whiteBg catDeseosPage">
	<section class="centerContent">
		<div id="breadCrumbs">
			<div id="breadNav">¿Preguntas?</div> 
			<a href="contactenos.php" id="breadContact">Contáctenos</a>
		</div>		
		<div class="shadow"></div>
		<div class="clear"></div>
		<div id="productList">			
			<div class="clear20"></div>
			<div class="testiTitle">
				<i class="fa fa-star"></i>
				<h2><?= $pageTitle ?></h2>			
			</div>
			<div class="clear20"></div>
			<div class="iLogin">
				<form id="loginForm" name="loginForm" action="<?= $destino?>" enctype="multipart/form-data" method="post" >

					<input type="hidden" name="form" value="login">
					<label><span class="required req">*</span>Correo electrónico:</label>
					<input id="username" name="username" size="50" class="validate[required]" type="email">

					<label><span class="required req">*</span>Contraseña:</label>
					<input id="password" name="password" size="50" class="validate[required]" type="password">

					<a href="recordar-pass-catalogo.php">¿Olvidó la contraseña?</a>
					<div class="clear20"></div>
					<input value="Iniciar Sesión" type="submit">
				</form>
			</div>
			<div class="clear20"></div>
		</div>
	</section>
	<div class="clear50"></div>		
</div>
<script type="text/javascript" src="js/jquery.validationEngine-es.js"></script>
<script type="text/javascript" src="js/jquery.validationEngine.js"></script>
<script type="text/javascript">
	
	
	$(document).ready(function(){
		$("#loginForm").validationEngine();


	});

	
-->
</script>

<? include("footer.php"); ?>