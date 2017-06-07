<?
include("lib/database.php");
$db = new database();
$module = $db->fetch_array($db->select('nombre', 'modulo_noticias', "pk='$_GET[id]'"));
$db->disconnect();
$pageTitle = 'EDS';
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
			$columns = 'nombre, id_ciudad';

			$nombre_eds = $_POST['nombre_eds'];
			$ciudad_eds = $_POST['ciudad_eds'];

			$values = "'$nombre_eds', '$ciudad_eds'";
		if($db->insert('eds', $columns, $values))
		{
			// $not_pk = $db->last_insert_id();
			// chdir('../');
			// $updir = getcwd().DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.'news'.DIRECTORY_SEPARATOR.$not_pk.DIRECTORY_SEPARATOR;
			// mkdir($updir);
			// chdir('cms/');
			

			

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
		while($titulo = each($_POST['titulo']))
		{
			$contenido = each($_POST['contenido']);
			$seoTitle = each($_POST['seoTitle']);
			$seoKeywords = each($_POST['seoKeywords']);
			$seoDescription = each($_POST['seoDescription']);
			if(isset($_POST['resumen']))
				$resumen = each($_POST['resumen']);
			if($_GET['id']=='4')
				$canje = each($_POST['canje']);
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
		
		$condi = "id_eds =". $_GET['delete'];
	

		if($db->delete('eds', $condi))  // Eliminar registro principal
		{
					
			echo "<script type=\"text/javascript\" language=\"javascript\"> alert('El registro ha sido eliminado.') </script>";
		}
		 else
			echo "<script type=\"text/javascript\" language=\"javascript\"> alert('No fue posible eliminar el registro.') </script>";
	}
}

if($_GET['interfaz']=='insert')
{ 
	$ciudades = array();
	$result = $db->select('id_ciudad, nom_ciudad', 'ciudad');	

	for($i=0; $row=$db->fetch_array($result); $i++){
		$ciudades[$i] = $row;
	}	

	?>
	<header>
	<div class="fLeft">
		<h1>Crear <?= $module['nombre']; ?></h1>
	</div>
	<div class="fRight"><a href="<?= $_SERVER['PHP_SELF']; ?>"><span class="icon">]</span> Regresar al listado</a></div>
	<div class="clear"></div>
	</header>
	<div class="contentPane">
		<form id="newsForm" name="newsForm" action="<?= $_SERVER['PHP_SELF'].'?interfaz=insert'; ?>" enctype="multipart/form-data" method="post" onsubmit="return validar()">
			<input type="hidden" name="form" value="insert">
			<table class="formTable w875"> 
				<!-- <?
			    foreach($languages as $key=>$value)
				{ ?>
					<tr>
						<th><span class="required">*</span> <? echo $titleLabel; if($numLanguages>1) echo " ($value[abbreviation])"; ?>:</th>
						<td><input type="text" id="titulo[<?= $key; ?>]" name="titulo[<?= $key; ?>]" size="100" maxlength="255" class="validate[required]"></td>
					</tr> <?
				}
				?> -->
				<tr>
					<th><span class="required">*</span>Nombre (Es):</th>
					<td><input id="nombre_eds" type="text" name="nombre_eds" size="90" maxlength="255" class="validate[required]"></td>

				</tr>

				<!-- <tr>
					<th><span class="required">Nombre: </span></th>
					<td><input type="text" name="nombre_eds" size="100" maxlength="255" class="validate[required]"></td>

				</tr>
 -->
				<tr>
					<th><span class="required">*</span> Ciudad:</th>
					<td>
						<select id="ciudad" name="ciudad_eds" class="validate[required]">
							<?php foreach ($ciudades as $ciudad): ?>
								<option value="<?= $ciudad['id_ciudad'] ?>"><?= $ciudad['nom_ciudad'] ?></option>
								
							<?php endforeach ?>
												
						</select>
					</td>
				</tr>

			</table>
			
			<div class="clear10"></div>
			<div align="center"><input type="submit" value="Guardar"></div>
		</form>
	</div> 
<?
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
				
				<div class="clear10"></div>
				<div align="center"><input type="submit" value="Guardar"></div>
			</form>
		</div> <?
	}
	else
	{
		$numRows = $db->num_rows($db->select('*', 'eds')); ?>
		<header>
		<div class="fLeft"><h1><?= $pageTitle; ?></h1></div>
		<div class="fRight" ><a href="<? echo $_SERVER['PHP_SELF'].'?interfaz=insert'?>" class="newItem"><span class="icon" >+</span> Crear</a></div>
		<div class="clear"></div>
		</header> <?
		if($numRows>0)
		{
			$offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
			reset($languages);
			
			
			$condi = "eds.id_ciudad = ciudad.id_ciudad ORDER BY eds.nombre LIMIT ".$conf['settings']->pager." OFFSET $offset";
			
			$result = $db->select('eds.id_eds, eds.nombre, ciudad.nom_ciudad', 'eds, ciudad', $condi);
			?>
			<div class="contentPane">
				<table width="100% "cellspacing="0" cellpadding="0" class="dataTable">
					<tr>
						<th width="300">EDS</th>
						<th width="300">Ciudad</th>
						<th>Opciones</th>
					</tr><?

					while($row=$db->fetch_array($result))
					{
					 ?>
						<tr>
							<td><?= $row['nombre']; ?></td>
							<td><?= $row['nom_ciudad']; ?></td>
							<td>
								<!-- <a href="<?= $_SERVER['PHP_SELF'].'?id='.$_GET['id'].'&regId='.$row['id_eds'].'&interfaz=edit'?>" class="blueLink"><span class="icon">V</span> Editar</a>-->
								
								<a class="redLink" onclick="javascript:removalConfirm('<?= $row['id_eds']; ?>', '<?= str_replace(array("'", "\""), '`', $row['nombre']); ?>')"><span class="icon">X</span> Borrar</a> 
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
		}else{
		?>
			<p>No se ha registrado ninguna estacion de servicio. Para crearla presione el boton Crear</p>
		<?

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

		

	});

	function validar(){

		nombre_eds = document.getElementById("nombre_eds").value;
		if( valor == null || valor.length == 0 || /^\s+$/.test(valor) ) {
 		 return false;
		}
	}

	function removalConfirm(regId, regTitle)
	{
		if(confirm("¿Confirma la eliminación de '"+regTitle+"'?"))
			window.location = '<?= "$_SERVER[PHP_SELF]?delete="; ?>'+regId;
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