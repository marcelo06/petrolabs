<?php
ini_set('default_charset', 'UTF-8');
function config()
{
	if(!isset($_SESSION['lang']))
		session_start();
	if(isset($_GET['lang']))
		$_SESSION['lang'] = $_GET['lang'];
	if(!isset($_SESSION['lang']))
		$_SESSION['lang'] = '1';
	include_once("cms/lib/settings.php");
	include_once("cms/lib/modules.php");
	$conf['settings'] = new settings();
	$conf['modules'] = new modules();
	return $conf;
}

function database()
{
	include_once("cms/lib/database.php");
	$db = new database();
	return $db;
}

function language($db)
{
	$row = $db->fetch_array($db->select('idi_locale', 'idioma', "idi_pk='$_SESSION[lang]'"));
	return mb_strtolower($row['idi_locale']);
}

function pager($numRows, $limit, $offset)
{
	if(!$numRows || $numRows<=$limit)
		return FALSE;
	$from = $offset+1;  // Número del primer registro de la página actual.
	$to = $offset+$limit<$numRows ? $offset+$limit : $numRows;  // Número del último registro de la página actual.
	$prevOffset = $offset>0 ? $offset-$limit : NULL;  // Valor de 'offset' a usar para ir a la página anterior.
	$value = 0;  // Determina el número del registro donde empieza cada página.
	$i = 0;  // Contador páginas.
	$pages = array();  // Almacena todas las páginas, cada una con 'offset' y 'selected'.
	while($value<$numRows)
	{
		$i++;
		$selected = $value==$offset ? TRUE : FALSE;
		$pages[$i] = array('offset'=>$value, 'selected'=>$selected);
		$value = $i * $limit;
	}
	$nextOffset = $offset<=$value-$limit*2 ? $offset+$limit : NULL;  // Valor de 'offset' a usar para ir a la siguiente página
	$result = array(
		'from' => $from,
		'to' => $to,
		'prevOffset' => $prevOffset,
		'pages' => $pages,
		'nextOffset' => $nextOffset,
	);
	return $result;
}

function date_to_spanish($date)
{
	$en = array("Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday",
							"January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December",
							"Jan", "Apr", "Aug", "Dec");
	$sp = array("Domingo", "Lunes", "Martes", "Mi&eacute;rcoles", "Jueves", "Viernes", "S&aacute;bado",
							"Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre",
							"Ene", "Abr", "Ago", "Dic");
	return str_replace($en, $sp, $date);
}

// Sacar el id del video de youtube
function youtube_id_from_url($url) {
	$pattern = 
		'%^# Match any youtube URL
		(?:https?://)?  # Optional scheme. Either http or https
		(?:www\.)?      # Optional www subdomain
		(?:             # Group host alternatives
		  youtu\.be/    # Either youtu.be,
		| youtube\.com  # or youtube.com
		  (?:           # Group path alternatives
			/embed/     # Either /embed/
		  | /v/         # or /v/
		  | /watch\?v=  # or /watch\?v=
		  )             # End path alternatives.
		)               # End host alternatives.
		([\w-]{10,12})  # Allow 10-12 for 11 char youtube id.
		$%x'
		;
	$result = preg_match($pattern, $url, $matches);
	if (false !== $result) {
		return $matches[1];
	}
	return false;
}	
?>