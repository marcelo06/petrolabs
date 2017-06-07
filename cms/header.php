<?
include_once("lib/functions.php");
$conf = config();
$db = database();
if(!$db->selected_db)  // No se ha configurado la base de datos
{
	header("Location: set-database.php");
	exit();
}
if(!isset($_SESSION['login']))
{
	header("Location: login.php");
	exit();
}
$modules = array();
$result = $db->select('*', 'modulo_noticias ORDER BY nombre');
for($i=0; $row=$db->fetch_array($result); $i++)
{
	$modules[$i] = $row;
}
$numModules = count($modules); ?>
<!doctype html>
<html>
	<head>
		<meta charset="utf-8">
		<title><?= $pageTitle; ?> - Easy website</title>
		<link href='http://fonts.googleapis.com/css?family=Cuprum' rel='stylesheet' type='text/css'>
		<link rel="stylesheet" type="text/css" href="css/validationEngine.jquery.css">
		<link rel="stylesheet" type="text/css" href="css/ui-theme/jquery-ui-1.10.2.custom.css">
		<link rel="stylesheet" type="text/css" href="css/style.css">
		<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
		<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.2/jquery-ui.min.js"></script>
		<script type="text/javascript" src="js/functions.js"></script>
		<script type="text/javascript" src="js/tinymce/tinymce.min.js"></script>
		<script type="text/javascript">
			tinymce.init({
				selector: ".editorTextArea",
				language : 'es',
				content_css: "../css/editor.css",
				plugins:["autolink charmap code fullscreen hr link lists media nonbreaking preview table autoresize save paste image"],
				paste_auto_cleanup_on_paste : true,
				browser_spellcheck : true,
				element_format : "html",
				menubar: "edit insert view format table tools save",
				statusbar : true,
				toolbar: "save undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | image link  media | fullscreen |",
				width : "100%",/*
				forced_root_block : false,
				force_br_newlines : true,
				force_p_newlines : false,*/
				autoresize_max_height: 350,
				convert_urls: false
			});
		</script>
	</head>
	<body>
		<div id="mainWrapper">
			<header id="pageHeader">
				<div id="logo"><a href="index.php"><img src="images/easy-website.png" alt=""></a></div>
				<div class="fRight">
					<div id="userLinks">
						<span><?= $_SESSION['user']; ?></span>
						<a href="my-account.php">Mi cuenta</a>
						<a href="login.php?logout=1">Cerrar sesión</a>
					</div>
					<div class="clear"></div>
					<nav>
						<ul>
							<li>
								<a href="index.php" class="lLeft <? if(isset($menuActive) && $menuActive==1) echo 'active' ?>">Inicio</a>
								<div class="sepMenu"></div>
							</li>
							<li>
								<a href="pages.php" <? if(isset($menuActive) && $menuActive==2) echo 'class="active"' ?>>Páginas</a>
								<div class="sepMenu"></div>
							</li>
							<li>
								<a href="#" <? if(isset($menuActive) && $menuActive==3) echo 'class="active"' ?>>Módulos <span class="icon">/</span></a>
								<div class="subNav modules"> <?
									$i = 1;
									foreach($modules as $mod)
									{ ?>
										<a href="news.php?id=<?= $mod['pk']; ?>"><?= $mod['nombre']; ?></a> <?
										if($i<$numModules)
										{ ?>
											<div class="sepSubNav"></div> <?
										}
										$i++;
									} ?>
									<div class="sepSubNav"></div>
									<a href="concurso.php">Concurso Terpel</a>
									<div class="sepSubNav"></div>
									<a href="concurso-cencosud.php">Concurso Cencosud</a>
									<div class="sepSubNav"></div>
									<a href="eds.php">EDS</a>
									<div class="sepSubNav"></div>
									<a href="eds_users.php">Gestión de Isleros</a>
									<div class="sepSubNav"></div>
									<a href="pedidos.php">Pedidos</a>
								</div>
								<div class="sepMenu"></div>
							</li> <?
							if(permit($db, $_SESSION['per_pk'], '6, 7'))
							{ ?>
								<li>
									<a href="#" <? if(isset($menuActive) && $menuActive==4) echo 'class="active"' ?>>Usuarios <span class="icon">/</span></a>
									<div class="subNav users"> <?
										$flag = FALSE;
										if(permit($db, $_SESSION['per_pk'], '6'))
										{ ?>
											<a href="user-profiles.php">Perfiles de usuario</a> <?
											$flag = TRUE;
										}
										if(permit($db, $_SESSION['per_pk'], '7'))
										{
											if($flag)
											{ ?>
												<div class="sepSubNav"></div> <?
											} ?>
											<a href="user-accounts.php">Cuentas de usuario</a> <?
										} ?>
									</div>

									<div class="sepMenu"></div>
								</li> <?
							}
							if(permit($db, $_SESSION['per_pk'], '1, 2, 3, 4, 5') || $conf['modules']->images)
							{ ?>
								<li>
									<a href="#" class="lRight  <? if(isset($menuActive) && $menuActive==5) echo 'active' ?>">Configuración <span class="icon">/</span></a>
									<div class="subNav config"> <?
										$flag = FALSE;
										if(permit($db, $_SESSION['per_pk'], '1'))
										{ ?>
											<a href="set-database.php">Base de datos</a> <?
											$flag = TRUE;
										}
										if(permit($db, $_SESSION['per_pk'], '2'))
										{
											if($flag)
											{ ?>
												<div class="sepSubNav"></div> <?
											}
											else
												$flag = TRUE; ?>
											<a href="general-settings.php">Configuración general</a> <?
										}
										if(permit($db, $_SESSION['per_pk'], '3'))
										{
											if($flag)
											{ ?>
												<div class="sepSubNav"></div> <?
											}
											else
												$flag = TRUE; ?>
											<a href="set-modules.php">Módulos activos</a> <?
										}
										if(permit($db, $_SESSION['per_pk'], '4'))
										{
											if($flag)
											{ ?>
												<div class="sepSubNav"></div> <?
											}
											else
												$flag = TRUE; ?>
											<a href="page-settings.php">Configuración de la página</a> <?
										}
										if(permit($db, $_SESSION['per_pk'], '5'))
										{
											if($flag)
											{ ?>
												<div class="sepSubNav"></div> <?
											}
											else
												$flag = TRUE; ?>
											<a href="slider.php">Slider (Cabecera)</a> <?
										}
										if($flag)
										{ ?>
											<div class="sepSubNav"></div> <?
										} ?>
										<a href="set-seo.php">SEO (Global)</a> <?
										if($conf['modules']->images)
										{ ?>
											<div class="sepSubNav"></div>
											<a href="image-manager.php">Gestión de imágenes</a> <?
										} ?>
									</div>
								</li> <?
							} ?>
						</ul>
						<div class="clear"></div>
					</nav>
				</div>
				<div class="clear"></div>
			</header>
			<section id="mainContent">