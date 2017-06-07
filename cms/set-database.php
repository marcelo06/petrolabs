<?
if($_SERVER['REQUEST_METHOD']=='POST')
{
	$msg = '';
	$changed = false;
	$file = file_get_contents("lib/database.php");
	if($_POST['host']!=$_POST['old_host'])
	{
		$file = str_replace("this->host = \"$_POST[old_host]\"", "this->host = \"$_POST[host]\"", $file);
		if(!$changed)
			$changed = true;
	}
	if($_POST['user']!=$_POST['old_user'])
	{
		$file = str_replace("this->user = \"$_POST[old_user]\"", "this->user = \"$_POST[user]\"", $file);
		if(!$changed)
			$changed = true;
	}
	if($_POST['password']!=$_POST['old_password'])
	{
		$file = str_replace("this->password = \"$_POST[old_password]\"", "this->password = \"$_POST[password]\"", $file);
		if(!$changed)
			$changed = true;
	}
	if($_POST['database']!=$_POST['old_database'])
	{
		$file = str_replace("this->database = \"$_POST[old_database]\"", "this->database = \"$_POST[database]\"", $file);
		if(!$changed)
			$changed = true;
	}
	if($changed)
	{
		file_put_contents("lib/database.php", $file);
		$msg = 'Actualización realizada.';
	}
	if(isset($_POST['create']))
	{
		$err = false;
		$link = mysql_connect($_POST['host'], $_POST['user'], $_POST['password']);
		mysql_query("SET NAMES 'utf8'", $link);
		$query = "CREATE DATABASE `$_POST[database]`
			CHARACTER SET utf8
			DEFAULT CHARACTER SET utf8
			COLLATE utf8_general_ci
			DEFAULT COLLATE utf8_general_ci";
		if($link && mysql_query("DROP DATABASE IF EXISTS `$_POST[database]`", $link) && mysql_query($query, $link) && mysql_select_db($_POST['database'], $link))
		{
			$f = file_get_contents("lib/create.sql");
			$sql = explode('-- SEPARADOR', $f);
			$limit = count($sql);
			for($i=0; $i<$limit && !$err; $i++)
			{
				if(!empty($sql[$i]))
				{
					if(!mysql_query($sql[$i], $link))
						$err = true;
				}
			}
		}
		else
			$err = true;
		if($msg!='')
			$msg .= '\n\n';
		if($err)
			$msg .= 'Ocurrió un fallo al crear la base de datos.';
		else
			$msg .= 'Base de datos creada satisfactoriamente.';
	}
}
include("lib/database.php");
$db = new database();
$pageTitle = "Configurar base de datos";
if($db->selected_db)
{
	$menuActive = 5; ?>
	<? include("header.php"); ?> <?
	if(!permit($db, $_SESSION['per_pk'], '1'))
	{
		echo "<script type=\"text/javascript\"> alert('Acceso denegado.'); window.history.back(); </script>";
		exit();
	}
}
else  // No se ha configurado la base de datos
{ ?>
	<!doctype html>
	<html>
		<head>
			<meta charset="utf-8">
			<title><?= $pageTitle; ?> - Easy website</title>
			<link href='http://fonts.googleapis.com/css?family=Cuprum' rel='stylesheet' type='text/css'>
			<link rel="stylesheet" type="text/css" href="css/validationEngine.jquery.css">
			<link rel="stylesheet" type="text/css" href="css/ui-theme/jquery-ui-1.10.2.custom.css">
			<link rel="stylesheet" type="text/css" href="css/style.css">
			<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
			<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.2/jquery-ui.min.js"></script>
		</head>
		<body>
			<div id="mainWrapper">
				<header id="pageHeader">
					<div id="logo"><a href="index.php"><img src="images/easy-website.png" alt=""></a></div>
					<div class="clear"></div>
				</header>
				<section id="mainContent"> <?
}
if($_SERVER['REQUEST_METHOD']=='POST')
{
	if($msg=='')
		echo "<script type=\"text/javascript\"> alert('No hubo cambios.'); </script>";
	else
		echo "<script type=\"text/javascript\"> alert('".$msg."'); </script>";
} ?>
<header>
	<div class="fLeft"><h1>Configurar base de datos</h1></div>
	<div class="fRight"><a href="lib/create.sql" target="_blank"><span class="icon">%</span> Descargar archivo de creaci&oacute;n de tablas</a></div>
	<div class="clear"></div>
</header>
<div class="contentPane">
	<form id="setDbForm" name="setDbForm" action="<?= $_SERVER['PHP_SELF']; ?>" method="post">
		<input type="hidden" name="old_host" value="<?= $db->host ?>" />
		<input type="hidden" name="old_user" value="<?= $db->user ?>" />
		<input type="hidden" name="old_password" value="<?= $db->password ?>" />
		<input type="hidden" name="old_database" value="<?= $db->database ?>" />
		<table class="formTable">
			<tr>
				<th>Host:</th>
				<td><input type="text" id="host" name="host" size="45" class="validate[required]" value="<?= $db->host ?>"></td>
			</tr>
			<tr>
				<th>User:</th>
				<td><input type="text" id="user" name="user" size="45" class="validate[required]" value="<?= $db->user ?>"></td>
			</tr>
			<tr>
				<th>Password:</th>
				<td><input type="password" id="password" name="password" size="45" class="validate[required]" value="<?= $db->password ?>"></td>
			</tr>
			<tr>
				<th>Database:</th>
				<td><input type="text" id="database" name="database" size="45" class="validate[required,custom[dbname]]" value="<?= $db->database ?>"></td>
			</tr>
			<tr>
				<th>&nbsp;</th>
				<td><label><input type="checkbox" id="create" name="create" value="1"> Crear base de datos (si ya existe una base de datos con el mismo nombre, será reemplazada).</label></td>
			</tr>
			<tr>
				<th>&nbsp;</th>
				<td>
					<input type="submit" value="Guardar">
				</td>
			</tr>
		</table>
	</form>
</div>
<script type="text/javascript" src="js/jquery.validationEngine-es.js"></script>
<script type="text/javascript" src="js/jquery.validationEngine.js"></script>
<script type="text/javascript">
	$(document).ready(function(){
		$("#setDbForm").validationEngine();
	});
</script>
<? include("footer.php"); ?>