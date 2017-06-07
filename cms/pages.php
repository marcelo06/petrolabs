<?
$pageTitle = "Páginas";
$menuActive = 2; ?>
<? include("header.php"); ?> <?

// Función para imprimir el listado de páginas
function printPageRow($db, $delPerm, $langs, $pageId=NULL)
{
	if(is_null($pageId))
	{
		$str1 = ' IS NULL';
		$str2 = '';
	}
	else
	{
		$str1 = "=$pageId";
		$str2 = '----- ';
	}
	$condition = 'sec.sec_pk=stx.sec_pk AND sec_pk_origen'.$str1.' AND idi_pk=1 ORDER BY sec_index DESC, sec.sec_pk';
	$result = $db->select('sec.sec_pk AS sec_pk, sec_index, sec_archivo, stx_nombre', 'seccion AS sec, seccion_txt AS stx', $condition);
	while($row = $db->fetch_array($result))
	{
		$home = $row['sec_index']=='1' ? " <span class=\"required\">[página inicial]</span>" : '';
		$delLink = $delPerm ? " <a class=\"redLink\" onclick=\"deletePage('$row[sec_pk]', '".str_replace(array("'", "\""), '`', $row['stx_nombre'])."')\"><span class=\"icon\">X</span> Borrar</a>" : '';

		// En caso de que el registro se haya grabado cuando el idioma 1 estaba inactivo, quedando el título vacío, mostrar el título del registro del siguiente idioma activo.
		if($row['stx_nombre']=='')
		{
			$rowT = $db->fetch_array($db->select('stx_nombre', 'seccion_txt', "sec_pk='$row[sec_pk]' AND idi_pk IN ($langs) AND stx_nombre!='' ORDER BY idi_pk ASC LIMIT 1"));
			$row['stx_nombre'] = $rowT['stx_nombre'];
		}

		echo "<tr>
						<td>$str2$row[stx_nombre]$home</td>
						<td>$row[sec_archivo]</td>
						<td><a href=\"$_SERVER[PHP_SELF]?pageId=$row[sec_pk]&interfaz=edit\" class=\"blueLink\"><span class=\"icon\">V</span> Editar</a>$delLink</td>
					</tr>";
		printPageRow($db, $delPerm, $langs, $row['sec_pk']);
	}
}

// Array de idiomas
$result = $db->select('idi_pk, idi_locale, idi_txt', 'idioma', "idi_estado='1' ORDER BY idi_pk");
$numLanguages = $db->num_rows($result);
while($row=$db->fetch_array($result))
{
	$id = $row['idi_pk'];
	$languages[$id] = array('name'=>$row['idi_txt'], 'abbreviation'=>$row['idi_locale']);
	$descText[$id] = $numLanguages>1 ? "Descripción ($row[idi_locale])" : 'Descripción';
}

$thumbnail = '120x90';

if($_SERVER['REQUEST_METHOD']=='POST')
{
	switch($_POST['task'])
	{
		case 'insert':
			$origen = empty($_POST['origen']) ? 'NULL' : $_POST['origen'];
			$index = isset($_POST['index']) ? '1' : '0';
			$contacto = isset($_POST['contacto']) ? '1' : '0';
			$slider = isset($_POST['slider']) ? '1' : '0';
			$values = "$origen, '$_POST[lim_img]', '$_POST[imgconf]', '$index', '$_POST[archivo]', '$contacto', '$slider'";
			if($db->insert('seccion', 'sec_pk_origen, sec_lim_img, sec_imgconf, sec_index, sec_archivo, sec_contacto, sec_slider', $values))
			{
				$sec_pk = $db->last_insert_id();
				if(isset($_POST['index']))
				{
					$db->update('seccion', "sec_index='0'", "sec_index='1' AND sec_pk!=$sec_pk");
				}
				$file = file_get_contents("lib/page.php");
				$file = str_replace('pageId', $sec_pk, $file);
				chdir('../');
				mkdir(getcwd().DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.'pages'.DIRECTORY_SEPARATOR.$sec_pk.DIRECTORY_SEPARATOR);
				file_put_contents($_POST['archivo'].'.php', $file);
				chdir('cms/');
				$failure = FALSE;
				while($nombre = each($_POST['nombre']))
				{
					if(!$db->insert("seccion_txt", "sec_pk, idi_pk, stx_nombre", "'$sec_pk', '$nombre[0]', '".addslashes($nombre[1])."'") && !$failure)
						$failure = TRUE;
				}
				if($failure)
					echo "<script type=\"text/javascript\" language=\"javascript\"> alert('No fue posible ingresar toda la información.') </script>";
				echo "<script type=\"text/javascript\" language=\"javascript\"> alert('La página ha sido creada.') </script>";
				echo "<script type=\"text/javascript\" language=\"javascript\"> window.location='".$_SERVER['PHP_SELF']."' </script>";
				exit();
			}
			else
				echo "<script type=\"text/javascript\" language=\"javascript\"> alert('No fue posible crear la página.') </script>";
			break;
		case 'insertText':
			if($db->insert("bloque", "sec_pk", "'$_GET[pageId]'"))
			{
				$blo_pk = $db->last_insert_id();
				$failure = FALSE;
				while($text = each($_POST['text']))
				{
					if(!$db->insert("bloque_txt", "blo_pk, idi_pk, btx_contenido", "'$blo_pk', '$text[0]', '".addslashes(trim($text[1]))."'") && !$failure)
						$failure = TRUE;
				}
				if($failure)
					echo "<script type=\"text/javascript\" language=\"javascript\"> alert('No fue posible ingresar toda la información.') </script>";
				echo "<script type=\"text/javascript\" language=\"javascript\"> alert('El texto ha sido creado.') </script>";
				echo "<script type=\"text/javascript\" language=\"javascript\"> window.location='$_SERVER[PHP_SELF]?pageId=$_GET[pageId]&textId=$blo_pk&interfaz=editText' </script>";
				exit();
			}
			else
				echo "<script type=\"text/javascript\" language=\"javascript\"> alert('No fue posible crear el texto.') </script>";
			break;
		case 'updateText':
			$failure = FALSE;
			while($text = each($_POST['text']))
			{
				$txt = addslashes(trim($text[1]));
				if($db->num_rows($db->select('btx_pk', 'bloque_txt', "blo_pk='$_GET[textId]' AND idi_pk='$text[0]'"))>0)  // Ya existe registro en el idioma
				{
					if(!$db->update('bloque_txt', "btx_contenido='$txt'", "blo_pk='$_GET[textId]' AND idi_pk='$text[0]'") && !$failure)  // Actualizar
						$failure = TRUE;
				}
				else
				{
					if(!$db->insert('bloque_txt', 'blo_pk, idi_pk, btx_contenido', "'$_GET[textId]', '$text[0]', '$txt'") && !$failure)  // Insertar
						$failure = TRUE;
				}
			}
			if($failure)
				echo "<script type=\"text/javascript\" language=\"javascript\"> alert('No fue posible actualizar toda la información.') </script>";
			echo "<script type=\"text/javascript\" language=\"javascript\"> alert('El texto ha sido actualizado.') </script>";
			break;
		case 'updateImages':
			chdir('../');
			$updir = getcwd().DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.'pages'.DIRECTORY_SEPARATOR.$_GET['pageId'].DIRECTORY_SEPARATOR;
			chdir('cms/');

			// Actualizar imágenes
			$failure = FALSE;
			for($i=1; $i<=$_POST['limImg']; $i++)
			{
				$imgPk = $_POST['imgPk_'.$i];
				if(empty($imgPk))  // Nuevo registro de imagen
				{
					$sei_pk = insert_img($db, 'seccion_img', $_GET['pageId'], $updir, $_FILES['imgArchivo_'.$i]['name'], $_FILES['imgArchivo_'.$i]['tmp_name'], $_POST['dimensions']);
					if($sei_pk)
					{
						foreach($languages as $key=>$value)
						{
							$imgTitulo = in_array($_POST['imgTitulo_'.$i.'_'.$key], $descText) ? '' : $_POST['imgTitulo_'.$i.'_'.$key];
							if(!$db->insert('sei_txt', 'sei_pk, idi_pk, titulo', "'$sei_pk', '$key', '$imgTitulo'") && !$failure)
								$failure = TRUE;
						}
					}
					elseif(!$failure)
						$failure = TRUE;
				}
				else  // Registro de imagen ya existente
				{
					if(update_img($db, 'seccion_img', $imgPk, $_POST['deleteImg_'.$i], $updir, $_FILES['imgArchivo_'.$i]['name'], $_FILES['imgArchivo_'.$i]['tmp_name'], $_POST['dimensions']))
					{
						foreach($languages as $key=>$value)
						{
							$imgTitulo = in_array($_POST['imgTitulo_'.$i.'_'.$key], $descText) ? '' : $_POST['imgTitulo_'.$i.'_'.$key];
							if($db->num_rows($db->select('titulo', 'sei_txt', "sei_pk='$imgPk' AND idi_pk='$key'"))>0)  // Ya existe el texto en este idioma
							{
								if(!$db->update('sei_txt', "titulo='$imgTitulo'", "sei_pk='$imgPk' AND idi_pk='$key'") && !$failure)
									$failure = TRUE;
							}
							else  // Aún no existe el texto en este idioma
							{
								if(!$db->insert('sei_txt', 'sei_pk, idi_pk, titulo', "'$imgPk', '$key', '$imgTitulo'") && !$failure)
									$failure = TRUE;
							}
						}
					}
					elseif(!$failure)
						$failure = TRUE;
				}
			}
			if($failure)
				echo "<script type=\"text/javascript\" language=\"javascript\"> alert('No fue posible actualizar toda la información.') </script>";
			echo "<script type=\"text/javascript\" language=\"javascript\"> alert('La información ha sido actualizada.') </script>";
			break;
		case 'updateSEO':
			$failure = FALSE;
			foreach($languages as $key=>$value)
			{
				$seoTitle = $_POST['seoTitle'][$key];
				$seoKeywords = $_POST['seoKeywords'][$key];
				$seoDescription = $_POST['seoDescription'][$key];
				if($db->num_rows($db->select('stx_pk', 'seccion_txt', "sec_pk='$_GET[pageId]' AND idi_pk='$key'"))>0)  // Ya existe registro en el idioma
				{
					// Actualizar
					$set = "stx_seo_title='$seoTitle', stx_seo_keywords='$seoKeywords', stx_seo_description='$seoDescription'";
					if(!$db->update('seccion_txt', $set, "sec_pk='$_GET[pageId]' AND idi_pk='$key'") && !$failure)
						$failure = TRUE;
				}
				else  // No existe registro en el idioma
				{
					// Insertar
					$values = "'$_GET[pageId]', '$key', '', '$seoTitle', '$seoKeywords', '$seoDescription'";
					if(!$db->insert('seccion_txt', 'sec_pk, idi_pk, stx_nombre, stx_seo_title, stx_seo_keywords, stx_seo_description', $values) && !$failure)
						$failure = TRUE;
				}
			}
			if($failure)
				echo "<script type=\"text/javascript\" language=\"javascript\"> alert('No fue posible actualizar toda la información.') </script>";
			echo "<script type=\"text/javascript\" language=\"javascript\"> alert('La información ha sido actualizada.') </script>";
			break;
		case 'update':
			$origen = empty($_POST['origen']) ? 'NULL' : $_POST['origen'];
			$index = isset($_POST['index']) ? '1' : '0';
			$contacto = isset($_POST['contacto']) ? '1' : '0';
			$slider = isset($_POST['slider']) ? '1' : '0';
			$set = "sec_pk_origen=$origen, sec_lim_img='$_POST[lim_img]', sec_imgconf='$_POST[imgconf]', sec_index='$index', sec_contacto='$contacto', sec_slider='$slider'";
			if($db->update('seccion', $set, "sec_pk='$_GET[pageId]'"))
			{
				$failure = FALSE;
				if($_POST['archivo']!=$_POST['arch'])
				{
					chdir('../');
					if(rename($_POST['arch'].'.php', $_POST['archivo'].'.php'))
						$db->update('seccion', "sec_archivo='$_POST[archivo]'", "sec_pk='$_GET[pageId]'");
					else
						$failure = TRUE;
					chdir('cms/');
				}
				if(isset($_POST['index']))
					$db->update('seccion', "sec_index='0'", "sec_index='1' AND sec_pk!='$_GET[pageId]'");
				while($nombre = each($_POST['nombre']))
				{
					if($db->num_rows($db->select('stx_pk', 'seccion_txt', "sec_pk='$_GET[pageId]' AND idi_pk='$nombre[0]'"))>0)  // Ya existe registro en el idioma
					{
						if(!$db->update('seccion_txt', "stx_nombre='".addslashes($nombre[1])."'", "sec_pk='$_GET[pageId]' AND idi_pk='$nombre[0]'") && !$failure)  // Actualizar
							$failure = TRUE;
					}
					else  // No existe registro en el idioma
					{
						if(!$db->insert('seccion_txt', 'sec_pk, idi_pk, stx_nombre', "'$_GET[pageId]', '$nombre[0]', '".addslashes($nombre[1])."'") && !$failure)  // Insertar
							$failure = TRUE;
					}
				}
				if($failure)
					echo "<script type=\"text/javascript\" language=\"javascript\"> alert('No fue posible ingresar toda la información.') </script>";
				echo "<script type=\"text/javascript\" language=\"javascript\"> alert('La configuración de la página ha sido actualizada.') </script>";
			}
			else
				echo "<script type=\"text/javascript\" language=\"javascript\"> alert('No fue posible actualizar la configuración de la página.') </script>";
	}
}
elseif(isset($_GET['task']))
{
	if($_GET['task']=='deleteText')
	{
		if($db->delete('bloque', "blo_pk='$_GET[textId]'"))
		{
			$db->delete('bloque_txt', "blo_pk='$_GET[textId]'");
			echo "<script language=\"javascript\" type=\"text/javascript\"> alert('El texto ha sido eliminado.'); </script>";
		}
		else
			echo "<script language=\"javascript\" type=\"text/javascript\"> alert('No fue posible eliminar el texto.'); </script>";
	}
	elseif($_GET['task']=='delete')  // Borrar página
	{
		$condi = "sec_pk='$_GET[pageId]'";
		$rowPage = $db->fetch_array($db->select('sec_archivo', 'seccion', $condi));
		if($db->delete('seccion', $condi))  // Eliminar registro principal
		{
			$db->delete('seccion_txt', $condi);  // Eliminar registros de textos en idiomas

			// Eliminar registros de textos
			$result = $db->select('blo_pk', 'bloque', $condi);
			while($row=$db->fetch_array($result))
			{
				$db->delete('bloque_txt', "blo_pk='$row[blo_pk]'");
			}
			$db->delete('bloque', $condi);

			// Eliminar registros de imágenes
			$result = $db->select('sei_pk', 'seccion_img', $condi);
			while($row=$db->fetch_array($result))
			{
				$db->delete('sei_txt', "sei_pk='$row[sei_pk]'");
			}
			$db->delete('seccion_img', $condi);

			// Eliminar registros de archivos adjuntos
			$result = $db->select('sea_pk', 'seccion_adjunto', $condi);
			while($row=$db->fetch_array($result))
			{
				$db->delete('sea_txt', "sea_pk='$row[sea_pk]'");
			}
			$db->delete('seccion_adjunto', $condi);

			// Borrar carpeta de imágenes y archivos
			chdir('../');
			unlink($rowPage['sec_archivo'].'.php');
			remove_dir(getcwd().DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.'pages'.DIRECTORY_SEPARATOR.$_GET['pageId']);
			chdir('cms/');
			echo "<script type=\"text/javascript\" language=\"javascript\"> alert('La página ha sido eliminada.') </script>";
		}
		else
			echo "<script type=\"text/javascript\" language=\"javascript\"> alert('No fue posible eliminar la página.') </script>";
	}
}
if(!isset($_GET['interfaz']))
{
	$_GET['interfaz'] = "";
}
if($_GET['interfaz']=='insert')
{ ?>
	<header>
	<div class="fLeft">
		<h1>Nueva página</h1>
	</div>
	<div class="fRight"><a href="<? echo $_SERVER['PHP_SELF']?>"><span class="icon">]</span> Regresar al listado de páginas</a></div>
	<div class="clear"></div>
	</header>
	<div class="contentPane">
		<form id="pagesForm" name="pagesForm" method="post" action="<? echo $_SERVER['PHP_SELF'].'?interfaz=insert'?>">
			<input type="hidden" name="task" value="insert"> <?
			// Asegurar la inserción del registro para el idioma 1 (en la tabla seccion_txt de la BD) en caso
			// de que este idioma esté inactivo, pues se necesita para listar correctamente los registros.
			if(!array_key_exists(1, $languages))
			{ ?>
				<input type="hidden" name="nombre[1]" value=""> <?
			} ?>
			<table class="formTable"> <?
				foreach($languages as $key=>$value)
				{ ?>
					<tr>
						<th><span class="required">*</span> Título<? if($numLanguages>1) echo " ($value[abbreviation])"; ?>:</th>
						<td><input type="text" id="nombre[<?= $key; ?>]" name="nombre[<?= $key; ?>]" size="45" class="validate[required]"></td>
					</tr> <?
				} ?>
				<tr>
					<th><span class="required">*</span> Nombre del archivo:</th>
					<td><input type="text" id="archivo" name="archivo" size="45" class="validate[required,custom[filename]]"></td>
				</tr>
				<tr>
					<th><span class="required">*</span> Límite de imágenes:</th>
					<td><input type="text" id="lim_img" name="lim_img" size="10" class="validate[required,custom[integer],min[0]]"></td>
				</tr>
				<tr>
					<th>Tamaño de las imágenes:</th>
					<td><input type="text" id="imgconf" name="imgconf" size="45"></td>
				</tr>
				<tr>
					<th>Página superior:</th>
					<td>
						<? /* Mostrar únicamente las de nivel 1 (igual que en el easy actual solo vamos a tener un nivel de profundidad)*/ ?>
						<select name="origen">
							<option value=""></option> <?
							$condition = "sec.sec_pk=stx.sec_pk AND sec_pk_origen IS NULL AND idi_pk=1 ORDER BY stx_nombre";
							$result = $db->select("sec.sec_pk AS sec_pk, stx_nombre", "seccion AS sec, seccion_txt AS stx", $condition);
							while($row = $db->fetch_array($result))
							{ ?>
								<option value="<?= $row['sec_pk']; ?>"><?= $row['stx_nombre']; ?></option> <?
							} ?>
						</select>
					</td>
				</tr>
				<tr>
					<th>&nbsp;</th>
					<td><label><input type="checkbox" name="index" value="1"> Configurar como página inicial</label></td>
				</tr>
				<tr>
					<th>&nbsp;</th>
					<td><label><input type="checkbox" name="contacto" value="1"> Incluir formulario de contacto</label></td>
				</tr>
				<tr>
					<th>&nbsp;</th>
					<td><label><input type="checkbox" name="slider" value="1" checked> Mostrar slider</label></td>
				</tr>
				<tr>
					<th>&nbsp;</th>
					<td>
						<input type="submit" value="Guardar">
					</td>
				</tr>
			</table>
		</form>
	</div> <?
}
else
{
	if($_GET['interfaz']=='edit' || $_GET['interfaz']=='editText' || $_GET['interfaz']=='insertText')
	{
		$condition = 'sec.sec_pk=stx.sec_pk AND sec.sec_pk='.$_GET['pageId'].' AND idi_pk=1';
		$columns = 'stx_nombre, sec_pk_origen, sec_index, sec_imgconf, sec_lim_img, sec_archivo, sec_contacto, sec_slider';
		$result = $db->select($columns, 'seccion AS sec, seccion_txt AS stx', $condition);
		$row = $db->fetch_array($result);
		$dimensions = $thumbnail.','.str_replace(' ', '', $row['sec_imgconf']);
		$strTextId = isset($_GET['textId']) ? '&textId='.$_GET['textId'] : '';
		if(isset($_POST['task']))
			$task = $_POST['task'];
		else
		{
			if($_GET['interfaz']=='insertText')
				$task = 'insertText';
			elseif($_GET['interfaz']=='editText')
				$task = 'updateText';
			else
				$task = '';
		} ?>
		<header>
		<div class="fLeft">
			<h1>Contenidos de la página: <?= $row['stx_nombre']; ?></h1>
		</div>
		<div class="fRight"><a href="<?= $_SERVER['PHP_SELF']; ?>"><span class="icon">]</span> Regresar al listado de páginas</a></div>
		<div class="clear"></div>
		</header>
		<div class="contentPane">
			<form id="pagesForm" name="pagesForm" action="<?= $_SERVER['PHP_SELF'].'?pageId='.$_GET['pageId'].$strTextId.'&interfaz='.$_GET['interfaz']; ?>" enctype="multipart/form-data" method="post">
				<input type="hidden" name="activeTab" id="activeTab" value="0">
				<input type="hidden" id="task" name="task" value="<?= $task; ?>">
				<input type="hidden" name="limImg" value="<?= $row['sec_lim_img']; ?>">
				<input type="hidden" name="dimensions" value="<?= $dimensions; ?>">
				<div id="tabs">
					<ul>
						<li><a href="#tabs-1"><?= $_GET['pageId']=='6' ? 'Videos' : 'Textos' ?></a></li> <?
						if($_GET['pageId']!='6')
						{ ?>
							<li><a href="#tabs-2">Imágenes</a></li> <?
						} ?>
						<li><a href="#tabs-3">SEO</a></li> <?
						if(permit($db, $_SESSION['per_pk'], '11'))
						{ ?>
							<li><a href="#tabs-4">Configuración</a></li> <?
						} ?>
					</ul>
					<div id="tabs-1">
						<? /* Textos */ ?><?
						if($_GET['interfaz']=="editText")
						{ ?>
							<header class="noBg">
								<div class="fRight">
									<a href="<?= $_SERVER['PHP_SELF'].'?pageId='.$_GET['pageId'].'&interfaz=edit'; ?>" class="redText"><span class="icon">]</span> Regresar al listado de <?= $_GET['pageId']=='6' ? 'videos' : 'textos' ?></a>
								</div>
								<div class="clear"></div>
							</header> <?
							$i=1;
							foreach($languages as $key=>$value)
							{
								$result_btx = $db->select('btx_contenido', 'bloque_txt', 'blo_pk='.$_GET['textId'].' AND idi_pk='.$key);
								$row_btx = $db->fetch_array($result_btx);
								if($numLanguages>1)
								{ ?>
									<h3><?= $value['name']; ?>:</h3> <?
								}
								if($_GET['pageId']=='6')
								{ ?>
									<input type="text" id="text[<?= $key; ?>]" name="text[<?= $key; ?>]" size="100" maxlength="100" class="validate[custom[youtubeUrl]]" value="<?= htmlentities($row_btx['btx_contenido'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="http://www.youtube.com/watch?v=zzK-n0CGJvo"> <?
								}
								else
								{ ?>
									<textarea name="text[<?= $key; ?>]" class="editorTextArea"><?= $row_btx['btx_contenido']; ?></textarea> <?
								}
								if($i<$numLanguages)
								{ ?>
									<div class="clear50"></div> <?
								}
								$i++;
							}
						}
						elseif($_GET['interfaz']=="insertText")
						{ ?>
							<header class="noBg">
								<div class="fRight">
									<a href="<?= $_SERVER['PHP_SELF'].'?pageId='.$_GET['pageId'].'&interfaz=edit'; ?>" class="redText"><span class="icon">]</span> Regresar al listado de <?= $_GET['pageId']=='6' ? 'videos' : 'textos' ?></a>
								</div>
								<div class="clear"></div>
							</header><?
							$i=1;
							foreach($languages as $key=>$value)
							{
								if($numLanguages>1)
								{ ?>
									<h3><?= $value['name']; ?>:</h3> <?
								}
								if($_GET['pageId']=='6')
								{ ?>
									<input type="text" id="text[<?= $key; ?>]" name="text[<?= $key; ?>]" size="100" maxlength="100" class="validate[custom[youtubeUrl]]" placeholder="http://www.youtube.com/watch?v=zzK-n0CGJvo"> <?
								}
								else
								{ ?>
									<textarea name="text[<?= $key; ?>]" class="editorTextArea"></textarea> <?
								}
								if($i<$numLanguages)
								{ ?>
									<div class="clear50"></div> <?
								}
							}
						}
						else
						{
							if(permit($db, $_SESSION['per_pk'], '10') || $_GET['pageId']=='6')
							{ ?>
								<header class="noBg">
									<div class="fRight"><a href="<?= $_SERVER['PHP_SELF'].'?pageId='.$_GET['pageId'].'&interfaz=insertText'; ?>" class="newItem">+ Nuevo <?= $_GET['pageId']=='6' ? 'video' : 'texto' ?></a></div>
									<div class="clear"></div>
								</header> <?
							}
							else
							{ ?>
								<div class="clear10"></div> <?
							}
							$result_blo = $db->select("blo_pk", "bloque", "sec_pk='$_GET[pageId]'");
							if($db->num_rows($result_blo)>0)
							{ ?>
								<table class="dataTable" cellpadding="0" cellspacing="0" style="width:880px">
									<tr>
										<th width="30">Id.</th>
										<th width="630"><?= $_GET['pageId']=='6' ? 'URL' : 'Resumen' ?></th>
										<th>Opciones</th>
									</tr> <?
									$condition = "blo.blo_pk=btx.blo_pk AND sec_pk='$_GET[pageId]' AND idi_pk=1 ORDER BY blo.blo_pk";
									$result_blo = $db->select("blo.blo_pk AS blo_pk, blo_id, btx_contenido", "bloque AS blo, bloque_txt AS btx", $condition);
									$i = 0;
									while($row_blo = $db->fetch_array($result_blo))
									{
										$str = html_entity_decode(strip_tags($row_blo['btx_contenido']), ENT_QUOTES, 'UTF-8');
										if(mb_strlen($str)>150)
											$str=mb_substr($str, 0, 120).'...'; ?>
										<tr>
											<td><?= $i; ?></td>
											<td><?= $str; ?></td>
											<td>
												<a href="<?= $_SERVER['PHP_SELF'].'?pageId='.$_GET['pageId'].'&textId='.$row_blo['blo_pk'].'&interfaz=editText'; ?>" class="blueLink"><span class="icon">V</span> Editar</a>
												<a class="redLink" onclick="deleteText(<?= $row_blo['blo_pk']; ?>, <?= $_GET['pageId']; ?>)"><span class="icon">X</span> Borrar</a>
											</td>
										</tr> <?
										$i++;
									} ?>
								</table> <?
							}
						} ?>
						<div class="clear"></div>
					</div> <?
					if($_GET['pageId']!='6')
					{ ?>
						<div id="tabs-2">
							<? /* Imágenes */ ?>
							<div class="clear10"></div>
							<div class="listPics"><?
								$result = $db->select('sei_pk, sei_archivo', 'seccion_img', "sec_pk='$_GET[pageId]' ORDER BY sei_pk");
								$numRows = $db->num_rows($result);
								for($i=1; $i<=$row['sec_lim_img']; $i++)
								{
									if($i<=$numRows)  // Registros ya existentes
									{
										$rowImg = $db->fetch_array($result);
										$imgPk = $rowImg['sei_pk'];  // Id. de la imagen
										if(empty($rowImg['sei_archivo']))
										{
											$imgClass = " class=\"noDisplay\"";
											$imgSrc = '';
											$classDel =  ' noDisplay';
										}
										else
										{
											$imgClass = '';
											$pos = mb_strrpos($rowImg['sei_archivo'], '.');
											$ini = mb_substr($rowImg['sei_archivo'], 0, $pos);
											$ext = mb_substr($rowImg['sei_archivo'], $pos);
											$imgSrc = '../uploads/pages/'.$_GET['pageId'].'/'.$ini.'--'.$thumbnail.$ext;
											$classDel =  '';
										}
										foreach($languages as $key=>$value)
										{
											$rowLang = $db->fetch_array($db->select('titulo', 'sei_txt', "sei_pk='$rowImg[sei_pk]' AND idi_pk='$key'"));
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
							</div>
							<div class="clear"></div>
						</div> <?
					} ?>
					<div id="tabs-3">
						<? /* SEO */ ?>
						<table class="formTable"> <?
							foreach($languages as $key=>$value)
							{
								$row_stx = $db->fetch_array($db->select('stx_seo_title', 'seccion_txt', "sec_pk='$_GET[pageId]' AND idi_pk='$key'")); ?>
								<tr>
									<th>Title<? if($numLanguages>1) echo " ($value[abbreviation])"; ?>:</th>
									<td><input type="text" name="seoTitle[<?= $key; ?>]" size="45" maxlength="255" value="<?= $row_stx['stx_seo_title']; ?>"></td>
								</tr> <?
							}
							foreach($languages as $key=>$value)
							{
								$row_stx = $db->fetch_array($db->select('stx_seo_keywords', 'seccion_txt', "sec_pk='$_GET[pageId]' AND idi_pk='$key'")); ?>
								<tr>
									<th>Keywords<? if($numLanguages>1) echo " ($value[abbreviation])"; ?>:</th>
									<td><input type="text" name="seoKeywords[<?= $key; ?>]" size="115" maxlength="255" value="<?= $row_stx['stx_seo_keywords']; ?>"></td>
								</tr> <?
							}
							foreach($languages as $key=>$value)
							{
								$row_stx = $db->fetch_array($db->select('stx_seo_description', 'seccion_txt', "sec_pk='$_GET[pageId]' AND idi_pk='$key'")); ?>
								<tr>
									<th>Description<? if($numLanguages>1) echo " ($value[abbreviation])"; ?>:</th>
									<td>
										<textarea name="seoDescription[<?= $key; ?>]" cols="60" rows="2" class="contador"><?= $row_stx['stx_seo_description']; ?></textarea>
										<div id="longitud"></div>
									</td>
								</tr> <?
							} ?>
						</table>
					</div> <?
					if(permit($db, $_SESSION['per_pk'], '11'))
					{ ?>
						<div id="tabs-4">
							<? /* Configuración */ ?>
							<input type="hidden" id="arch" name="arch" value="<?= $row['sec_archivo']; ?>">
							<table class="formTable"> <?
								foreach($languages as $key=>$value)
								{
									$condition = 'sec.sec_pk=stx.sec_pk AND sec.sec_pk='.$_GET['pageId'].' AND idi_pk='.$key;
									$row_stx = $db->fetch_array($db->select('stx_nombre', 'seccion AS sec, seccion_txt AS stx', $condition)); ?>
									<tr>
										<th><span class="required">*</span> Título<? if($numLanguages>1) echo " ($value[abbreviation])"; ?>:</th>
										<td><input type="text" id="nombre[<?= $key; ?>]" name="nombre[<?= $key; ?>]" size="45" class="validate[required]" value="<?= htmlentities($row_stx['stx_nombre'], ENT_QUOTES, 'UTF-8'); ?>"></td>
									</tr> <?
								} ?>
								<tr>
									<th><span class="required">*</span> Nombre del archivo:</th>
									<td><input type="text" id="archivo" name="archivo" size="45" class="validate[required,custom[filename]]" value="<?= $row['sec_archivo']; ?>"></td>
								</tr>
								<tr>
									<th><span class="required">*</span> Límite de imágenes:</th>
									<td><input type="text" id="lim_img" name="lim_img" size="10" class="validate[required,custom[integer],min[0]]" value="<?= $row['sec_lim_img']; ?>"></td>
								</tr>
								<tr>
									<th>Tamaño de las imágenes:</th>
									<td><input type="text" id="imgconf" name="imgconf" size="45" value="<?= $row['sec_imgconf']; ?>"></td>
								</tr>
								<tr>
									<th>Página superior:</th>
									<td>
										<? /* Mostrar únicamente las de nivel 1 (igual que en el easy actual solo vamos a tener un nivel de profundidad)*/ ?>
										<select name="origen">
											<option value=""></option> <?
											$condition = "sec.sec_pk=stx.sec_pk AND sec_pk_origen IS NULL AND idi_pk=1 AND sec.sec_pk!='$_GET[pageId]' ORDER BY sec_orden, sec.sec_pk";
											$result = $db->select("sec.sec_pk AS sec_pk, stx_nombre", "seccion AS sec, seccion_txt AS stx", $condition);
											while($row_stx = $db->fetch_array($result))
											{ ?>
												<option value="<?= $row_stx['sec_pk']; ?>"<? if($row_stx['sec_pk']==$row['sec_pk_origen']) echo ' selected'; ?>><?= $row_stx['stx_nombre']; ?></option> <?
											} ?>
										</select>
									</td>
								</tr>
								<tr>
									<th>&nbsp;</th>
									<td><label><input type="checkbox" name="index" value="1"<? if($row['sec_index']=='1') echo ' checked'; ?>> Configurar como página inicial</label></td>
								</tr>
								<tr>
									<th>&nbsp;</th>
									<td><label><input type="checkbox" name="contacto" value="1"<? if($row['sec_contacto']=='1') echo ' checked'; ?>> Incluir formulario de contacto</label></td>
								</tr>
								<tr>
									<th>&nbsp;</th>
									<td><label><input type="checkbox" name="slider" value="1"<? if($row['sec_slider']=='1') echo ' checked'; ?>> Mostrar slider</label></td>
								</tr>
							</table>
						</div> <?
					} ?>
				</div>
				<div align="center">
					<div class="clear10"></div>
					<input type="submit" value="Guardar">
				</div>
			</form>
		</div> <?
	}
	else
	{ ?>
		<header>
		<div class="fLeft"><h1>Páginas</h1></div> <?
		if(permit($db, $_SESSION['per_pk'], '8'))
		{ ?>
			<div class="fRight"><a href="<? echo $_SERVER['PHP_SELF'].'?interfaz=insert'?>" class="newItem"><span class="icon">+</span> Nueva página</a></div> <?
		} ?>
		<div class="clear"></div>
		</header>
		<div class="contentPane">
			<table width="100% "cellspacing="0" cellpadding="0" class="dataTable">
				<tr>
					<th width="250">Página</th>
					<th width="250">Nombre del archivo</th>
					<th>Opciones</th>
				</tr>	<?
				printPageRow($db, permit($db, $_SESSION['per_pk'], '9'), implode(', ', array_keys($languages))); ?>
			</table>
		</div> <?
	}
} ?>
<script type="text/javascript" src="js/jquery.validationEngine-es.js"></script>
<script type="text/javascript" src="js/jquery.validationEngine.js"></script>
<script type="text/javascript">
<!--
	$(document).ready(function(){
		$("#pagesForm").validationEngine();

		// Tabs
        $("#tabs").tabs({
			collapsible: true,
            activate: function (event, ui) {
				$('#activeTab').val(ui.newTab.index())
            },
			active: <? if(!isset($_POST['activeTab'])) echo '0'; else echo $_POST['activeTab'];?>,
			//heightStyle: "auto"
        });

		$('#ui-id-1').click(function(){ <?
			if($_GET['interfaz']=='insertText')
			{ ?>
				$('#task').val('insertText'); <?
			}
			elseif($_GET['interfaz']=='editText')
			{ ?>
				$('#task').val('updateText'); <?
			}
			else
			{ ?>
				$('#task').val(''); <?
			} ?>
		});
		$('#ui-id-2').click(function(){
			$('#task').val('updateImages');
		});
		$('#ui-id-3').click(function(){
			$('#task').val('updateSEO');
		});
		$('#ui-id-4').click(function(){
			$('#task').val('update');
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

		// Generar la vista previa de la imagen (No es compatible con ie ni safari)
		$('input[type=file]').change(function(e){
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

		// Limpiar el input
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

		$('.contador').each(function(){
			var longitud = $(this).val().length;
			$(this).parent().find('#longitud').html('<b>'+longitud+'</b> caracteres');
			$(this).keyup(function(){ 
				var nueva_longitud = $(this).val().length;
				$(this).parent().find('#longitud').html('<b>'+nueva_longitud+'</b> caracteres');
			});
		});
	});

	function deleteText(textId, pageId)
	{
		if(confirm('¿Confirma la eliminación del texto '+textId+'?'))
		{
			window.location = 'pages.php?pageId='+pageId+'&interfaz=edit&textId='+textId+'&task=deleteText';
		}
	}

	function deletePage(pageId, pageName)
	{
		if(confirm("¿Confirma la eliminación de la página '"+pageName+"'?"))
			window.location = '<?= "$_SERVER[PHP_SELF]?task=delete&pageId="; ?>'+pageId;
	}
-->
</script>
<? include("footer.php"); ?>