<?php
include("lib/functions.php");
$db = database();
if(isset($_POST['not_fecha']))
{
	$result = $db->select('not_pk', 'noticia', "pk='$_POST[pk]' AND not_fecha='$_POST[not_fecha]'");
	if($db->num_rows($result)==1)
	{
		$row = $db->fetch_array($result);
		echo $row['not_pk'];
	}
}
else
{
	$arr = array();
	$result = $db->select('DISTINCT not_fecha', 'noticia', "pk='$_POST[pk]' ORDER BY not_fecha DESC");
	while($row = $db->fetch_array($result))
	{
		$date = explode('-', $row['not_fecha']);
		$arr[] = "$date[1]/$date[2]/$date[0]";
	}
	echo json_encode($arr);
}
$db->disconnect();
?>