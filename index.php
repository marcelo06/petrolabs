<?php
include("lib/functions.php");
session_start();
if(isset($_GET['lang']))
	$_SESSION['lang'] = $_GET['lang'];
if(!isset($_SESSION['lang']))
	$_SESSION['lang'] = '1';
$db = database();
if($row = $db->fetch_array($db->select('sec_archivo', 'seccion', "sec_index='1' LIMIT 1")))
{
	$db->disconnect();
	header("Location: $row[sec_archivo].php");
	exit();
}
$db->disconnect();
$pageTitle = ''; ?>
<? include("header.php"); ?>
<? include("footer.php"); ?>