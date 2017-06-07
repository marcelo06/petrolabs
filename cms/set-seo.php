<?php
$pageTitle = "SEO global";
$menuActive = 5; ?>
<? include("header.php"); ?> <?
$rowLangs = $db->fetch_all($db->select('*', 'idioma', 'idi_estado="1" ORDER BY idi_pk'));
if($_SERVER['REQUEST_METHOD']=='POST' && isset($_POST['seoTitle'][1]))
{
	$error = FALSE;
	foreach($rowLangs as $rowLang)
	{
		$values = '';
		if($_POST['seoTitle'][$rowLang['idi_pk']]!=$rowLang['seo_title'])
		{
			$values .= "seo_title='".$_POST['seoTitle'][$rowLang['idi_pk']]."', ";
		}
		if($_POST['seoKeywords'][$rowLang['idi_pk']]!=$rowLang['seo_keywords'])
		{
			$values .= "seo_keywords='".$_POST['seoKeywords'][$rowLang['idi_pk']]."', ";
		}
		if($_POST['seoDescription'][$rowLang['idi_pk']]!=$rowLang['seo_description'])
		{
			$values .= "seo_description='".$_POST['seoDescription'][$rowLang['idi_pk']]."', ";
		}
		if($values!='')
		{
			$values = mb_substr($values, 0, -2);  // Quito la última coma.
			if(!$db->update('idioma', $values, 'idi_pk='.$rowLang['idi_pk']))
			{
				$error = TRUE;  // Sólo registro si hay errores.
			}
		}
	}
	if(!$error)
	{
		echo '<script type="text/javascript"> alert(\'La información ha sido actualizada.\'); </script>';
		$rowLangs = $db->fetch_all($db->select('*', 'idioma', 'idi_estado="1" ORDER BY idi_pk'));  // Vuelvo y cargo la info. de los idiomas.
	}
	else
	{
		echo '<script type="text/javascript"> alert(\'No fue posible actualizar toda la información.\'); </script>';
	}
}
?>
<header>
	<h1>SEO (Global)</h1>
</header>
<div class="contentPane">
	<form id="seoForm" name="seoForm" action="<?= $_SERVER['PHP_SELF']; ?>" method="post">
		<table class="formTable"> <?
			foreach($rowLangs as $rowLang)
			{ ?>
				<tr>
					<th>Título<? if(count($rowLangs)>1) { ?> (<?= $rowLang['idi_locale']; ?>)<? } ?>:</th>
					<td><input type="text" id="seoTitle[<?= $rowLang['idi_pk']; ?>]" name="seoTitle[<?= $rowLang['idi_pk']; ?>]" size="45" value="<?= $rowLang['seo_title']; ?>"></td>
				</tr> <?
			}
			foreach($rowLangs as $rowLang)
			{ ?>
				<tr>
					<th>Keywords<? if(count($rowLangs)>1) { ?> (<?= $rowLang['idi_locale']; ?>)<? } ?>:</th>
					<td><input type="text" id="seoKeywords[<?= $rowLang['idi_pk']; ?>]" name="seoKeywords[<?= $rowLang['idi_pk']; ?>]" size="45" value="<?= $rowLang['seo_keywords']; ?>"></td>
				</tr> <?
			}
			foreach($rowLangs as $rowLang)
			{ ?>
				<tr>
					<th>Description<? if(count($rowLangs)>1) { ?> (<?= $rowLang['idi_locale']; ?>)<? } ?>:</th>
					<td><textarea id="seoDescription[<?= $rowLang['idi_pk']; ?>]" name="seoDescription[<?= $rowLang['idi_pk']; ?>]" cols="60" rows="2"><?= $rowLang['seo_description']; ?></textarea></td>
				</tr> <?
			} ?>
			<tr>
				<th>&nbsp;</th>
				<td>
					<input type="submit" value="Aceptar">
				</td>
			</tr>
		</table>
	</form>
</div>
<? include("footer.php"); ?>