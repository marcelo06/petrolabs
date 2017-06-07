<?
include("lib/database.php");
$db = new database();
$module = $db->fetch_array($db->select('nombre', 'modulo_noticias', "pk='$_GET[id]'"));
$db->disconnect();
$pageTitle = $module['nombre'];
$menuActive = 3; ?>
<? include("header.php"); ?> <?

$titleLabel = $_GET['id']=='2' ? 'Título' : 'Nombre';

$module = $db->fetch_array($db->select('*', 'modulo_noticias', "pk='$_GET[id]'"));
$editor = $module['editor']=='1' ? " class=\"editorTextArea\"" : '';
$editorResumen = $_GET['id']=='1' ? " class=\"editorTextArea\"" : '';
$thumbnail = '120x90';
$dimensions = $thumbnail.','.str_replace(' ', '', $module['tamano_img']);

// Array de idiomas
$result = $db->select('idi_pk, idi_locale, idi_txt', 'idioma', "idi_estado='1' ORDER BY idi_pk");
$numLanguages = $db->num_rows($result);
while($row = $db->fetch_array($result))
{
	$id = $row['idi_pk'];
	$languages[$id] = array('name'=>$row['idi_txt'], 'abbreviation'=>$row['idi_locale']);
	$descText[$id] = $numLanguages>1 ? "Descripción ($row[idi_locale])" : 'Descripción';
}

if($_SERVER['REQUEST_METHOD']=='POST')
{
	if($_POST['form']=='insert')
	{
		if(isset($_POST['fecha']))
		{
			$arr = explode('/', $_POST['fecha']);
			$date = "$arr[2]-$arr[1]-$arr[0]";
		}
		else
			$date = date('Y-m-d');  // Fecha actual
		$columns = 'pk, not_fecha';
		$values = "'$_GET[id]', '$date'";
		if(isset($_POST['categoria']) && !empty($_POST['categoria']))
		{
			$columns .= ', categoria';
			$values .= ", '$_POST[categoria]'";
		}
		if(isset($_POST['subcategoria']) && !empty($_POST['subcategoria']))
		{
			$columns .= ', subcategoria';
			$values .= ", '$_POST[subcategoria]'";
		}


		if($db->insert('noticia', $columns, $values))
		{
			$not_pk = $db->last_insert_id();
			chdir('../');
			$updir = getcwd().DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.'news'.DIRECTORY_SEPARATOR.$not_pk.DIRECTORY_SEPARATOR;
			mkdir($updir);
			chdir('cms/');
			$failure = FALSE;
			$columns = 'not_pk, idi_pk, ntx_titulo, ntx_contenido, ntx_seo_title, ntx_seo_keywords, ntx_seo_description';
			if(isset($_POST['resumen']))
				$columns .= ', ntx_resumen';
			if($_GET['id']=='1')
				$columns .= ', uso, demo, ficha, sulfuro, info_tecnica, beneficios, usos';
			if($_GET['id']=='4')
				$columns .= ', canje';

			$contador=0;
			while($titulo = each($_POST['titulo']))
			{	
				$contador++;
				$contenido = each($_POST['contenido']);
				$seoTitle = each($_POST['seoTitle']);
				$seoKeywords = each($_POST['seoKeywords']);
				$seoDescription = each($_POST['seoDescription']);
				$values = "'$not_pk', '$titulo[0]', '".addslashes($titulo[1])."', '".addslashes($contenido[1])."', '$seoTitle[1]', '$seoKeywords[1]', '$seoDescription[1]'";
				if(isset($_POST['resumen']))
				{
					$resumen = each($_POST['resumen']);
					$values .= ", '".addslashes($resumen[1])."'";
				}
				
				if($_GET['id']=='4' && $contador==1)
				{

					$val_canje = "'".$_POST['Super-4x1']."', '".$_POST['Op2-Dp2-Dp4']."', '".$_POST['Super_plus']."', '".$_POST['Dp16']."'";
					$col_canje ='`Super-4x1`,`Op2-Dp2-Dp4`,`Super_plus`,`Dp16`';

					
					if ($db->insert('canje',$col_canje, $val_canje)) {
						
						$canje = $db->last_insert_id();
						$values .= ", '".$canje."'";
					}
					else{
						echo "<script type=\"text/javascript\" language=\"javascript\"> alert('No fue posible ingresar el canje.') </script>";
						$canje =0;
						$values .= ", '".$canje."'";

					}
				}elseif($contador>1)
				{
					$values .= ", '".$canje."'";

				}				
				
				if($_GET['id']=='1')
				{
					$uso = each($_POST['uso']);
					$demo = each($_POST['demo']);
					$ficha = each($_POST['ficha']);
					$sulfuro = each($_POST['sulfuro']);
					$info_tecnica = each($_POST['info_tecnica']);
					$beneficios = each($_POST['beneficios']);
					$usos = each($_POST['usos']);
					$values .= ", '".addslashes($uso[1])."', '".trim($demo[1])."', '".addslashes($ficha[1])."', '".addslashes($sulfuro[1])."', '".addslashes($info_tecnica[1])."', '".addslashes($beneficios[1])."', '".addslashes($usos[1])."'";
				}
				if(!$db->insert('noticia_txt', $columns, $values) && !$failure)
					$failure = TRUE;

				
			}


			// Insertar imágenes
			for($i=1; $i<=$module['limite_img']; $i++)
			{
				$noi_pk = insert_img($db, 'noticia_img', $not_pk, $updir, $_FILES['imgArchivo_'.$i]['name'], $_FILES['imgArchivo_'.$i]['tmp_name'], $dimensions);
				if($noi_pk)
				{
					foreach($languages as $key=>$value)
					{
						$imgTitulo = in_array($_POST['imgTitulo_'.$i.'_'.$key], $descText) ? '' : $_POST['imgTitulo_'.$i.'_'.$key];
						if(!$db->insert('noi_txt', 'noi_pk, idi_pk, titulo', "'$noi_pk', '$key', '$imgTitulo'") && !$failure)
							$failure = TRUE;
					}
				}
				elseif(!$failure)
					$failure = TRUE;
			}

			// Insertar archivos adjuntos
			if($module['adjuntos']=='1')
			{
				for($i=1; $i<=$module['limite_adj']; $i++)
				{
					$noa_pk = insert_file($db, 'noticia_adjunto', $not_pk, $updir, $_FILES['adjArchivo_'.$i]['name'], $_FILES['adjArchivo_'.$i]['tmp_name']);
					if($noa_pk)
					{
						foreach($languages as $key=>$value)
						{
							$adjTitulo = in_array($_POST['adjTitulo_'.$i.'_'.$key], $descText) ? '' : $_POST['adjTitulo_'.$i.'_'.$key];
							if(!$db->insert('noa_txt', 'noa_pk, idi_pk, titulo', "'$noa_pk', '$key', '$adjTitulo'") && !$failure)
								$failure = TRUE;
						}
					}
					elseif(!$failure)
						$failure = TRUE;
				}
			}

			if($failure)
				echo "<script type=\"text/javascript\" language=\"javascript\"> alert('No fue posible ingresar toda la información.') </script>";
			echo "<script type=\"text/javascript\" language=\"javascript\"> alert('El registro ha sido creado.') </script>";
			echo "<script type=\"text/javascript\" language=\"javascript\"> window.location='".$_SERVER['PHP_SELF']."?id=".$_GET['id']."' </script>";
			exit();
		}
		else
			echo "<script type=\"text/javascript\" language=\"javascript\"> alert('No fue posible crear el registro.') </script>";
	}
	elseif($_POST['form']=='update')
	{
		$failure = FALSE;
		$set = '';
		if(isset($_POST['fecha']))
		{
			$date = explode('/', $_POST['fecha']);
			$set = "not_fecha='$date[2]-$date[1]-$date[0]'";
		}
		if(isset($_POST['categoria']))
		{
			$categoria = empty($_POST['categoria']) ? 'NULL' : "'$_POST[categoria]'";
			if($set != '')
				$set .= ', ';
			$set .= "categoria=$categoria";
		}
		if(isset($_POST['subcategoria']))
		{
			$subcategoria = empty($_POST['subcategoria']) ? 'NULL' : "'$_POST[subcategoria]'";
			if($set != '')
				$set .= ', ';
			$set .= "subcategoria=$subcategoria";
		}
		if(!$db->update('noticia', $set, "not_pk='$_GET[regId]'"))
			$failure = TRUE;
		$contador =1;
		while($titulo = each($_POST['titulo']))
		{
			$contenido = each($_POST['contenido']);
			$seoTitle = each($_POST['seoTitle']);
			$seoKeywords = each($_POST['seoKeywords']);
			$seoDescription = each($_POST['seoDescription']);
			if(isset($_POST['resumen']))
				$resumen = each($_POST['resumen']);

			if($_GET['id']=='4' && $contador==1)
			{
				$contador++;
				$condi = "ntx_titulo = '".$titulo[1]."'";
				$prod_canje =$db->fetch_assoc($db->select('canje','noticia_txt',$condi));

				$val_canje = "`Super-4x1`= '".$_POST['Super-4x1']."', `Op2-Dp2-Dp4` ='".$_POST['Op2-Dp2-Dp4']."',`Super_plus` ='".$_POST['Super_plus']."',`Dp16` = '".$_POST['Dp16']."'";
				
				$condi_canje = "id =".$prod_canje['canje'];
			
				if ($prod_canje['canje'] > 0) //el producto tiene un canje
				{
					if ($db->update('canje', $val_canje, $condi_canje))// actualizar el registro 
					{
						echo "<script type=\"text/javascript\" language=\"javascript\"> alert('se actualizo el canje.') </script>";
					}
					else
					{
						echo "<script type=\"text/javascript\" language=\"javascript\"> alert('Error al actualizar el canje.') </script>";
					}
				}else//El canje no esta registrado, hay que crear uno nuevo
				{
					$val_canje = "'".$_POST['Super-4x1']."', '".$_POST['Op2-Dp2-Dp4']."', '".$_POST['Super_plus']."', '".$_POST['Dp16']."'";
					$col_canje ='`Super-4x1`,`Op2-Dp2-Dp4`,`Super_plus`,`Dp16`';

					if ($db->insert('canje', $col_canje, $val_canje)) {
						$canje = $db->last_insert_id();
						echo "<script type=\"text/javascript\" language=\"javascript\"> alert('se creo el nuevo  canje ".$canje.".') </script>";
						$set = "canje=$canje";
						if(!$db->update('noticia_txt', $set, $condi)){
							$failure = TRUE;
						}
						
					}else
					{
						echo "<script type=\"text/javascript\" language=\"javascript\"> alert('Error al crear el canje.') </script>";
					}

				}
				
			}

			if($_GET['id']=='1')
			{
				$uso = each($_POST['uso']);
				$demo = each($_POST['demo']);
				$ficha = each($_POST['ficha']);
				$sulfuro = each($_POST['sulfuro']);
				$info_tecnica = each($_POST['info_tecnica']);
				$beneficios = each($_POST['beneficios']);
				$usos = each($_POST['usos']);
			}
			$condi = "not_pk='$_GET[regId]' AND idi_pk='$titulo[0]'";
			$result = $db->select('ntx_pk', 'noticia_txt', $condi);
			if($db->num_rows($result))  // Ya existe el registro en este idioma
			{
				// Actualizar el registro
				$set = "ntx_titulo='".addslashes($titulo[1])."', ntx_contenido='".addslashes($contenido[1])."', ntx_seo_title='$seoTitle[1]', ".
					"ntx_seo_keywords='$seoKeywords[1]', ntx_seo_description='$seoDescription[1]'";
				if(!$db->update('noticia_txt', $set, $condi) && !$failure)
					$failure = TRUE;
				if(isset($_POST['resumen']))
				{
					if(!$db->update('noticia_txt', "ntx_resumen='".addslashes($resumen[1])."'", $condi) && !$failure)
						$failure = TRUE;
				}
				if(isset($_POST['canje']))
				{
					if(!$db->update('noticia_txt', "canje='".addslashes($canje[1])."'", $condi) && !$failure)
						$failure = TRUE;
				}
				if($_GET['id']=='1')
				{
					if(!$db->update('noticia_txt', "uso='".addslashes($uso[1])."', demo='".trim($demo[1])."', ficha='".addslashes($ficha[1])."', sulfuro='".addslashes($sulfuro[1])."', beneficios='".addslashes($beneficios[1])."', usos='".addslashes($usos[1])."', info_tecnica='".addslashes($info_tecnica[1])."'", $condi) && !$failure)
						$failure = TRUE;
				}
			}
			else  // Aún no existe el registro en este idioma
			{
				$values = "'$_GET[regId]', '$titulo[0]', '".addslashes($titulo[1])."', '".addslashes($contenido[1])."'";
				if($db->insert('noticia_txt', 'not_pk, idi_pk, ntx_titulo, ntx_contenido', $values))  // Crear el registro
				{
					if(isset($_POST['resumen']))
					{
						if(!$db->update('noticia_txt', "ntx_resumen='".addslashes($resumen[1])."'", $condi) && !$failure)
							$failure = TRUE;
					}
					if(isset($_POST['canje']))
					{
						if(!$db->update('noticia_txt', "canje='".addslashes($canje[1])."'", $condi) && !$failure)
							$failure = TRUE;
					}
					if($_GET['id']=='1')
					{
						if(!$db->update('noticia_txt', "uso='".addslashes($uso[1])."', demo='".trim($demo[1])."'", $condi) && !$failure)
							$failure = TRUE;
					}
				}
				elseif(!$failure)
					$failure = TRUE;
			}
		}

		chdir('../');
		$updir = getcwd().DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.'news'.DIRECTORY_SEPARATOR.$_GET['regId'].DIRECTORY_SEPARATOR;
		chdir('cms/');

		// Actualizar imágenes
		for($i=1; $i<=$module['limite_img']; $i++)
		{
			$imgPk = $_POST['imgPk_'.$i];
			if(empty($imgPk))  // Nuevo registro de imagen
			{
				$noi_pk = insert_img($db, 'noticia_img', $_GET['regId'], $updir, $_FILES['imgArchivo_'.$i]['name'], $_FILES['imgArchivo_'.$i]['tmp_name'], $dimensions);
				if($noi_pk)
				{
					foreach($languages as $key=>$value)
					{
						$imgTitulo = in_array($_POST['imgTitulo_'.$i.'_'.$key], $descText) ? '' : $_POST['imgTitulo_'.$i.'_'.$key];
						if(!$db->insert('noi_txt', 'noi_pk, idi_pk, titulo', "'$noi_pk', '$key', '$imgTitulo'") && !$failure)
							$failure = TRUE;
					}
				}
				elseif(!$failure)
					$failure = TRUE;
			}
			else  // Registro de imagen ya existente
			{
				if(update_img($db, 'noticia_img', $imgPk, $_POST['deleteImg_'.$i], $updir, $_FILES['imgArchivo_'.$i]['name'], $_FILES['imgArchivo_'.$i]['tmp_name'], $dimensions))
				{
					foreach($languages as $key=>$value)
					{
						$imgTitulo = in_array($_POST['imgTitulo_'.$i.'_'.$key], $descText) ? '' : $_POST['imgTitulo_'.$i.'_'.$key];
						if($db->num_rows($db->select('titulo', 'noi_txt', "noi_pk='$imgPk' AND idi_pk='$key'"))>0)  // Ya existe el texto en este idioma
						{
							if(!$db->update('noi_txt', "titulo='$imgTitulo'", "noi_pk='$imgPk' AND idi_pk='$key'") && !$failure)
								$failure = TRUE;
						}
						else  // Aún no existe el texto en este idioma
						{
							if(!$db->insert('noi_txt', 'noi_pk, idi_pk, titulo', "'$imgPk', '$key', '$imgTitulo'") && !$failure)
								$failure = TRUE;
						}
					}
				}
				elseif(!$failure)
					$failure = TRUE;
			}
		}

		// Actualizar archivos adjuntos
		if($module['adjuntos']=='1')
		{
			for($i=1; $i<=$module['limite_adj']; $i++)
			{
				$adjPk = $_POST['adjPk_'.$i];
				if(empty($adjPk))  // Nuevo registro de archivo
				{
					$noa_pk = insert_file($db, 'noticia_adjunto', $_GET['regId'], $updir, $_FILES['adjArchivo_'.$i]['name'], $_FILES['adjArchivo_'.$i]['tmp_name']);
					if($noa_pk)
					{
						foreach($languages as $key=>$value)
						{
							$adjTitulo = in_array($_POST['adjTitulo_'.$i.'_'.$key], $descText) ? '' : $_POST['adjTitulo_'.$i.'_'.$key];
							if(!$db->insert('noa_txt', 'noa_pk, idi_pk, titulo', "'$noa_pk', '$key', '$adjTitulo'") && !$failure)
								$failure = TRUE;
						}
					}
					elseif(!$failure)
						$failure = TRUE;
				}
				else  // Registro de archivo ya existente
				{
					$deleteFile = isset($_POST['deleteFile_'.$i]) ? $_POST['deleteFile_'.$i] : '0';
					if(update_file($db, 'noticia_adjunto', $adjPk, $deleteFile, $updir, $_FILES['adjArchivo_'.$i]['name'], $_FILES['adjArchivo_'.$i]['tmp_name']))
					{
						foreach($languages as $key=>$value)
						{
							$adjTitulo = in_array($_POST['adjTitulo_'.$i.'_'.$key], $descText) ? '' : $_POST['adjTitulo_'.$i.'_'.$key];
							if($db->num_rows($db->select('titulo', 'noa_txt', "noa_pk='$adjPk' AND idi_pk='$key'"))>0)  // Ya existe el texto en este idioma
							{
								if(!$db->update('noa_txt', "titulo='$adjTitulo'", "noa_pk='$adjPk' AND idi_pk='$key'") && !$failure)
									$failure = TRUE;
							}
							else  // Aún no existe el texto en este idioma
							{
								if(!$db->insert('noa_txt', 'noa_pk, idi_pk, titulo', "'$adjPk', '$key', '$adjTitulo'") && !$failure)
									$failure = TRUE;
							}
						}
					}
					elseif(!$failure)
						$failure = TRUE;
				}
			}
		}

		if($failure)
			echo "<script type=\"text/javascript\" language=\"javascript\"> alert('No fue posible actualizar toda la información.') </script>";
		else
			echo "<script type=\"text/javascript\" language=\"javascript\"> alert('El registro ha sido actualizado.') </script>";
	}
}
else
{
	if(isset($_GET['delete']))
	{
		$condi = "not_pk='$_GET[delete]'";
		if($db->delete('noticia', $condi))  // Eliminar registro principal
		{
			$db->delete('noticia_txt', $condi);  // Eliminar registros de textos en idiomas

			// Eliminar registros de imágenes
			$result = $db->select('noi_pk', 'noticia_img', $condi);
			while($row=$db->fetch_array($result))
			{
				$db->delete('noi_txt', "noi_pk='$row[noi_pk]'");
			}
			$db->delete('noticia_img', $condi);

			// Eliminar registros de archivos adjuntos
			$result = $db->select('noa_pk', 'noticia_adjunto', $condi);
			while($row=$db->fetch_array($result))
			{
				$db->delete('noa_txt', "noa_pk='$row[noa_pk]'");
			}
			$db->delete('noticia_adjunto', $condi);

			// Borrar carpeta de imágenes y archivos
			chdir('../');
			remove_dir(getcwd().DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.'news'.DIRECTORY_SEPARATOR.$_GET['delete']);
			chdir('cms/');
			echo "<script type=\"text/javascript\" language=\"javascript\"> alert('El registro ha sido eliminado.') </script>";
		}
		else
			echo "<script type=\"text/javascript\" language=\"javascript\"> alert('No fue posible eliminar el registro.') </script>";
	}
	elseif(isset($_GET['duplicate']))
	{
		$condi = "not_pk='$_GET[duplicate]'";
		$row = $db->fetch_array($db->select('not_fecha', 'noticia', $condi));
		if($db->insert('noticia', 'pk, not_fecha', "'$_GET[id]', '$row[not_fecha]'"))  // Insertar registro principal
		{
			$not_pk = $db->last_insert_id();  // Id. registro principal
			$failure = FALSE;
			chdir('../');
			$dir = getcwd().DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.'news'.DIRECTORY_SEPARATOR;
			$destDir = $dir.$not_pk.DIRECTORY_SEPARATOR;
			mkdir($destDir);  // Crear directorio
			$sourceDir = $dir.$_GET['duplicate'].DIRECTORY_SEPARATOR;
			$objects = scandir($sourceDir);
			foreach($objects as $object)  // Copiar los archivos del directorio fuente en el recién creado
			{
				if($object!='.' && $object!='..' && !copy($sourceDir.'/'.$object, $destDir.'/'.$object) && !$failure)
					$failure = TRUE;
			}
			chdir('cms/');

			// Insertar textos en idiomas
			$columns = 'idi_pk, ntx_titulo, ntx_resumen, ntx_contenido, ntx_seo_title, ntx_seo_keywords, ntx_seo_description';
			$result = $db->select($columns, 'noticia_txt', "$condi ORDER BY idi_pk");
			while($row=$db->fetch_array($result))
			{
				$values = "'$not_pk', '$row[idi_pk]', '$row[ntx_titulo] - copia', '$row[ntx_resumen]', '$row[ntx_contenido]', '$row[ntx_seo_title]', '$row[ntx_seo_keywords]', '$row[ntx_seo_description]'";
				if(!$db->insert('noticia_txt', "not_pk, $columns", $values) && !$failure)
					$failure = TRUE;
			}

			// Insertar imágenes
			$result = $db->select('noi_pk, noi_archivo', 'noticia_img', "$condi ORDER BY noi_pk");
			while($row=$db->fetch_array($result))
			{
				if($db->insert('noticia_img', 'not_pk, noi_archivo', "'$not_pk', '$row[noi_archivo]'"))
				{
					$noi_pk = $db->last_insert_id();
					$result1 = $db->select('idi_pk, titulo, descripcion', 'noi_txt', "noi_pk='$row[noi_pk]' ORDER BY idi_pk");
					while($row1=$db->fetch_array($result1))
					{
						if(!$db->insert('noi_txt', 'noi_pk, idi_pk, titulo, descripcion', "'$noi_pk', '$row1[idi_pk]', '$row1[titulo]', '$row1[descripcion]'"))
							$failure = TRUE;
					}
				}
				elseif(!$failure)
					$failure = TRUE;
			}

			// Insertar archivos
			$result = $db->select('noa_pk, noa_archivo', 'noticia_adjunto', "$condi ORDER BY noa_pk");
			while($row=$db->fetch_array($result))
			{
				if($db->insert('noticia_adjunto', 'not_pk, noa_archivo', "'$not_pk', '$row[noa_archivo]'"))
				{
					$noa_pk = $db->last_insert_id();
					$result1 = $db->select('idi_pk, titulo, descripcion', 'noa_txt', "noa_pk='$row[noa_pk]]' ORDER BY idi_pk");
					while($row1=$db->fetch_array($result1))
					{
						if(!$db->insert('noa_txt', 'noa_pk, idi_pk, titulo, descripcion', "'$noa_pk', '$row1[idi_pk]', '$row1[titulo]', '$row1[descripcion]'"))
							$failure = TRUE;
					}
				}
				elseif(!$failure)
					$failure = TRUE;
			}

			if($failure)
				echo "<script type=\"text/javascript\" language=\"javascript\"> alert('No fue posible duplicar toda la información.') </script>";
			echo "<script type=\"text/javascript\" language=\"javascript\"> alert('El registro ha sido duplicado.') </script>";
		}
		else
			echo "<script type=\"text/javascript\" language=\"javascript\"> alert('No fue posible duplicar el registro.') </script>";
	}
}
if(isset($_GET['interfaz']))
{
	if($_GET['interfaz'] == 'insert' || $_GET['interfaz'] == 'edit')
	{
		$categorias = array(
			'tecnologia' => 'Tecnología',
			'electrohogar' => 'Electrohogar',
			'muebles' => 'Muebles',
			'deportes' => 'Deportes y entretenimiento',
			'hogar' => 'Hogar y decoración',
			'bebe' => 'Bebé'
		);
		$subcategorias = array(
			'celular' => array('tecnologia', 'Celulares'),
			'tablet' => array('tecnologia', 'Tablets'),
			'televisor' => array('electrohogar', 'Televisores'),
			'lavadora' => array('electrohogar', 'Lavadoras'),
			'nevera' => array('electrohogar', 'Neveras'),
			'minicomponente' => array('electrohogar', 'Minicomponentes'),
			'ventilador' => array('electrohogar', 'Ventiladores'),
			'arrocera' => array('electrohogar', 'Ollas arroceras'),
			'licuadora' => array('electrohogar', 'Licuadoras'),
			'maquina-coser' => array('electrohogar', 'Máquinas de coser'),
			'plancha' => array('electrohogar', 'Planchas'),
			'microondas' => array('electrohogar', 'Horno microondas'),
			'tostador' => array('electrohogar', 'Horno tostador'),
			'plancha-cabello' => array('electrohogar', 'Planchas para el cabello'),
			'comedor' => array('muebles', 'Comedores'),
			'sala' => array('muebles', 'Salas'),
			'sofa' => array('muebles', 'Sofás'),
			'colchon' => array('muebles', 'Colchones'),
			'cocina' => array('muebles', 'Cocinas'),
			'mueble-cocina' => array('muebles', 'Muebles de cocina'),
			'puerta' => array('muebles', 'Puertas'),
			'closet' => array('muebles', 'Closet'),
			'mueble' => array('muebles', 'Muebles'),
			'mueble-entretenimiento' => array('muebles', 'Muebles de entretenimiento'),
			'bicicleta' => array('deportes', 'Bicicletas'),
			'balon' => array('deportes', 'Balones'),
			'moto' => array('deportes', 'Motos'),
			'casco' => array('deportes', 'Cascos para moto'),
			'impermeable' => array('deportes', 'Impermeables para moto'),
			'guante' => array('deportes', 'Guantes para moto'),
			'estufa' => array('hogar', 'Estufa a gas'),
			'varios-cocina' => array('hogar', 'Varios cocina'),
			'olla-presión' => array('hogar', 'Ollas a presión'),
			'combo-bano' => array('hogar', 'Combos de baño'),
			'sanitario' => array('hogar', 'Sanitarios'),
			'lavamanos' => array('hogar', 'Lavamanos'),
			'teja-fibrocemento' => array('hogar', 'Tejas de fibrocemento'),
			'teja-plastica' => array('hogar', 'Tejas plásticas'),
			'cemento' => array('hogar', 'Cemento gris'),
			'bloque-ladrillo' => array('hogar', 'Bloques y ladrillos'),
			'bloque-concreto' => array('hogar', 'Bloque de concreto'),
			'drywall' => array('hogar', 'Drywall'),
			'perfil' => array('hogar', 'Perfiles'),
			'pintura' => array('hogar', 'Pinturas'),
			'articulo-bebe' => array('bebe', 'Artículos para bebé')
		);
	}
}
else
{
	$_GET['interfaz'] = '';
}
if($_GET['interfaz']=='insert')
{ ?>
	<header>
	<div class="fLeft">
		<h1>Crear <?= $module['nombre']; ?></h1>
	</div>
	<div class="fRight"><a href="<?= $_SERVER['PHP_SELF'].'?id='.$_GET['id']; ?>"><span class="icon">]</span> Regresar al listado</a></div>
	<div class="clear"></div>
	</header>
	<div class="contentPane">
		<form id="newsForm" name="newsForm" action="<?= $_SERVER['PHP_SELF'].'?id='.$_GET['id'].'&interfaz=insert'; ?>" enctype="multipart/form-data" method="post">
			<input type="hidden" name="form" value="insert"> <?
			// Asegurar la inserción del registro para el idioma 1 (en la tabla noticia_txt de la BD) en caso
			// de que este idioma esté inactivo, pues se necesita para listar correctamente los registros.
			if(!array_key_exists(1, $languages))
			{ ?>
				<input type="hidden" name="titulo[1]" value="">
				<input type="hidden" name="contenido[1]" value="">
				<input type="hidden" name="seoTitle[1]" value="">
				<input type="hidden" name="seoKeywords[1]" value="">
				<input type="hidden" name="seoDescription[1]" value="">
				<input type="hidden" name="canje" value="">  <?
				if($module['resumen']=='1')
				{ ?>
					<input type="hidden" name="resumen[1]" value=""> <?
				}
			} ?>
			<table class="formTable w875"> <?
				if($module['fecha']=='1')
				{ ?>
					<tr><? /* Si tiene activa la fecha mostrar esta fila */ ?>
						<th><span class="required">*</span> Fecha:</th>
						<td><input type="text" id="fecha" name="fecha" size="10" class="dateSelector validate[required]"></td>
					</tr> <?
				}
				foreach($languages as $key=>$value)
				{ ?>
					<tr>
						<th><span class="required">*</span> <? echo $titleLabel; if($numLanguages>1) echo " ($value[abbreviation])"; ?>:</th>
						<td><input type="text" id="titulo[<?= $key; ?>]" name="titulo[<?= $key; ?>]" size="100" maxlength="255" class="validate[required]"></td>
					</tr> <?
				}
				if($_GET['id']=='4')  // Módulo "Catálogo de deseos".
				{ ?>
					<tr>
						<th><span class="required">*</span> Categoría:</th>
						<td>
							<select id="categoria" name="categoria" class="validate[required]">
								<option value=""></option><?
								foreach($categorias as $key => $value)
								{?>
									<option value="<?= $key ?>"><?= $value ?></option><?
								}?>
							</select>
						</td>
					</tr>
					<tr>
						<th>Subcategoría:</th>
						<td>
							<select id="subcategoria" name="subcategoria">
								<option value=""></option><?
								foreach($subcategorias as $key => $value)
								{?>
									<option class="subc <?= $value[0] ?>" value="<?= $key ?>"><?= $value[1] ?></option><?
								}?>
							</select>
						</td>
					</tr> <?
				} ?>
			</table>
			<div id="accordion"> <?
				if($_GET['id']=='1')
				{ ?>
					<h3>Uso en</h3>
					<div>
						<table class="formTable"> <?
							foreach($languages as $key=>$value)
							{ ?>
								<tr>
									<th>Uso en<? if($numLanguages>1) echo " ($value[abbreviation])"; ?>:</th>
									<td><input type="text" id="uso[<?= $key; ?>]" name="uso[<?= $key; ?>]" size="100" maxlength="100"></td>
								</tr> <?
							} ?>
						</table>
					</div>

					<h3>Demostración (video de youtube Ej. http://www.youtube.com/watch?v=zzK-n0CGJvo)</h3>
					<div>
						<table class="formTable"> <?
							foreach($languages as $key=>$value)
							{ ?>
								<tr>
									<th>Demostración<? if($numLanguages>1) echo " ($value[abbreviation])"; ?>:</th>
									<td><input type="text" id="demo[<?= $key; ?>]" name="demo[<?= $key; ?>]" size="45" maxlength="100" class="validate[custom[youtubeUrl]] demoField" placeholder="http://www.youtube.com/watch?v=zzK-n0CGJvo"></td>
								</tr> <?
							} ?>
						</table>
					</div> <?
				}

				if($_GET['id']=='4')  // Módulo "Catálogo de deseos".
				{ 
					$ventas_col= $db->select('*', 'ventas_adt');
					$numRows = $db->num_rows($db->select('*', 'productos'));

					$rowT = $db->fetch_array($db->select('ntx_titulo', 'noticia_txt', "not_pk='$row[not_pk]' AND idi_pk IN (".implode(', ', array_keys($languages)).") AND ntx_titulo!='' ORDER BY idi_pk ASC LIMIT 1"));
					$row['ntx_titulo'] = $rowT['ntx_titulo'];

				?>
					<h3>Canje equivalente para cuatro productos</h3>
					<div>
						<table width="100% "cellspacing="0" cellpadding="0" class="dataTable">
					<tr>
						<?php for ($i=0; $i < $numRows; $i++) :?>
							<th width="100"><?=$col = $db->getFieldName($ventas_col, $i+3);	?>
								
							</th>
						<?php endfor ?>	
					</tr>
					<!-- Fila para registrar la venta del dia -->
					<tr>
						
											
						<?php for ($i=0; $i < $numRows; $i++) :?>
							<? $col = $db->getFieldName($ventas_col, $i+3); $j=$i+1;?>
							<td><input id="prod<?=$j?>" class="validate[required]" type="number" name="<?= "$col" ?>" value="0" style="width: 80px"></td>
						<?php endfor ?>	

					</tr>
					
				</table>
					</div> <?
				}

				if($module['resumen']=='1')
				{
					/* Si tiene activo el resumen mostrar los textarea para esto */ ?>
					<h3>Resumen</h3>
					<div>
						<table class="formTable w875 noMargin paddingL10px">
							<tr><td> <?
								$i = 1;
								foreach($languages as $key=>$value)
								{
									if($numLanguages>1)
									{ ?>
										<h3><?= $value['name']; ?>:</h3> <?
									} ?>
									<textarea name="resumen[<?= $key; ?>]"<?= $editorResumen; ?> cols="110" rows="5"></textarea> <?
									if($i<$numLanguages)
									{ ?>
										<div class="clear50"></div> <?
									}
									$i++;
								} ?>
							</td></tr>
						</table>
					</div> <?
				} ?>

				<h3>Contenido</h3>
				<div>
					<table class="formTable w875 noMargin paddingL10px">
						<tr><td> <?
							$i = 1;
							foreach($languages as $key=>$value)
							{
								if($numLanguages>1)
								{ ?>
									<h3><?= $value['name']; ?>:</h3> <?
								} ?>
								<textarea id="contenido[<?= $key; ?>]" name="contenido[<?= $key; ?>]"<?= $editor; ?> cols="110" rows="5"></textarea> <?
								if($i<$numLanguages)
								{ ?>
									<div class="clear50"></div> <?
								}
								$i++;
							} ?>
						</td></tr>
					</table>
				</div> <?

				if($_GET['id']=='1')
				{ ?>
					<h3>Ficha técnica</h3>
					<div>
						<table class="formTable w875 noMargin paddingL10px">
							<tr><td> <?
								$i = 1;
								foreach($languages as $key=>$value)
								{
									if($numLanguages>1)
									{ ?>
										<h3><?= $value['name']; ?>:</h3> <?
									} ?>
									<textarea id="ficha[<?= $key; ?>]" name="ficha[<?= $key; ?>]"<?= $editor; ?> cols="110" rows="5"></textarea> <?
									if($i<$numLanguages)
									{ ?>
										<div class="clear50"></div> <?
									}
									$i++;
								} ?>
							</td></tr>
						</table>
					</div>
					<h3>Sulfuro</h3>
					<div>
						<table class="formTable w875 noMargin paddingL10px">
							<tr><td> <?
								$i = 1;
								foreach($languages as $key=>$value)
								{
									if($numLanguages>1)
									{ ?>
										<h3><?= $value['name']; ?>:</h3> <?
									} ?>
									<textarea id="sulfuro[<?= $key; ?>]" name="sulfuro[<?= $key; ?>]"<?= $editor; ?> cols="110" rows="5"></textarea> <?
									if($i<$numLanguages)
									{ ?>
										<div class="clear50"></div> <?
									}
									$i++;
								} ?>
							</td></tr>
						</table>
					</div>
					<h3>Información técnica</h3>
					<div>
						<table class="formTable w875 noMargin paddingL10px">
							<tr><td> <?
								$i = 1;
								foreach($languages as $key=>$value)
								{
									if($numLanguages>1)
									{ ?>
										<h3><?= $value['name']; ?>:</h3> <?
									} ?>
									<textarea id="info_tecnica[<?= $key; ?>]" name="info_tecnica[<?= $key; ?>]"<?= $editor; ?> cols="110" rows="5"></textarea> <?
									if($i<$numLanguages)
									{ ?>
										<div class="clear50"></div> <?
									}
									$i++;
								} ?>
							</td></tr>
						</table>
					</div>
					<h3>Beneficios</h3>
					<div>
						<table class="formTable w875 noMargin paddingL10px">
							<tr><td> <?
								$i = 1;
								foreach($languages as $key=>$value)
								{
									if($numLanguages>1)
									{ ?>
										<h3><?= $value['name']; ?>:</h3> <?
									} ?>
									<textarea id="beneficios[<?= $key; ?>]" name="beneficios[<?= $key; ?>]"<?= $editor; ?> cols="110" rows="5"></textarea> <?
									if($i<$numLanguages)
									{ ?>
										<div class="clear50"></div> <?
									}
									$i++;
								} ?>
							</td></tr>
						</table>
					</div>
					<h3>Usos</h3>
					<div>
						<table class="formTable w875 noMargin paddingL10px">
							<tr><td> <?
								$i = 1;
								foreach($languages as $key=>$value)
								{
									if($numLanguages>1)
									{ ?>
										<h3><?= $value['name']; ?>:</h3> <?
									} ?>
									<textarea id="usos[<?= $key; ?>]" name="usos[<?= $key; ?>]"<?= $editor; ?> cols="110" rows="5"></textarea> <?
									if($i<$numLanguages)
									{ ?>
										<div class="clear50"></div> <?
									}
									$i++;
								} ?>
							</td></tr>
						</table>
					</div> <?
				}

				if($module['limite_img']>0) /* Si el límite de imágenes es mayor a cero */
				{ ?>
					<h3>Imágenes</h3>
					<div class="listPics"><?
						for($i=1; $i<=$module['limite_img']; $i++)
						{ ?>
							<div class="previewPicture">
								<div class="imageContainer">
									<img src="" alt="" width="120" class="noDisplay">
									<div class="noPic"></div>
									<a class="redLink noDisplay"><span class="icon">X</span>Borrar</a>
								</div>
								<div class="fLeft"> <?
									foreach($languages as $key=>$value)
									{ ?>
										<input type="text" name="<?= 'imgTitulo_'.$i.'_'.$key; ?>" value="<?= $descText[$key]; ?>" size="40" class="<?= 'desc'.ucfirst(mb_strtolower($value['abbreviation'])); ?>">
										<div class="clear5"></div> <?
									} ?>
									<input type="file" name="<?= 'imgArchivo_'.$i; ?>" value="">
								</div>
							</div>
							 <div class="file-preview"></div>
							<?
						} ?>
					</div> <?
				}

				if($module['adjuntos']=='1' && $module['limite_adj']>0) /* Si se permiten archivos adjuntos */
				{ ?>
					<h3>Archivos adjuntos</h3>
					<div> <?
						for($i=1; $i<=$module['limite_adj']; $i++)
						{ ?>
							<div class="filePreview">
								<div class="clear"></div> <?
								foreach($languages as $key=>$value)
								{ ?>
									<input type="text" name="<?= 'adjTitulo_'.$i.'_'.$key; ?>" value="<?= $descText[$key]; ?>" size="40" class="<?= 'desc'.ucfirst(mb_strtolower($value['abbreviation'])); ?>">
									<div class="clear5"></div> <?
								} ?>
								<input type="file" name="<?= 'adjArchivo_'.$i; ?>" value="">
							</div><?
						} ?>
					</div> <?
				} ?>

				<? /* SEO */ ?>
				<h3>SEO</h3>
				<div>
					<table class="formTable w875"> <?
						foreach($languages as $key=>$value)
						{ ?>
							<tr>
								<th><? echo $titleLabel; if($numLanguages>1) echo " ($value[abbreviation])"; ?>:</th>
								<td><input type="text" name="seoTitle[<?= $key; ?>]" size="45" maxlength="255"></td>
							</tr> <?
						}
						foreach($languages as $key=>$value)
						{ ?>
							<tr>
								<th>Keywords<? if($numLanguages>1) echo " ($value[abbreviation])"; ?>:</th>
								<td><input type="text" name="seoKeywords[<?= $key; ?>]" size="115" maxlength="255"></td>
							</tr> <?
						}
						foreach($languages as $key=>$value)
						{ ?>
							<tr>
								<th>Description<? if($numLanguages>1) echo " ($value[abbreviation])"; ?>:</th>
								<td>
									<textarea name="seoDescription[<?= $key; ?>]" cols="60" rows="2" class="contador"></textarea>
									<div id="longitud"></div>
								</td>
							</tr> <?
						} ?>
					</table>
				</div>
			</div>
			<div class="clear10"></div>
			<div align="center"><input type="submit" value="Guardar"></div>
		</form>
	</div> <?
}
else
{
	if($_GET['interfaz']=='edit')
	{
		foreach($languages as $key=>$value)
		{
			$columns = 'ntx_titulo, ntx_resumen, ntx_contenido, ntx_seo_title, ntx_seo_keywords, ntx_seo_description, uso, demo, ficha, sulfuro, info_tecnica, beneficios, usos, canje';
			$row[$key] = $db->fetch_array($db->select($columns, 'noticia_txt', "not_pk='$_GET[regId]' AND idi_pk='$key'"));
		}
		$rowNew = $db->fetch_array($db->select('not_fecha, categoria, subcategoria', 'noticia', "not_pk='$_GET[regId]'")); ?>
		<header>
			<div class="fLeft">
				<h1>Editar <?= $module['nombre']; ?></h1>
			</div>
			<div class="fRight"><a href="<?= $_SERVER['PHP_SELF'].'?id='.$_GET['id']; ?>"><span class="icon">]</span> Regresar al listado</a></div>
			<div class="clear"></div>
		</header>
		<div class="contentPane">
			<form id="newsForm" name="newsForm" action="<?= $_SERVER['PHP_SELF'].'?id='.$_GET['id'].'&regId='.$_GET['regId'].'&interfaz=edit'; ?>" enctype="multipart/form-data" method="post" onsubmit="return enviar()">
				<input type="hidden" name="form" value="update">
				<table class="formTable w875"> <?
					if($module['fecha']=='1')
					{
						$date = explode('-', $rowNew['not_fecha']); ?>
						<tr><? /* Si tiene activa la fecha mostrar esta fila */ ?>
							<th><span class="required">*</span> Fecha:</th>
							<td><input type="text" id="fecha" name="fecha" size="10" class="dateSelector validate[required]" value="<?= "$date[2]/$date[1]/$date[0]"; ?>"></td>
						</tr> <?
					}
					foreach($languages as $key=>$value)
					{ ?>
						<tr>
							<th><span class="required">*</span> <? echo $titleLabel; if($numLanguages>1) echo " ($value[abbreviation])"; ?>:</th>
							<td><input type="text" id="titulo[<?= $key; ?>]" name="titulo[<?= $key; ?>]" size="100" maxlength="255" class="validate[required]" value="<?= htmlentities($row[$key]['ntx_titulo'], ENT_QUOTES, 'UTF-8'); ?>"></td>
						</tr> <?
					}
					if($_GET['id']=='4')  // Módulo "Catálogo de deseos".
					{ ?>
						<tr>
							<th><span class="required">*</span> Categoría:</th>
							<td>
								<select id="categoria" name="categoria" class="validate[required]">
									<option value=""></option><?
									foreach($categorias as $key => $value)
									{?>
										<option value="<?= $key ?>"<? if($rowNew['categoria']==$key) echo ' selected'; ?>><?= $value ?></option><?
									}?>
								</select>
							</td>
						</tr>
						<tr>
							<th>Subcategoría:</th>
							<td>
								<select id="subcategoria" name="subcategoria">
									<option value=""></option><?
									foreach($subcategorias as $key => $value)
									{?>
										<option class="subc <?= $value[0] ?>" value="<?= $key ?>"<? if($rowNew['subcategoria']==$key) echo ' selected'; ?>><?= $value[1] ?></option><?
									}?>
								</select>
							</td>
						</tr> <?
					} ?>
				</table>
				<div id="accordion"> <?
					if($_GET['id']=='1')
					{ ?>
						<h3>Uso en</h3>
						<div>
							<table class="formTable"> <?
								foreach($languages as $key=>$value)
								{ ?>
									<tr>
										<th>Uso en<? if($numLanguages>1) echo " ($value[abbreviation])"; ?>:</th>
										<td><input type="text" id="uso[<?= $key; ?>]" name="uso[<?= $key; ?>]" size="100" maxlength="100" value="<?= htmlentities($row[$key]['uso'], ENT_QUOTES, 'UTF-8'); ?>"></td>
									</tr> <?
								} ?>
							</table>
						</div>

						<h3>Demostración (video de youtube Ej. http://www.youtube.com/watch?v=zzK-n0CGJvo)</h3>
						<div>
							<table class="formTable"> <?
								foreach($languages as $key=>$value)
								{ ?>
									<tr>
										<th>Demostración<? if($numLanguages>1) echo " ($value[abbreviation])"; ?>:</th>
										<td><input type="text" id="demo[<?= $key; ?>]" name="demo[<?= $key; ?>]" size="100" maxlength="100" class="validate[custom[youtubeUrl]] demoField" value="<?= htmlentities($row[$key]['demo'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="http://www.youtube.com/watch?v=zzK-n0CGJvo"></td>
									</tr> <?
								} ?>
							</table>
						</div> <?
					}

					if($_GET['id']=='4')  // Módulo "Catálogo de deseos".
					{ 
						$canjes_col= $db->select('*', 'canje');
						$numRows = $db->num_rows($db->select('*', 'productos'));

						$canje_id= $row[1]['canje'];
						$condi = 'id ='.$canje_id;

						
						if($canje_values= $db->fetch_assoc($db->select('*','canje',$condi)))
						{?> 
							<h3>Canje equivalente para cuatro productos</h3>
							<div>
								<table width="100% "cellspacing="0" cellpadding="0" class="dataTable">
									<tr>
										
									<?php for ($i=0; $i < $numRows; $i++) :?>
										<th width="100"><?=$col = $db->getFieldName($canjes_col, $i+1);	?>	</th>
									<?php endfor ?>	
									</tr>
							<!-- Fila para registrar la venta del dia -->
									<tr>
									
													
									<?php for ($i=0; $i < $numRows; $i++) :?>
										<? $col = $db->getFieldName($canjes_col, $i+1); $j=$i+1;?>
										<td><input id="prod<?=$j?>" class="validate[required]" type="number" name="<?= "$col" ?>" value="<?= "$canje_values[$col]" ?>" style="width: 80px"></td>
									<?php endfor ?>	

									</tr>
							
								</table>
							</div> 
							
						<?
						}else{
						?>
							<h3>Canje equivalente para cuatro productos</h3>
							<div>
								<table width="100% "cellspacing="0" cellpadding="0" class="dataTable">
									<tr>
										
									<?php for ($i=0; $i < $numRows; $i++) :?>
										<th width="100"><?=$col = $db->getFieldName($canjes_col, $i+1);	?>	</th>
									<?php endfor ?>	
									</tr>
							<!-- Fila para registrar la venta del dia -->
									<tr>
									
													
									<?php for ($i=0; $i < $numRows; $i++) :?>
										<? $col = $db->getFieldName($canjes_col, $i+1); $j=$i+1;?>
										<td><input id="prod<?=$j?>" class="validate[required]" type="number" name="<?= "$col" ?>" value="0" style="width: 80px"></td>
									<?php endfor ?>	

									</tr>
							
								</table>
							</div> 
						<?	
						}


				
					}

					if($module['resumen']=='1')
					{
						/* Si tiene activo el resumen mostrar los textarea para esto */ ?>
						<h3>Resumen</h3>
						<div>
							<table class="formTable w875 noMargin paddingL10px">
								<tr><td> <?
									$i = 1;
									foreach($languages as $key=>$value)
									{
										if($numLanguages>1)
										{ ?>
											<h3><?= $value['name']; ?>:</h3> <?
										} ?>
										<textarea name="resumen[<?= $key; ?>]"<?= $editorResumen; ?> cols="110" rows="5"><?= $row[$key]['ntx_resumen']; ?></textarea> <?
										if($i<$numLanguages)
										{ ?>
											<div class="clear50"></div> <?
										}
										$i++;
									} ?>
								</td></tr>
							</table>
						</div> <?
					} ?>

					<h3>Contenido</h3>
					<div>
						<? /* Tener en cuenta si está activo el editor en el contenido */ ?>
						<table class="formTable w875 noMargin paddingL10px">
							<tr><td> <?
								$i = 1;
								foreach($languages as $key=>$value)
								{
									if($numLanguages>1)
									{ ?>
										<h3><?= $value['name']; ?>:</h3> <?
									} ?>
									<textarea name="contenido[<?= $key; ?>]"<?= $editor; ?> cols="110" rows="5"><?= $row[$key]['ntx_contenido']; ?></textarea> <?
									if($i<$numLanguages)
									{ ?>
										<div class="clear50"></div> <?
									}
									$i++;
								} ?>
							</td></tr>
						</table>
					</div> <?

					if($_GET['id']=='1')
					{ ?>
						<h3>Ficha técnica</h3>
						<div>
							<? /* Tener en cuenta si está activo el editor en el contenido */ ?>
							<table class="formTable w875 noMargin paddingL10px">
								<tr><td> <?
									$i = 1;
									foreach($languages as $key=>$value)
									{
										if($numLanguages>1)
										{ ?>
											<h3><?= $value['name']; ?>:</h3> <?
										} ?>
										<textarea name="ficha[<?= $key; ?>]"<?= $editor; ?> cols="110" rows="5"><?= $row[$key]['ficha']; ?></textarea> <?
										if($i<$numLanguages)
										{ ?>
											<div class="clear50"></div> <?
										}
										$i++;
									} ?>
								</td></tr>
							</table>
						</div>
						<h3>Sulfuro</h3>
						<div>
							<? /* Tener en cuenta si está activo el editor en el contenido */ ?>
							<table class="formTable w875 noMargin paddingL10px">
								<tr><td> <?
									$i = 1;
									foreach($languages as $key=>$value)
									{
										if($numLanguages>1)
										{ ?>
											<h3><?= $value['name']; ?>:</h3> <?
										} ?>
										<textarea name="sulfuro[<?= $key; ?>]"<?= $editor; ?> cols="110" rows="5"><?= $row[$key]['sulfuro']; ?></textarea> <?
										if($i<$numLanguages)
										{ ?>
											<div class="clear50"></div> <?
										}
										$i++;
									} ?>
								</td></tr>
							</table>
						</div>
						<h3>Información técnica</h3>
						<div>
							<? /* Tener en cuenta si está activo el editor en el contenido */ ?>
							<table class="formTable w875 noMargin paddingL10px">
								<tr><td> <?
									$i = 1;
									foreach($languages as $key=>$value)
									{
										if($numLanguages>1)
										{ ?>
											<h3><?= $value['name']; ?>:</h3> <?
										} ?>
										<textarea name="info_tecnica[<?= $key; ?>]"<?= $editor; ?> cols="110" rows="5"><?= $row[$key]['info_tecnica']; ?></textarea> <?
										if($i<$numLanguages)
										{ ?>
											<div class="clear50"></div> <?
										}
										$i++;
									} ?>
								</td></tr>
							</table>
						</div>
						<h3>Beneficios</h3>
						<div>
							<? /* Tener en cuenta si está activo el editor en el contenido */ ?>
							<table class="formTable w875 noMargin paddingL10px">
								<tr><td> <?
									$i = 1;
									foreach($languages as $key=>$value)
									{
										if($numLanguages>1)
										{ ?>
											<h3><?= $value['name']; ?>:</h3> <?
										} ?>
										<textarea name="beneficios[<?= $key; ?>]"<?= $editor; ?> cols="110" rows="5"><?= $row[$key]['beneficios']; ?></textarea> <?
										if($i<$numLanguages)
										{ ?>
											<div class="clear50"></div> <?
										}
										$i++;
									} ?>
								</td></tr>
							</table>
						</div>
						<h3>Usos</h3>
						<div>
							<? /* Tener en cuenta si está activo el editor en el contenido */ ?>
							<table class="formTable w875 noMargin paddingL10px">
								<tr><td> <?
									$i = 1;
									foreach($languages as $key=>$value)
									{
										if($numLanguages>1)
										{ ?>
											<h3><?= $value['name']; ?>:</h3> <?
										} ?>
										<textarea name="usos[<?= $key; ?>]"<?= $editor; ?> cols="110" rows="5"><?= $row[$key]['usos']; ?></textarea> <?
										if($i<$numLanguages)
										{ ?>
											<div class="clear50"></div> <?
										}
										$i++;
									} ?>
								</td></tr>
							</table>
						</div> <?
					}

					if($module['limite_img']>0) /* Si el límite de imágenes es mayor a cero */
					{ ?>
						<h3>Imágenes</h3>
						<div class="listPics"> <?
							$result = $db->select('noi_pk, noi_archivo', 'noticia_img', "not_pk='$_GET[regId]' ORDER BY noi_pk");
							$numRows = $db->num_rows($result);
							for($i=1; $i<=$module['limite_img']; $i++)
							{
								if($i<=$numRows)  // Registros ya existentes
								{
									$rowImg = $db->fetch_array($result);
									$imgPk = $rowImg['noi_pk'];  // Id. de la imagen
									if(empty($rowImg['noi_archivo']))
									{
										$imgClass = " class=\"noDisplay\"";
										$imgSrc = '';
										$classDel =  ' noDisplay';
									}
									else
									{
										$imgClass = '';
										$pos = mb_strrpos($rowImg['noi_archivo'], '.');
										$ini = mb_substr($rowImg['noi_archivo'], 0, $pos);
										$ext = mb_substr($rowImg['noi_archivo'], $pos);
										$imgSrc = '../uploads/news/'.$_GET['regId'].'/'.$ini.'--'.$thumbnail.$ext;
										$classDel =  '';
									}
									foreach($languages as $key=>$value)
									{
										$rowLang = $db->fetch_array($db->select('titulo', 'noi_txt', "noi_pk='$rowImg[noi_pk]' AND idi_pk='$key'"));
										if($rowLang && !empty($rowLang['titulo']))
											$imgTitle[$key] = $rowLang['titulo'];
										else
											$imgTitle[$key] = $descText[$key];
									}
								}
								else  // Registros por ingresar
								{
									$imgPk = '';  // Id. de la imagen
									$imgClass = " class=\"noDisplay\"";
									$imgSrc = '';
									$classDel =  ' noDisplay';
									foreach($languages as $key=>$value)
									{
										$imgTitle[$key] = $descText[$key];
									}
								} ?>
								<div class="previewPicture">
									<div class="imageContainer">
										<img src="<?= $imgSrc; ?>" alt="" width="120"<?= $imgClass; ?>>
										<div class="noPic"></div>
										<a class="redLink<?= $classDel; ?>"><span class="icon">X</span>Borrar</a>
										<input type="hidden" name="<?= 'deleteImg_'.$i; ?>" value="0" class="deleteImg">
									</div>
									<div class="fLeft"> <?
										foreach($languages as $key=>$value)
										{ ?>
											<input type="text" name="<?= 'imgTitulo_'.$i.'_'.$key; ?>" value="<?= $imgTitle[$key]; ?>" size="40" class="<?= 'desc'.ucfirst(mb_strtolower($value['abbreviation'])); ?>">
											<div class="clear5"></div> <?
										} ?>
										<input type="file" name="<?= 'imgArchivo_'.$i; ?>" value="">
										<input type="hidden" name="<?= 'imgPk_'.$i; ?>" value="<?= $imgPk; ?>">
									</div>
								</div>
								 <div class="file-preview"></div>
								<?
							} ?>
						</div> <?
					}

					if($module['adjuntos']=='1' && $module['limite_adj']>0) /* Si se permiten archivos adjuntos */
					{ ?>
						<h3>Archivos adjuntos</h3>
						<div><?
							$result = $db->select('noa_pk, noa_archivo', 'noticia_adjunto', "not_pk='$_GET[regId]' ORDER BY noa_pk");
							$numRows = $db->num_rows($result);
							for($i=1; $i<=$module['limite_adj']; $i++)
							{
								if($i<=$numRows)  // Registros ya existentes
								{
									$rowFile = $db->fetch_array($result);
									$adjPk = $rowFile['noa_pk'];  // Id. del archivo
									if(empty($rowFile['noa_archivo']))
										$filename = '';
									else
										$filename = $rowFile['noa_archivo'];
									foreach($languages as $key=>$value)
									{
										$rowLang = $db->fetch_array($db->select('titulo', 'noa_txt', "noa_pk='$rowFile[noa_pk]' AND idi_pk='$key'"));
										if($rowLang && !empty($rowLang['titulo']))
											$adjTitle[$key] = $rowLang['titulo'];
										else
											$adjTitle[$key] = $descText[$key];
									}
								}
								else  // Registros por ingresar
								{
									$adjPk = '';  // Id. del archivo
									$filename = '';
									foreach($languages as $key=>$value)
									{
										$adjTitle[$key] = $descText[$key];
									}
								} ?>
								<div class="filePreview"> <?
									if(!empty($filename))
									{ ?>
										<div class="fileName">
											<span><?= $filename; ?></span>
											<a class="redLink">Borrar</a>
											<input type="hidden" name="<?= 'deleteFile_'.$i; ?>" value="0" class="deleteFile">
										</div> <?
									} ?>
									<div class="clear"></div> <?
									foreach($languages as $key=>$value)
									{ ?>
										<input type="text" name="<?= 'adjTitulo_'.$i.'_'.$key; ?>" value="<?= $adjTitle[$key]; ?>" size="40" class="<?= 'desc'.ucfirst(mb_strtolower($value['abbreviation'])); ?>">
										<div class="clear5"></div> <?
									} ?>
									<input type="file" name="<?= 'adjArchivo_'.$i; ?>" value="">
									<input type="hidden" name="<?= 'adjPk_'.$i; ?>" value="<?= $adjPk; ?>">
								</div><?
							} ?>
						</div> <?
					} ?>

					<? /* SEO */ ?>
					<h3>SEO</h3>
					<div>
						<table class="formTable"> <?
							foreach($languages as $key=>$value)
							{ ?>
								<tr>
									<th><? echo $titleLabel; if($numLanguages>1) echo " ($value[abbreviation])"; ?>:</th>
									<td><input type="text" name="seoTitle[<?= $key; ?>]" size="45" maxlength="255" value="<?= $row[$key]['ntx_seo_title']; ?>"></td>
								</tr> <?
							}
							foreach($languages as $key=>$value)
							{ ?>
								<tr>
									<th>Keywords<? if($numLanguages>1) echo " ($value[abbreviation])"; ?>:</th>
									<td><input type="text" name="seoKeywords[<?= $key; ?>]" size="115" maxlength="255" value="<?= $row[$key]['ntx_seo_keywords']; ?>"></td>
								</tr> <?
							}
							foreach($languages as $key=>$value)
							{ ?>
								<tr>
									<th>Description<? if($numLanguages>1) echo " ($value[abbreviation])"; ?>:</th>
									<td>
										<textarea name="seoDescription[<?= $key; ?>]" cols="60" rows="2" class="contador"><?= $row[$key]['ntx_seo_description']; ?></textarea>
										<div id="longitud"></div>
									</td>
								</tr> <?
							} ?>
						</table>
					</div>
				</div>
				<div class="clear10"></div>
				<div align="center"><input type="submit" value="Guardar"></div>
			</form>
		</div> <?
	}
	else
	{
		$numRows = $db->num_rows($db->select('not_pk', 'noticia', "pk='$_GET[id]'")); ?>
		<header>
		<div class="fLeft"><h1><?= $module['nombre']; ?></h1></div>
		<div class="fRight"><a href="<? echo $_SERVER['PHP_SELF'].'?id='.$_GET['id'].'&interfaz=insert'?>" class="newItem"><span class="icon">+</span> Crear</a></div>
		<div class="clear"></div>
		</header> <?
		if($numRows>0)
		{
			$offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
			if($module['orden']=='2')
				$order = 'not_fecha DESC, noticia.not_pk DESC';
			elseif($module['orden']=='3')
				$order = 'not_fecha ASC, noticia.not_pk ASC';
			else  // $module['orden']=='1'
				$order = 'ntx_titulo ASC';
			reset($languages);
			$condi = "pk='$_GET[id]' AND noticia.not_pk=noticia_txt.not_pk AND idi_pk='".key($languages)."' ORDER BY $order LIMIT ".$conf['settings']->pager." OFFSET $offset";
			$result = $db->select('noticia.not_pk AS not_pk, not_fecha, ntx_titulo', 'noticia, noticia_txt', $condi); ?>
			<div class="contentPane">
				<table width="100% "cellspacing="0" cellpadding="0" class="dataTable">
					<tr>
						<th width="80">Fecha</th>
						<th width="530"><?= $titleLabel; ?></th>
						<th>Opciones</th>
					</tr><?
					while($row=$db->fetch_array($result))
					{
						$date = explode('-', $row['not_fecha']);
						// En caso de que el registro se haya grabado cuando el idioma 1 estaba inactivo, quedando el título vacío, mostrar el título del registro del siguiente idioma activo.
						if($row['ntx_titulo']=='')
						{
							$rowT = $db->fetch_array($db->select('ntx_titulo', 'noticia_txt', "not_pk='$row[not_pk]' AND idi_pk IN (".implode(', ', array_keys($languages)).") AND ntx_titulo!='' ORDER BY idi_pk ASC LIMIT 1"));
							$row['ntx_titulo'] = $rowT['ntx_titulo'];
						} ?>
						<tr>
							<td><?= $date[2].'/'.$date[1].'/'.$date[0]; ?></td>
							<td><?= $row['ntx_titulo']; ?></td>
							<td>
								<a href="<?= $_SERVER['PHP_SELF'].'?id='.$_GET['id'].'&regId='.$row['not_pk'].'&interfaz=edit'?>" class="blueLink"><span class="icon">V</span> Editar</a>
								<a class="blueLink" onclick="javascript:duplicateConfirm('<?= $row['not_pk']; ?>', '<?= str_replace(array("'", "\""), '`', $row['ntx_titulo']); ?>')"><span class="icon">W</span> Duplicar</a>
								<a class="redLink" onclick="javascript:removalConfirm('<?= $row['not_pk']; ?>', '<?= str_replace(array("'", "\""), '`', $row['ntx_titulo']); ?>')"><span class="icon">X</span> Borrar</a>
							</td>
						</tr> <?
					} ?>
				</table> <?
				if($pag=pager($numRows, $conf['settings']->pager, $offset))  // Si es necesario paginar
				{
					$currentUrl = "$_SERVER[PHP_SELF]?id=$_GET[id]";
					$prev = is_null($pag['prevOffset']) ? 'Anterior' : "<a href=\"$currentUrl&offset=$pag[prevOffset]\">Anterior</a>";
					$next = is_null($pag['nextOffset']) ? 'Siguiente' : "<a href=\"$currentUrl&offset=$pag[nextOffset]\">Siguiente</a>"; ?>
					<div class="pager">
						<div class="fLeft"><?= "Viendo del $pag[from] al $pag[to] de $numRows"; ?></div>
						<div class="fRight">
							<?= $prev; ?>
							<select onchange="javascript:window.location='<?= "$currentUrl&offset="; ?>'+this.value"> <?
								foreach($pag['pages'] as $num=>$val)
								{
									$selected = $val['selected'] ? ' selected' : ''; ?>
									<option value="<?= $val['offset']; ?>"<?= $selected; ?>><?= $num; ?></option> <?
								} ?>
							</select>
							<?= $next; ?>
						</div>
						<div class="clear"></div>
					</div> <?
				} ?>
			</div><?
		}
	}
} ?>
<script type="text/javascript" src="js/jquery.validationEngine-es.js"></script>
<script type="text/javascript" src="js/jquery.validationEngine.js"></script>
<script type="text/javascript">
<!--
	$(document).ready(function(){
		$("#newsForm").validationEngine();

		// Datepicker
		$('.dateSelector').datepicker({
			showOn: "both",
			buttonImage: "images/calendar.png",
			buttonImageOnly: true,
			buttonText: "Calendario",
			dateFormat: "dd/mm/yy",
			firstDay: 1,
		}); <?
		if($_GET['interfaz']=='insert')
		{ ?>
			$('.dateSelector').datepicker('setDate', 'today'); <?
		} ?>

		// Acordeon
		$('#accordion').accordion({
			active:false,
			collapsible:true,
			heightStyle: "content",
		});

		// Borrar la imagen
		$('.previewPicture .redLink').click(function(){
			$(this).parents('.imageContainer').find('img').hide();
			$(this).parents('.imageContainer').find('.deleteImg').val('1'); <?
			foreach($languages as $key=>$value)
			{
				$langStr = ucfirst(mb_strtolower($value['abbreviation'])); ?>
				$(this).parents('.previewPicture').find('<?= '.desc'.$langStr; ?>').val('<?= $descText[$key]; ?>'); <?
			} ?>
			$(this).parents('.previewPicture').find('span.fcText').html('Seleccionar archivo...');
			$(this).hide();
		});

		// Borrar el archivo
		$('.filePreview .redLink').click(function(){
			$(this).parents('.fileName').hide();
			$(this).parents('.fileName').find('.deleteFile').val('1'); <?
			foreach($languages as $key=>$value)
			{
				$langStr = ucfirst(mb_strtolower($value['abbreviation'])); ?>
				$(this).parents('.filePreview').find('<?= '.desc'.$langStr; ?>').val('<?= $descText[$key]; ?>'); <?
			} ?>
			$(this).parents('.filePreview').find('span.fcText').html('Seleccionar archivo...');
			$(this).hide();
		});

		// Generar la vista previa de la imagen (No es compatible con ie ni safari)
		$('.previewPicture input[type=file]').change(function(e){
			$(this).parents('.previewPicture').find('.imageContainer img').show();
			newPic = $(this).parents('.previewPicture').find('.imageContainer img');
			if($(this).prop('files')[0])
			{
				var reader = new FileReader();
                	reader.onload = function (e) {
					newPic.attr('src', e.target.result);
				};
               	reader.readAsDataURL($(this).prop('files')[0]);
				$(this).parents('.previewPicture').find('.redLink').show();
			}
		});

		// Limpiar el input de la descripcion (Imágenes)
		$('.previewPicture input[type=text]').focus(function(){ <?
			foreach($languages as $key=>$value)
			{ ?>
				if($(this).val()=="<?= $descText[$key]; ?>")
				{
					$(this).val('');
				} <?
			} ?>
		});

		$('.previewPicture input[type=text]').blur(function(){
			if($(this).val()=='')
			{ <?
				foreach($languages as $key=>$value)
				{
					$class = 'desc'.ucfirst(mb_strtolower($value['abbreviation'])); ?>
					if($(this).attr('class')=='<?= $class; ?>')
						defaultText = "<?= $descText[$key]; ?>"; <?
				} ?>
				$(this).val(defaultText);
			}
		});

		// Limpiar el input de la descripcion (Archivos adjuntos)
		$('.filePreview input[type=text]').focus(function(){ <?
			foreach($languages as $key=>$value)
			{ ?>
				if($(this).val()=="<?= $descText[$key]; ?>")
				{
					$(this).val('');
				} <?
			} ?>
		});

		$('.filePreview input[type=text]').blur(function(){
			if($(this).val()=='')
			{ <?
				foreach($languages as $key=>$value)
				{
					$class = 'desc'.ucfirst(mb_strtolower($value['abbreviation'])); ?>
					if($(this).attr('class')=='<?= $class; ?>')
						defaultText = "<?= $descText[$key]; ?>"; <?
				} ?>
				$(this).val(defaultText);
			}
		});

		$('.contador').each(function(){
			var longitud = $(this).val().length;
			$(this).parent().find('#longitud').html('<b>'+longitud+'</b> caracteres');
			$(this).keyup(function(){ 
				var nueva_longitud = $(this).val().length;
				$(this).parent().find('#longitud').html('<b>'+nueva_longitud+'</b> caracteres');
			});
		});

		$('.subc').hide();
		if($('#categoria').val() != ''){
			$('#subcategoria .' + $('#categoria').val()).show();
		}
		$('#categoria').change(function(){
			$('#subcategoria').val('');
			$('.subc').hide();
			if($(this).val() != ''){
				$('.' + $(this).val()).show();
			}
		});
	});

	function removalConfirm(regId, regTitle)
	{
		if(confirm("¿Confirma la eliminación de '"+regTitle+"'?"))
			window.location = '<?= "$_SERVER[PHP_SELF]?id=$_GET[id]&delete="; ?>'+regId;
	}

	function duplicateConfirm(regId, regTitle)
	{
		if(confirm("¿Confirma la duplicación de '"+regTitle+"'?"))
			window.location = '<?= "$_SERVER[PHP_SELF]?id=$_GET[id]&duplicate="; ?>'+regId;
	}

	function enviar()
	{
		$('.demoField').each(function(key, element){
			if($.trim($(element).val())!=''){
				$(element).val(' '+$.trim($(element).val()));
			}
		});
		return true;
	}
-->
</script>
<? include("footer.php"); ?>