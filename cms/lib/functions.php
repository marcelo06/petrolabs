<?php
/*
 ***************************
 * EasyWebsite versión 2.2 *
 ***************************
 */

ini_set('default_charset', 'UTF-8');

function config()
{
	session_start();
	include("settings.php");
	include("modules.php");
	$conf['settings'] = new settings();
	$conf['modules'] = new modules();
	return $conf;
}

function database()
{
	include_once("database.php");
	$db = new database();
	return $db;
}

function insert_file($db, $table, $pk, $updir, $name, $tmpName)
{
	$name = str_replace(' ', '-', $name);
	$pos = mb_strrpos($name, '.');
	$ext = mb_substr($name, $pos+1);
	$exts = array('pdf', 'docx', 'doc', 'xlsx', 'xls', 'jpg', 'jpeg', 'gif', 'png');
	if(!in_array(mb_strtolower($ext), $exts))  // No es un archivo válido.
		return FALSE;
	switch($table)
	{
		case 'noticia_adjunto':
			$idColumn = 'not_pk';
			$filenameColumn = 'noa_archivo';
			break;
	}
	if(!$db->insert($table, "$idColumn, $filenameColumn", "'$pk', '$name'"))  // Insertar registro en la BD para conservar el orden, sin importar el valor de $name.
		return FALSE;
	$adjPk = $db->last_insert_id();
	if(!empty($name) && (!is_uploaded_file($tmpName) || !copy($tmpName, $updir.utf8_decode($name))))
	{
		$db->update($table, "$filenameColumn=''", "noa_pk='$adjPk'");  // Borrar nombre del archivo porque no se pudo subir.
		return FALSE;
	}
	return $adjPk;
}

function insert_img($db, $table, $pk, $updir, $name, $tmpName, $dimensions)
{
	$name = str_replace(' ', '-', $name);
	switch($table)
	{
		case 'noticia_img':
			$idColumn = 'not_pk';
			$filenameColumn = 'noi_archivo';
			break;
		case 'seccion_img':
			$idColumn = 'sec_pk';
			$filenameColumn = 'sei_archivo';
			break;
	}
	if(!$db->insert($table, "$idColumn, $filenameColumn", "'$pk', '$name'"))  // Insertar registro en la BD para conservar el orden, sin importar el valor de $name.
		return FALSE;
	$imgPk = $db->last_insert_id();
	if(is_uploaded_file($tmpName))
	{
		$fileup = $updir.$name;
		$pos = mb_strrpos($fileup, '.');
		$ext = mb_substr($fileup, $pos+1);
		$exts = array('jpg', 'jpeg', 'gif', 'png');
		if(!in_array(mb_strtolower($ext), $exts))
		{
			$db->update($table, "$filenameColumn=''", "noi_pk='$imgPk'");  // Borrar nombre del archivo porque no era una imagen.
			return $imgPk;
		}
		include_once("resize-class.php");
		$resizeObj = new resize($tmpName, '.'.mb_strtolower($ext));
		$dims = explode(',', $dimensions);
		foreach($dims as $val)
		{
			$fname = utf8_decode(mb_substr($fileup, 0, $pos)).'--'.$val.".".$ext;
			$dim = explode('x', $val);
			$width = intval($dim[0]);
			$height = intval($dim[1]);
			if(!$width && !$height || !$width && $height>=$resizeObj->height || !$height && $width>=$resizeObj->width || $width>=$resizeObj->width && $height>=$resizeObj->height)
				copy($tmpName, $fname);
			else
			{
				$quality = isset($dim[2]) ? $dim[2] : '100';
				if($width && !$height)
					$option = 'landscape';
				elseif(!$width && $height)
					$option = 'portrait';
				else
					$option = 'crop';
				$resizeObj->resizeImage($width, $height, $option);
				$resizeObj->saveImage($fname, $quality);
			}
		}
	}
	return $imgPk;
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

function permit($db, $profile, $actions)
{
	$row = $db->fetch_array($db->select("prm_pk", "permiso", "per_pk='$profile' AND acc_pk IN ($actions)"));
	if($row)
		return TRUE;
	return FALSE;
}

// Elimina un directorio y todo su contenido
function remove_dir($dir)
{
	if(is_dir($dir))
	{
		$objects = scandir($dir);
		foreach($objects as $object)
		{
			if($object!="." && $object!="..")
			{
				if(filetype($dir."/".$object)=="dir")
					remove_dir($dir."/".$object);
				else
					unlink($dir."/".$object);
			}
		}
		reset($objects);
		rmdir($dir);
	}
}

function update_file($db, $table, $filePk, $deleteFile, $updir, $name, $tmpName)
{
	$name = str_replace(' ', '-', $name);
	switch($table)
	{
		case 'noticia_adjunto':
			$idColumn = 'noa_pk';
			$filenameColumn = 'noa_archivo';
			break;
	}
	$row = $db->fetch_array($db->select($filenameColumn, $table, "$idColumn='$filePk'"));
	if(is_uploaded_file($tmpName))  // Nuevo archivo seleccionado
	{
		$pos = mb_strrpos($name, '.');
		$ext = mb_substr($name, $pos+1);
		$exts = array('pdf', 'docx', 'doc', 'xlsx', 'xls', 'jpg', 'jpeg', 'gif', 'png');
		if(!in_array(mb_strtolower($ext), $exts))  // No es un archivo válido.
			return FALSE;
		if(!copy($tmpName, $updir.utf8_decode($name)))
			return FALSE;
		if(!$db->update($table, "$filenameColumn='$name'", "$idColumn='$filePk'"))
		{
			unlink($updir.utf8_decode($name));  // Eliminar archivo
			return FALSE;
		}
		if(!empty($row[$filenameColumn]))  // Había archivo antes
			unlink($updir.utf8_decode($row[$filenameColumn]));  // Eliminarlo
	}
	elseif($deleteFile=='1' && !empty($row[$filenameColumn]))  // No hay nuevo archivo seleccionado y se debe borrar al archivo actual
	{
		// Borrar archivo
		if(!$db->update($table, "$filenameColumn=''", "$idColumn='$filePk'"))
			return FALSE;
		unlink($updir.utf8_decode($row[$filenameColumn]));
	}
	return TRUE;
}

function update_img($db, $table, $imgPk, $deleteImg, $updir, $name, $tmpName, $dimensions)
{
	$name = str_replace(' ', '-', $name);
	switch($table)
	{
		case 'noticia_img':
			$idColumn = 'noi_pk';
			$filenameColumn = 'noi_archivo';
			break;
		case 'seccion_img':
			$idColumn = 'sei_pk';
			$filenameColumn = 'sei_archivo';
			break;
	}
	$row = $db->fetch_array($db->select($filenameColumn, $table, "$idColumn='$imgPk'"));
	if(is_uploaded_file($tmpName))  // Nueva imagen seleccionada
	{
		$fileup = $updir.$name;
		$pos = mb_strrpos($fileup, '.');
		$ext = mb_substr($fileup, $pos+1);
		$exts = array('jpg', 'jpeg', 'gif', 'png');
		if(!in_array(mb_strtolower($ext), $exts))  // No es una imagen válida
			return FALSE;
		if(!$db->update($table, "$filenameColumn='$name'", "$idColumn='$imgPk'"))
			return FALSE;
		include_once("resize-class.php");
		$resizeObj = new resize($tmpName, '.'.mb_strtolower($ext));
		$dims = explode(',', $dimensions);
		foreach($dims as $val)
		{
			$fname = utf8_decode(mb_substr($fileup, 0, $pos)).'--'.$val.".".$ext;
			$dim = explode('x', $val);
			$width = intval($dim[0]);
			$height = intval($dim[1]);
			if(!$width && !$height || !$width && $height>=$resizeObj->height || !$height && $width>=$resizeObj->width || $width>=$resizeObj->width && $height>=$resizeObj->height)
				copy($tmpName, $fname);
			else
			{
				$quality = isset($dim[2]) ? $dim[2] : '100';
				if($width && !$height)
					$option = 'landscape';
				elseif(!$width && $height)
					$option = 'portrait';
				else
					$option = 'crop';
				$resizeObj->resizeImage($width, $height, $option);
				$resizeObj->saveImage($fname, $quality);
			}
		}
		if(!empty($row[$filenameColumn]))  // Había imagen antes
		{
			// Eliminar imagen en todos los tamaños
			$row[$filenameColumn] = utf8_decode($row[$filenameColumn]);
			if($gestor=opendir($updir))
			{
				$pos = mb_strrpos($row[$filenameColumn], ".");
				$ini = mb_substr($row[$filenameColumn], 0, $pos);
				$ext = mb_substr($row[$filenameColumn], $pos+1);
				while(FALSE!==($arch=readdir($gestor)))
				{
					if($arch!="." && $arch!="..")
					{
						if(preg_match("/^(".$ini."--)([x0-9]+)\.(".$ext.")$/", $arch))
							unlink($updir.$arch);
					}
				}
				closedir($gestor);
			}
		}
	}
	elseif($deleteImg=='1' && !empty($row[$filenameColumn]))  // No hay nueva imagen seleccionada y se debe borrar la imagen actual
	{
		if(!$db->update($table, "$filenameColumn=''", "$idColumn='$imgPk'"))  // Borrar imagen
			return FALSE;

		// Eliminar imagen en todos los tamaños
		if($gestor=opendir($updir))
		{
			$row[$filenameColumn] = utf8_decode($row[$filenameColumn]);
			$pos = mb_strrpos($row[$filenameColumn], ".");
			$ini = mb_substr($row[$filenameColumn], 0, $pos);
			$ext = mb_substr($row[$filenameColumn], $pos+1);
			while(FALSE!==($arch=readdir($gestor)))
			{
				if($arch!="." && $arch!="..")
				{
					if(preg_match("/^(".$ini."--)([x0-9]+)\.(".$ext.")$/", $arch))
						unlink($updir.$arch);
				}
			}
			closedir($gestor);
		}
	}
	return TRUE;
}
?>