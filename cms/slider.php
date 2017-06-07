<?
$pageTitle = "Slider - Cabecera de la página";
$menuActive = 5; ?>
<? include("header.php"); ?> <?

// Array de idiomas
$result = $db->select('idi_pk, idi_locale, idi_txt', 'idioma', "idi_estado='1' ORDER BY idi_pk");
$numLanguages = $db->num_rows($result);
while($row=$db->fetch_array($result))
{
	$id = $row['idi_pk'];
	$languages[$id] = array('name'=>$row['idi_txt'], 'abbreviation'=>$row['idi_locale']);
	$titText[$id] = $numLanguages>1 ? "Título ($row[idi_locale])" : 'Título';
	$descText[$id] = $numLanguages>1 ? "Descripción ($row[idi_locale])" : 'Descripción';
}

$thumbnail = '120x90';

if($_SERVER['REQUEST_METHOD']=='POST')
{
	chdir('../');
	$updir = getcwd().DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.'slider'.DIRECTORY_SEPARATOR;
	chdir('cms/');
	$dimensions = $thumbnail.','.$conf['modules']->sliderImgWidth.'x'.$conf['modules']->sliderImgHeight;
	$failure = FALSE;
	for($i=1; $i<=$conf['modules']->sliderImgMaxNum; $i++)
	{
		$imgPk = $_POST['imgPk_'.$i];
		if(empty($imgPk))  // Nuevo registro de imagen
		{
			$sei_pk = insert_img($db, 'seccion_img', '0', $updir, $_FILES['imgArchivo_'.$i]['name'], $_FILES['imgArchivo_'.$i]['tmp_name'], $dimensions);
			if($sei_pk)
			{
				foreach($languages as $key=>$value)
				{
					$imgTitulo = in_array($_POST['imgTitulo_'.$i.'_'.$key], $titText) ? '' : $_POST['imgTitulo_'.$i.'_'.$key];
					$imgDescripcion = in_array($_POST['imgDesc_'.$i.'_'.$key], $descText) ? '' : $_POST['imgDesc_'.$i.'_'.$key];
					if(!$db->insert('sei_txt', 'sei_pk, idi_pk, titulo, descripcion', "'$sei_pk', '$key', '$imgTitulo', '$imgDescripcion'") && !$failure)
						$failure = TRUE;
				}
			}
			elseif(!$failure)
				$failure = TRUE;
		}
		else  // Registro de imagen ya existente
		{
			if(update_img($db, 'seccion_img', $imgPk, $_POST['deleteImg_'.$i], $updir, $_FILES['imgArchivo_'.$i]['name'], $_FILES['imgArchivo_'.$i]['tmp_name'], $dimensions))
			{
				foreach($languages as $key=>$value)
				{
					$imgTitulo = in_array($_POST['imgTitulo_'.$i.'_'.$key], $titText) ? '' : $_POST['imgTitulo_'.$i.'_'.$key];
					$imgDescripcion = in_array($_POST['imgDesc_'.$i.'_'.$key], $descText) ? '' : $_POST['imgDesc_'.$i.'_'.$key];
					if($db->num_rows($db->select('titulo', 'sei_txt', "sei_pk='$imgPk' AND idi_pk='$key'"))>0)  // Ya existe el texto en este idioma
					{
						if(!$db->update('sei_txt', "titulo='$imgTitulo', descripcion='$imgDescripcion'", "sei_pk='$imgPk' AND idi_pk='$key'") && !$failure)
							$failure = TRUE;
					}
					else  // Aún no existe el texto en este idioma
					{
						if(!$db->insert('sei_txt', 'sei_pk, idi_pk, titulo, descripcion', "'$imgPk', '$key', '$imgTitulo', '$imgDescripcion'") && !$failure)
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
	else
		echo "<script type=\"text/javascript\" language=\"javascript\"> alert('La información ha sido actualizada.') </script>";
} ?>

<header>
	<h1>Slider - Cabecera de la página</h1>
</header>
<div class="contentPane">
	<form id="sliderForm" name="sliderForm" action="<?= $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data" method="post">
		<p>Las imágenes están configuradas para ser recortadas a un tamaño de <strong><?= $conf['modules']->sliderImgWidth.' x '.$conf['modules']->sliderImgHeight.'px'; ?></strong>. Si no desea que las imágenes sean recortadas automáticamente puede subir imágenes del tamaño configurado.</p>
		<div class="listSlider"><?
			$result = $db->select('sei_pk, sei_archivo', 'seccion_img', "sec_pk=0 ORDER BY sei_pk");
			$numRows = $db->num_rows($result);
			for($i=1; $i<=$conf['modules']->sliderImgMaxNum; $i++)
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
						$imgSrc = '../uploads/slider/'.$ini.'--'.$thumbnail.$ext;
						$classDel =  '';
					}
					foreach($languages as $key=>$value)
					{
						if($rowLang=$db->fetch_array($db->select('titulo, descripcion', 'sei_txt', "sei_pk='$rowImg[sei_pk]' AND idi_pk='$key'")))
						{
							$imgTitle[$key] = empty($rowLang['titulo']) ? $titText[$key] : $rowLang['titulo'];
							$imgDesc[$key] = empty($rowLang['descripcion']) ? $descText[$key] : $rowLang['descripcion'];
						}
						else
						{
							$imgTitle[$key] = $titText[$key];
							$imgDesc[$key] = $descText[$key];
						}
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
						$imgTitle[$key] = $titText[$key];
						$imgDesc[$key] = $descText[$key];
					}
				} ?>
				<div class="previewSlider">
					<div class="imageContainer">
						<img src="<?= $imgSrc; ?>" alt="" width="120"<?= $imgClass; ?>>
						<div class="noPic"></div>
						<a class="redLink<?= $classDel; ?>"><span class="icon">X</span>Borrar</a>
						<input type="hidden" name="<?= 'deleteImg_'.$i; ?>" value="0" class="deleteImg">
					</div>
					<div class="fLeft paddingR10px"> <?
						foreach($languages as $key=>$value)
						{ ?>
							<input type="text" name="<?= 'imgTitulo_'.$i.'_'.$key; ?>" value="<?= $imgTitle[$key]; ?>" size="40" maxlength="100" class="<?= 'tit'.ucfirst(mb_strtolower($value['abbreviation'])); ?>">
							<div class="clear5"></div> <?
						} ?>
						<input type="file" name="<?= 'imgArchivo_'.$i; ?>" value="">
						<input type="hidden" name="<?= 'imgPk_'.$i; ?>" value="<?= $imgPk; ?>">
					</div> <?
					if($conf['modules']->sliderImgDesc)
					{ ?>
						<div class="fLeft"> <?
							foreach($languages as $key=>$value)
							{ ?>
								<input type="text" name="<?= 'imgDesc_'.$i.'_'.$key; ?>" value="<?= $imgDesc[$key]; ?>" size="40" maxlength="255" class="<?= 'desc'.ucfirst(mb_strtolower($value['abbreviation'])); ?>">
								<div class="clear5"></div> <?
							} ?>
						</div> <?
					} ?>
				</div>
				<div class="file-preview"></div> <?
			} ?>
		</div>
		<div class="clear"></div>
		<div align="center"><input type="submit" value="Guardar"></div>
	</form>
</div>
<script type="text/javascript">
<!--
	$(document).ready(function(){
		// Borrar la imagen
		$('.previewSlider .redLink').click(function(){
			$(this).parents('.imageContainer').find('img').hide();
			$(this).parents('.imageContainer').find('.deleteImg').val('1'); <?
			foreach($languages as $key=>$value)
			{
				$langStr = ucfirst(mb_strtolower($value['abbreviation'])); ?>
				$(this).parents('.previewSlider').find('<?= '.tit'.$langStr; ?>').val('<?= $titText[$key]; ?>');
				$(this).parents('.previewSlider').find('<?= '.desc'.$langStr; ?>').val('<?= $descText[$key]; ?>'); <?
			} ?>
			$(this).parents('.previewSlider').find('span.fcText').html('Seleccionar archivo...');
			$(this).hide();
		});

		// Generar la vista previa de la imagen (No es compatible con ie ni safari)
		$('input[type=file]').change(function(e){
			$(this).parents('.previewSlider').find('.imageContainer img').show();
			newPic = $(this).parents('.previewSlider').find('.imageContainer img');
			if($(this).prop('files')[0])
			{
				var reader = new FileReader();
                	reader.onload = function (e) {
					newPic.attr('src', e.target.result);
				};
               	reader.readAsDataURL($(this).prop('files')[0]);
				$(this).parents('.previewSlider').find('.redLink').show();
			}
		});

		// Limpiar el input
		$('.previewSlider input[type=text]').focus(function(){ <?
			foreach($languages as $key=>$value)
			{ ?>
				if($(this).val()=="<?= $titText[$key]; ?>" || $(this).val()=="<?= $descText[$key]; ?>")
				{
					$(this).val('');
				} <?
			} ?>
		});

		$('.previewSlider input[type=text]').blur(function(){
			if($(this).val()=='')
			{ <?
				foreach($languages as $key=>$value)
				{
					$titClass = 'tit'.ucfirst(mb_strtolower($value['abbreviation']));
					$descClass = 'desc'.ucfirst(mb_strtolower($value['abbreviation'])); ?>
					if($(this).attr('class')=='<?= $titClass; ?>')
						defaultText = "<?= $titText[$key]; ?>";
					else
						if($(this).attr('class')=='<?= $descClass; ?>')
							defaultText = "<?= $descText[$key]; ?>"; <?
				} ?>
				$(this).val(defaultText);
			}
		});
	});
-->
</script>

<? include("footer.php"); ?>