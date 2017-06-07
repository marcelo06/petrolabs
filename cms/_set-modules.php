<?
$pageTitle = "Módulos activos";
$menuActive = 5; ?>
<? include("header.php"); ?> <?
if(!permit($db, $_SESSION['per_pk'], '3'))
{
	echo "<script type=\"text/javascript\"> alert('Acceso denegado.'); window.history.back(); </script>";
	exit();
}
if($_SERVER['REQUEST_METHOD']=='POST')
{
	$msg = '';
	$changed = false;
	$file = file_get_contents("lib/modules.php");
	switch($_POST['form'])
	{
		case 'modules':
			$content = isset($_POST['content']) ? 'TRUE' : 'FALSE';
			$pages = isset($_POST['pages']) ? 'TRUE' : 'FALSE';
			$subpages = isset($_POST['subpages']) ? 'TRUE' : 'FALSE';
			$images = isset($_POST['images']) ? 'TRUE' : 'FALSE';
			if($content!=$_POST['oldContent'])
			{
				$file = str_replace("this->content = $_POST[oldContent]", "this->content = $content", $file);
				if(!$changed)
					$changed = true;
			}
			if($pages!=$_POST['oldPages'])
			{
				$file = str_replace("this->pages = $_POST[oldPages]", "this->pages = $pages", $file);
				if(!$changed)
					$changed = true;
			}
			if($subpages!=$_POST['oldSubpages'])
			{
				$file = str_replace("this->subpages = $_POST[oldSubpages]", "this->subpages = $subpages", $file);
				if(!$changed)
					$changed = true;
			}
			if($images!=$_POST['oldImages'])
			{
				$file = str_replace("this->images = $_POST[oldImages]", "this->images = $images", $file);
				if(!$changed)
					$changed = true;
			}
			if($changed)
			{
				file_put_contents("lib/modules.php", $file);
				file_put_contents("lib/modules.txt", "$content $pages $subpages $images");
				$msg = 'Actualización realizada.';
			}
			break;
		case 'slider':
			$sliderImgMaxNum = $_POST['sliderImgMaxNum']=='' ? '0' : $_POST['sliderImgMaxNum'];
			$sliderImgWidth = $_POST['sliderImgWidth']=='' ? '0' : $_POST['sliderImgWidth'];
			$sliderImgHeight = $_POST['sliderImgHeight']=='' ? '0' : $_POST['sliderImgHeight'];
			$sliderImgDesc = $_POST['sliderImgDesc'];
			if($sliderImgMaxNum!=$_POST['oldSliderImgMaxNum'])
			{
				$file = str_replace("this->sliderImgMaxNum = $_POST[oldSliderImgMaxNum]", "this->sliderImgMaxNum = $sliderImgMaxNum", $file);
				if(!$changed)
					$changed = true;
			}
			if($sliderImgWidth!=$_POST['oldSliderImgWidth'])
			{
				$file = str_replace("this->sliderImgWidth = $_POST[oldSliderImgWidth]", "this->sliderImgWidth = $sliderImgWidth", $file);
				if(!$changed)
					$changed = true;
			}
			if($sliderImgHeight!=$_POST['oldSliderImgHeight'])
			{
				$file = str_replace("this->sliderImgHeight = $_POST[oldSliderImgHeight]", "this->sliderImgHeight = $sliderImgHeight", $file);
				if(!$changed)
					$changed = true;
			}
			if($sliderImgDesc!=$_POST['oldSliderImgDesc'])
			{
				$file = str_replace("this->sliderImgDesc = $_POST[oldSliderImgDesc]", "this->sliderImgDesc = $sliderImgDesc", $file);
				if(!$changed)
					$changed = true;
			}
			if($changed)
			{
				file_put_contents("lib/modules.php", $file);
				file_put_contents("lib/slider.txt", "$sliderImgMaxNum $sliderImgWidth $sliderImgHeight $sliderImgDesc");
				$msg = 'Actualización realizada.';
			}
			break;
		case 'languages':
			while($status = each($_POST['langStatus']))
			{
				$db->update('idioma', "idi_estado='$status[1]'", "idi_pk='$status[0]'");
			}
			$howDisplayLang = $_POST['howDisplayLang'];
			if($howDisplayLang!=$_POST['oldHowDisplayLang'])
			{
				$file = str_replace("this->howDisplayLang = '$_POST[oldHowDisplayLang]'", "this->howDisplayLang = '$howDisplayLang'", $file);
				if(!$changed)
					$changed = true;
			}
			if($changed)
			{
				file_put_contents("lib/modules.php", $file);
				file_put_contents("lib/languages.txt", "$howDisplayLang");
			}
			$msg = 'Actualización realizada.';
			break;
		case 'news':
			if(empty($_POST['mnoPk']))  // Agregar
			{
				$columns = 'nombre, archivo, orden, fecha, resumen, editor, tamano_img, limite_img, adjuntos, limite_adj, paginador';
				$values = "'$_POST[nombre]', '$_POST[archivo]', '$_POST[orden]', '$_POST[fecha]', '$_POST[resumen]', '$_POST[editor]', ".
					"'$_POST[tamano_img]', '$_POST[limite_img]', '$_POST[adjuntos]', '$_POST[limite_adj]', '$_POST[paginador]'";
				if($_POST['calendario']=='1' && $db->num_rows($db->select('pk', 'modulo_noticias', "calendario='1'"))>0)
				{
					$db->update('modulo_noticias', "calendario='0'", "calendario='1'");
					$columns .= ', calendario';
					$values .= ", '1'";
				}
				$db->insert('modulo_noticias', $columns, $values);
				$pk = $db->last_insert_id();
				$file = file_get_contents("lib/news.php");
				$file = str_replace('newsId', $pk, $file);
				$fileSingle = file_get_contents("lib/news-single.php");
				$fileSingle = str_replace('newsId', $pk, $fileSingle);
				chdir('../');
				file_put_contents($_POST['archivo'].'.php', $file);
				file_put_contents($_POST['archivo'].'-single.php', $fileSingle);
				chdir('cms/');
				$msg = 'Inserción realizada.';
			}
			else  // Editar
			{
				if($_POST['calendario']=='1' && $db->num_rows($db->select('pk', 'modulo_noticias', "calendario='1' AND pk!=$_POST[mnoPk]"))>0)
				{
					$db->update('modulo_noticias', "calendario='0'", "calendario='1' AND pk!=$_POST[mnoPk]");
				}
				$set = "nombre='$_POST[nombre]', archivo='$_POST[archivo]', orden='$_POST[orden]', fecha='$_POST[fecha]', calendario='$_POST[calendario]', resumen='$_POST[resumen]', editor='$_POST[editor]', ".
					"tamano_img='$_POST[tamano_img]', limite_img='$_POST[limite_img]', adjuntos='$_POST[adjuntos]', limite_adj='$_POST[limite_adj]', paginador='$_POST[paginador]'";
				$db->update('modulo_noticias', $set, 'pk='.$_POST['mnoPk']);
				if($_POST['archivo']!=$_POST['arch'])
				{
					chdir('../');
					rename($_POST['arch'].'.php', $_POST['archivo'].'.php');
					rename($_POST['arch'].'-single.php', $_POST['archivo'].'-single.php');
					chdir('cms/');
				}
				$msg = 'Actualización realizada.';
			}
			break;
	}
	if($msg=='')
		echo "<script type=\"text/javascript\"> alert('No hubo cambios.'); </script>";
	else
		echo "<script type=\"text/javascript\"> alert('".$msg."'); </script>";
	echo "<script type=\"text/javascript\"> window.location='$_SERVER[PHP_SELF]'; </script>";
	exit();
}
elseif(!empty($_GET['del']))
{
	$db->delete('modulo_noticias', 'pk='.$_GET['del']);
	echo "<script type=\"text/javascript\"> alert('Eliminación realizada.'); window.location='$_SERVER[PHP_SELF]'; </script>";
	exit();
}
$modules = explode(' ', file_get_contents("lib/modules.txt"));
$slider = explode(' ', file_get_contents("lib/slider.txt"));
$languages = file_get_contents("lib/languages.txt");
if(empty($_GET['mnoPk']))
{
	$nombre = $archivo = $tamano_img = $limite_img = $limite_adj = $paginador = '';
	$orden = $fecha = $resumen = $editor = $adjuntos = $calendario = '';
}
else
{
	$row = $db->fetch_array($db->select('*', 'modulo_noticias', 'pk='.$_GET['mnoPk']));
	$nombre = $row['nombre'];
	$archivo = $row['archivo'];
	$tamano_img = $row['tamano_img'];
	$limite_img = $row['limite_img'];
	$limite_adj = $row['limite_adj'];
	$paginador = $row['paginador'];
	$orden = $row['orden'];
	$fecha = $row['fecha'];
	$resumen = $row['resumen'];
	$editor = $row['editor'];
	$adjuntos = $row['adjuntos'];
	$calendario = $row['calendario'];
} ?>
<header>
	<h1>Módulos activos para páginas</h1>
</header>
<div class="contentPane">
	<form id="modulesForm" name="modulesForm" action="<?= $_SERVER['PHP_SELF']; ?>" method="post">
		<input type="hidden" name="form" value="modules">
		<input type="hidden" name="oldContent" value="<?= $modules[0]; ?>" />
		<input type="hidden" name="oldPages" value="<?= $modules[1]; ?>" />
		<input type="hidden" name="oldSubpages" value="<?= $modules[2]; ?>" />
		<input type="hidden" name="oldImages" value="<?= $modules[3]; ?>" />
		<table class="formTable">
			<tr>
				<th>Contenidos:</th>
				<td><input type="checkbox" name="content" value="1"<? if($modules[0]=='TRUE') echo " checked"; ?>></td>
				<td width="50"></td>
				<th>Páginas:</th>
				<td><input type="checkbox" name="pages" value="1"<? if($modules[1]=='TRUE') echo " checked"; ?>></td>
				<td width="50"></td>
				<th>Sub-páginas:</th>
				<td><input type="checkbox" name="subpages" value="1"<? if($modules[2]=='TRUE') echo " checked"; ?>></td>
				<td width="50"></td>
				<th>Gestor de imágenes:</th>
				<td><input type="checkbox" name="images" value="1"<? if($modules[3]=='TRUE') echo " checked"; ?>></td>
				<td width="20"></td>
				<td><input type="submit" value="Aceptar"></td>
			</tr>
		</table>
	</form>
</div>
<? /* Slider */ ?>
<header class="marginT35px">
	<h1>Slider</h1>
</header>
<div class="contentPane">
	<form id="sliderForm" name="sliderForm" action="<?= $_SERVER['PHP_SELF']; ?>" method="post">
		<input type="hidden" name="form" value="slider">
		<input type="hidden" name="oldSliderImgMaxNum" value="<?= $slider[0]; ?>">
		<input type="hidden" name="oldSliderImgWidth" value="<?= $slider[1]; ?>">
		<input type="hidden" name="oldSliderImgHeight" value="<?= $slider[2]; ?>">
		<input type="hidden" name="oldSliderImgDesc" value="<?= $slider[3]; ?>">
		<table class="formTable">
			<tr>
				<th>Límite de imágenes:</th>
				<td><input type="text" name="sliderImgMaxNum" value="<?= $slider[0]; ?>" size="45"></td>
			</tr>
			<tr>
				<th>Tamaño de las imágenes:</th>
				<td>
					<input type="text" name="sliderImgWidth" value="<?= $slider[1]; ?>" size="10"> x
					<input type="text" name="sliderImgHeight" value="<?= $slider[2]; ?>" size="10">
				</td>
			</tr>
			<tr>
				<th>Activar descripción:</th>
				<td>
					<select name="sliderImgDesc">
						<option value="FALSE">No</option>
						<option value="TRUE"<? if($slider[3]=='TRUE') echo " selected"; ?>>Si</option>
					</select>
				</td>
			</tr>
			<tr>
				<th>&nbsp;</th>
				<td>
					<input type="submit" value="Aceptar">
				</td>
			</tr>
		</table>
	</form>
</div>

<? /* Idiomas */ ?>
<form id="languagesForm" name="languagesForm" action="<?= $_SERVER['PHP_SELF']; ?>" method="post">
	<input type="hidden" name="form" value="languages">
	<input type="hidden" name="oldHowDisplayLang" value="<?= $languages; ?>" />
	<header class="marginT35px">
		<div class="fLeft"><h1>Idiomas</h1></div>
		<div class="fRight">
			<div class="clear5"></div>
			Mostrar en la página:
			<select name="howDisplayLang">
				<option value="name">Nombre</option>
				<option value="abbreviation"<? if($languages=='abbreviation') echo " selected"; ?>>Abreviatura</option>
				<option value="image"<? if($languages=='image') echo " selected"; ?>>Imagen</option>
			</select>
		</div>
		<div class="clear"></div>
	</header>
	<div class="contentPane">
		<table width="100%" cellpadding="0" cellspacing="0" class="dataTable">
			<tr>
				<th>Nombre</th>
				<th>Abreviatura</th>
				<th>Imagen</th>
				<th>Estado</th>
			</tr> <?
			$result = $db->select('*', 'idioma ORDER BY idi_pk');
			while($row = $db->fetch_array($result))
			{ ?>
				<tr>
					<td class="whiteTd"><?= $row['idi_txt']; ?></td>
					<td class="whiteTd"><?= $row['idi_locale']; ?></td>
					<td class="whiteTd">
						<? /* Cambiar la ruta de esta imagen por la de la carpeta images de la pagina */ ?>
						<img src="<?= '../images/'.mb_strtolower($row['idi_locale']).'-flag.png' ?>" alt="">
					</td>
					<td class="whiteTd">
						<select name="langStatus[<?= $row['idi_pk']; ?>]" class="sLang">
							<option value="1"<? if($row['idi_estado']=='1') echo " selected"; ?>>Activo</option>
							<option value="0"<? if($row['idi_estado']=='0') echo " selected"; ?>>Inactivo</option>
						</select>
					</td>
				</tr> <?
			} ?>
		</table>
		<div class="clear10"></div>
		<div align="right"><input type="submit" value="Aceptar"></div>
	</div>
</form>

<? /* Módulo de noticias */ ?>
<header class="marginT35px">
	<h1>Módulo de noticias</h1>
</header>
<div class="contentPane"> <?
	$result = $db->select('*', 'modulo_noticias ORDER BY nombre');
	if($db->num_rows($result)>0)
	{ ?>
		<table class="dataTable" cellpadding="0" cellspacing="0" width="100%">
			<tr>
				<th width="200">Nombre</th>
				<th>Acciones</th>
			</tr> <?
			while($row = $db->fetch_array($result))
			{ ?>
				<tr>
					<td><?= $row['nombre']; ?></td>
					<td>
						<a href="<?= $_SERVER['PHP_SELF'].'?mnoPk='.$row['pk'].'#formNews'; ?>" class="blueLink"><span class="icon">V</span> Editar</a>
						<a onclick="confirmDelete(<?= $row['pk']; ?>)" class="redLink"><span class="icon">X</span> Borrar</a>
					</td>
				</tr> <?
			} ?>
		</table> <?
	} ?>
	<div class="clear10"></div>
	<a name="formNews"></a>
	<div id="cFormNews">
		<form id="newsForm" name="newsForm" action="<?= $_SERVER['PHP_SELF']; ?>" method="post">
			<input type="hidden" name="form" value="news">
			<input type="hidden" name="mnoPk" value="<? if(isset($_GET['mnoPk'])) echo $_GET['mnoPk']; ?>">
			<input type="hidden" name="arch" value="<?= $archivo; ?>">
			<table class="formTable">
				<tr>
					<th>Nombre:</th>
					<td><input type="text" name="nombre" size="45" value="<?= $nombre; ?>"></td>
				</tr>
				<tr>
					<th>Nombre del archivo:</th>
					<td><input type="text" name="archivo" size="45" value="<?= $archivo; ?>"></td>
				</tr>
				<tr>
					<th>Ordenar elementos por:</th>
					<td>
						<select name="orden">
							<option value="1">Nombre (A-Z)</option>
							<option value="2"<? if($orden=='2') echo " selected"; ?>>Los más nuevos primero</option>
							<option value="3"<? if($orden=='3') echo " selected"; ?>>Los más nuevos al final</option>
							<? /* option value="4"<? if($orden=='4') echo " selected"; ?>>Activar campo orden</option */?>
						</select>
					</td>
				</tr>
				<tr>
					<? /* Seleccionar si se oculta  el campo de la fecha en el cms. (Por ej. para los productos no es necesario pedir la fecha al crearlos, entonces se oculta para que el cliente no vea ese campo) */?>
					<th>Seleccionar fecha:</th>
					<td>
						<select name="fecha">
							<option value="1">Si</option>
							<option value="0"<? if($fecha=='0') echo " selected"; ?>>No</option>
						</select>
					</td>
				</tr>
				<tr>
					<th>Activar calendario:</th>
					<td>
						<select name="calendario">
							<option value="1">Si</option>
							<option value="0"<? if(empty($calendario)) echo " selected"; ?>>No</option>
						</select>
						<small>Sólo un módulo puede tener el calendario activo.</small>
					</td>
				</tr>
				<tr>
					<th>Activar resumen:</th>
					<td>
						<select name="resumen">
							<option value="1">Si</option>
							<option value="0"<? if($resumen=='0') echo " selected"; ?>>No</option>
						</select>
					</td>
				</tr>
				<tr>
					<th>Activar editor en el contenido:</th>
					<td>
						<select name="editor">
							<option value="1">Si</option>
							<option value="0"<? if($editor=='0') echo " selected"; ?>>No</option>
						</select>
					</td>
				</tr>
				<tr>
					<th>Tamaño de las imágenes:</th>
					<td><input type="text" name="tamano_img" size="45" value="<?= $tamano_img; ?>"></td>
				</tr>
				<tr>
					<th>Límite de imágenes:</th>
					<td><input type="text" name="limite_img" size="10" value="<?= $limite_img; ?>"></td>
				</tr>
				<tr>
					<th>Permitir archivos adjuntos:</th>
					<td>
						<select name="adjuntos">
							<option value="1">Si</option>
							<option value="0"<? if($adjuntos=='0') echo " selected"; ?>>No</option>
						</select>
					</td>
				</tr>
				<tr>
					<th>Límite de archivos adjuntos:</th>
					<td><input type="text" name="limite_adj" size="10" value="<?= $limite_adj; ?>"></td>
				</tr>
				<tr>
					<th>Tamaño del paginador:</th>
					<td><input type="text" name="paginador" size="10" value="<?= $paginador; ?>"></td>
				</tr>
				<tr>
					<th>&nbsp;</th>
					<td>
						<input type="submit" value="Aceptar">
					</td>
				</tr>
			</table>
		</form>
	</div>
</div>

<script type="text/javascript">
	function confirmDelete(pk)
	{
		if(confirm('¿Confirma la eliminación de este módulo de noticias?'))
		{
			window.location = 'set-modules.php?del='+pk;
		}
	}
</script>

<? include("footer.php"); ?>