<?php
if($_SERVER['REQUEST_METHOD']=='POST')
{
	$msg = '';
	$changed = FALSE;
	$file = file_get_contents("lib/settings.php");
	if($_POST['pager'] != $_POST['oldPager'])
	{
		$file = str_replace("this->pager = $_POST[oldPager]", "this->pager = $_POST[pager]", $file);
		if(!$changed)
			$changed = TRUE;
	}
	if($_POST['domain'] != $_POST['oldDomain'])
	{
		$file = str_replace("this->domain = \"$_POST[oldDomain]\"", "this->domain = \"$_POST[domain]\"", $file);
		if(!$changed)
			$changed = TRUE;
	}
	if($_POST['sendersEmail'] != $_POST['oldSendersEmail'])
	{
		$file = str_replace("this->sendersEmail = \"$_POST[oldSendersEmail]\"", "this->sendersEmail = \"$_POST[sendersEmail]\"", $file);
		if(!$changed)
			$changed = TRUE;
	}
	if($_POST['pw'] != $_POST['oldPw'])
	{
		$file = str_replace("this->pw = \"$_POST[oldPw]\"", "this->pw = \"$_POST[pw]\"", $file);
		if(!$changed)
			$changed = TRUE;
	}
	if($changed)
	{
		file_put_contents("lib/settings.php", $file);
		file_put_contents("lib/settings.txt", "$_POST[pager] $_POST[domain] $_POST[sendersEmail] $_POST[pw]");
		$msg = 'Actualización realizada.';
	}
}
$pageTitle = "Configuración general";
$menuActive = 5; ?>
<? include("header.php"); ?> <?
if(!permit($db, $_SESSION['per_pk'], '2'))
{
	echo "<script type=\"text/javascript\"> alert('Acceso denegado.'); window.history.back(); </script>";
	exit();
}
if($_SERVER['REQUEST_METHOD']=='POST')
{
	if($msg=='')
		echo "<script type=\"text/javascript\"> alert('No hubo cambios.'); </script>";
	else
		echo "<script type=\"text/javascript\"> alert('".$msg."'); </script>";
}
$f = file_get_contents("lib/settings.txt");
$settings = explode(' ', $f); ?>
<header>
	<h1>Configuración general</h1>
</header>
<div class="contentPane">
	<form id="genSetForm" name="genSetForm" action="<?= $_SERVER['PHP_SELF']; ?>" method="post">
		<input type="hidden" name="oldPager" value="<?= $settings[0]; ?>" />
		<input type="hidden" name="oldDomain" value="<?= $settings[1]; ?>" />
		<input type="hidden" name="oldSendersEmail" value="<?= $settings[2]; ?>" />
		<input type="hidden" name="oldPw" value="<?= $settings[3]; ?>" />
		<table class="formTable">
			<tr>
				<th>Paginador del CMS:</th>
				<td><input type="text" id="pager" name="pager" size="30" class="validate[required,custom[integer],min[1]]" value="<?= $settings[0]; ?>"></td>
			</tr>
			<tr>
				<th>Dominio:</th>
				<td>
					<input type="text" id="domain" name="domain" size="45" class="validate[required]" value="<?= $settings[1]; ?>">
					&nbsp;(No incluir http:// ni www. Ej:nombredominio.com)
				</td>
			</tr>
			<tr>
				<th>Correo de envío:</th>
				<td><input type="text" id="sendersEmail" name="sendersEmail" size="45" class="validate[required,custom[email]]" value="<?= $settings[2]; ?>"></td>
			</tr>
			<tr>
				<th>Contraseña:</th>
				<td><input type="text" id="pw" name="pw" size="45" class="validate[required]" value="<?= $settings[3]; ?>"></td>
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
<script type="text/javascript" src="js/jquery.validationEngine-es.js"></script>
<script type="text/javascript" src="js/jquery.validationEngine.js"></script>
<script type="text/javascript">
	$(document).ready(function(){
		$("#genSetForm").validationEngine();
	});
</script>
<? include("footer.php"); ?>