<?
$pageTitle = "Inicio";
$menuActive = 1; ?>
<? include("header.php"); ?> <?
$numPages = $db->num_rows($db->select('sec_pk', 'seccion'));
?>
<h2>¡Hola <?= $_SESSION['user']; ?>!</h2>
<div class="clear5"></div>
<a href="my-account.php">Mi cuenta</a> |
<a href="../" target="_blank">Ver <?= $conf['settings']->domain; ?></a>
<div class="clear10"></div>
<div class="clear10"></div>
<div class="w450 fLeft h85">
	<header>
		<h1>Páginas</h1>
	</header>
	<div class="contentPane">
		Actualmente puedes administrar los contenidos de <strong><?= $numPages; ?></strong> páginas.
		<div class="clear10"></div>
		<a href="pages.php">&raquo; Administrar páginas</a>
	</div>
</div>
<div class="w450 fRight h85">
	<header>
		<h1>Módulos</h1>
	</header>
	<div class="contentPane"> <?
		$result = $db->select('*', 'modulo_noticias ORDER BY nombre');
		$numModules = $db->num_rows($result);
		if($numModules>0)
		{ ?>
			Tus módulos son:
			<div class="clear10"></div> <?
			for($i=0; $i<$numModules; $i++)
			{
				$row = $db->fetch_array($result); ?>
				<a href="news.php?id=<?= $row['pk']; ?>"><?= $row['nombre']; ?></a> <?
				if($i<$numModules-1)
					echo '| ';
			}
		}
		else
			echo 'Actualmente no tienes módulos activos.'; ?>
	</div>
</div>
<br>
<? include ("footer.php"); ?>