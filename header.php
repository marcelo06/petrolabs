<?
include_once("lib/functions.php");
$conf = config();
$db = database();
if(!$db->selected_db)  // No se ha configurado la base de datos
{
	header("Location: cms/set-database.php");
	exit();
}
include_once("lang/".language($db).".php");

// Obtener SEO: $seo['title'], $seo['keywords'] y $seo['description'].
if(isset($not_pk))  // Módulo de noticias o similar
	$seo = $db->fetch_array($db->select('ntx_seo_title AS title, ntx_seo_keywords AS keywords, ntx_seo_description AS description', 'noticia_txt', "not_pk='$not_pk' AND idi_pk='$_SESSION[lang]'"));
elseif(isset($sec_pk))  // Páginas
	$seo = $db->fetch_array($db->select('stx_seo_title AS title, stx_seo_keywords AS keywords, stx_seo_description AS description', 'seccion_txt', "sec_pk='$sec_pk' AND idi_pk='$_SESSION[lang]'"));
else
	$seo = array('title'=>'', 'keywords'=>'', 'description'=>'');
if(empty($seo['title']) || empty($seo['keywords']) || empty($seo['description']))
{
	$globalSeo = $db->fetch_array($db->select('seo_title AS title, seo_keywords AS keywords, seo_description AS description', 'idioma', "idi_pk='$_SESSION[lang]'"));
	if(empty($seo['title']))
		$seo['title'] = $globalSeo['title'];
	if(empty($seo['keywords']))
		$seo['keywords'] = $globalSeo['keywords'];
	if(empty($seo['description']))
		$seo['description'] = $globalSeo['description'];
}

// Obtener la información del slider
if(isset($sec_pk) && $conf['modules']->sliderImgMaxNum>0)  // Es una página y el límite de imágenes del slider es uno o más
{
	$pageRow = $db->fetch_array($db->select('sec_slider', 'seccion', "sec_pk='$sec_pk'"));
	if($pageRow['sec_slider']=='1')
	{
		$showSlider = TRUE;
		$sliderCondi = "sec_pk=0 AND sei_archivo!='' AND si.sei_pk=sit.sei_pk AND idi_pk='$_SESSION[lang]' ORDER BY si.sei_pk";
		$sliderResult = $db->select('sei_archivo AS filename, titulo AS title, descripcion AS description', 'seccion_img AS si, sei_txt AS sit', $sliderCondi);
		$sliderNumImg = $db->num_rows($sliderResult);
		if($sliderNumImg>0)
		{
			for($i=0; $i<$sliderNumImg; $i++)
			{
				$sliderImgInfo[$i] = $db->fetch_array($sliderResult);
				$pos = mb_strrpos($sliderImgInfo[$i]['filename'], '.');
				$ini = mb_substr($sliderImgInfo[$i]['filename'], 0, $pos);
				$ext = mb_substr($sliderImgInfo[$i]['filename'], $pos);
				$sliderImgFile[$i] = $ini.'--'.$conf['modules']->sliderImgWidth.'x'.$conf['modules']->sliderImgHeight.$ext;
			}
		}
	}
	else
		$showSlider = FALSE;
}
else
	$showSlider = FALSE;

// Array de idiomas
$result = $db->select('idi_pk, idi_txt, idi_locale', 'idioma', "idi_estado='1' ORDER BY idi_pk");
while($row = $db->fetch_array($result))
{
	$id = $row['idi_pk'];
	$languages[$id] = array('name'=>$row['idi_txt'], 'abbreviation'=>$row['idi_locale']);
}
$numLanguages = count($languages);

// Configuración de la página
$columns = 'nombre_sitio AS siteName, correo_contacto AS contactEmail, twitter AS twitterUser, facebook AS facebookPage, analytics AS googleAnalytics';
$pageSettings = $db->fetch_array($db->select($columns, 'configuracion', '1=1 LIMIT 1')); ?>

<!doctype html>
<html>
	<head>
		<meta charset="utf-8">
		<title><? echo $pageTitle; if(!empty($seo['title'])) echo ' | '.$seo['title']; ?></title>
		<meta name="keywords" content="<?= $seo['keywords'];?>">
		<meta name="description" content="<?= $seo['description'];?>">
		<meta property="fb:admins" content="638687491">
		<link rel="stylesheet" type="text/css" href="css/validationEngine.jquery.css">
		<link href="//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css" rel="stylesheet">
		<link href='http://fonts.googleapis.com/css?family=Lato:400,700' rel='stylesheet' type='text/css'>
		<link href='http://fonts.googleapis.com/css?family=Roboto+Condensed:400,300' rel='stylesheet' type='text/css'>
		<link rel="stylesheet" type="text/css" href="http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css">
		<link rel="stylesheet" type="text/css" href="js/jquery.bxslider/jquery.bxslider.css">
		<link rel="stylesheet" type="text/css" href="js/fancybox/jquery.fancybox.css">
		<link rel="stylesheet" type="text/css" href="css/style.css?v=6">
		<link rel="stylesheet" type="text/css" href="css/loader.css">
		<link rel="icon" href="http://www.aditivospetrolabs.com/images/petrolabs-icon.png">
		<? /* Etiquetas responsive */ ?>
		<meta name="viewport" content="width=device-width">
		<meta name="format-detection" content="telephone=no">
		<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
		<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script>
		<script type="text/javascript" src="js/jquery.ui.datepicker-es.js"></script>
		<script type="text/javascript" src="js/jquery.bxslider/jquery.bxslider.min.js"></script>
		<script type="text/javascript" src="js/fancybox/jquery.fancybox.pack.js"></script>
		<script type="text/javascript">
			// html5 fix
			document.createElement("nav");
			document.createElement("header");
			document.createElement("footer");
			document.createElement("section");
			document.createElement("article");
			document.createElement("aside");
			document.createElement("hgroup");

			$(document).ready(function(){
				currentLang = '<?= language($db) ?>';
				if(currentLang=='es')
					$('#ES').addClass('currentLang');
				else
					$('#EN').addClass('currentLang');

				$('.bxslider').bxSlider({
					'mode':'fade',
					'slideMargin':0,
					'auto':true,
					'pause':7000,
					'responsive':true,
				});

				$(".fancybox").fancybox();

				$('.menu_button').click(function(){
					if($(this).hasClass('active'))
					{
						$(this).removeClass('active');
						$('.phoneMenu').slideUp(300);
					}
					else
					{
						$(this).addClass('active');
						$('.phoneMenu').slideDown(300);
					}
				})
			});
		</script>
	</head>
	<body>
		<div class="menu_button">
			<div class="line first"></div>
			<div class="line"></div>
			<div class="line last"></div>
		</div><?
		/* Código Google Analytics */
		echo $pageSettings['googleAnalytics'];?>
		<div id="cPageHeader">
		<header id="pageHeader">
			<div id="logo">
				<a href="index.php"><img src="images/Aditivos-Petrolabs.png" alt="Aditivos Petrolabs"></a>
			</div>
			<div id="busquedaGoogle">
				<script>
				  (function() {
					var cx = '016888350044306203037:7ggxeu3r2w0';
					var gcse = document.createElement('script');
					gcse.type = 'text/javascript';
					gcse.async = true;
					gcse.src = (document.location.protocol == 'https:' ? 'https:' : 'http:') +
						'//www.google.com/cse/cse.js?cx=' + cx;
					var s = document.getElementsByTagName('script')[0];
					s.parentNode.insertBefore(gcse, s);
				  })();
				</script>
				<gcse:search></gcse:search>
			</div>
			<div id="topContact">
				<?= TOP_CONTACT ?>
				<div class="clear"></div>
				<a href="mailto:<?= $pageSettings['contactEmail'];?>"><?= $pageSettings['contactEmail'];?></a>
				<div class="clear"></div>
				<div class="topSocialIcons">
					<a href="<?= $pageSettings['facebookPage'];?> " target="_blank"><img src="images/facebook.png" alt=""></a>
					<a href="http://www.twitter.com/<?= $pageSettings['twitterUser']?>" target="_blank"><img src="images/twitter.png" alt=""></a>
				</div>
				<div class="clear"></div>
			</div>
			<nav id="pageNav">
				<ul>
					<li <? if($currentPage==1) echo'class="activeMenu"'?>><a href="index.php"><?= INICIO ?></a></li>
					<li <? if($currentPage==2) echo'class="activeMenu"'?>><a href="testimonios.php"><?= TESTIMONIOS ?></a></li>
					<li <? if($currentPage==7) echo'class="activeMenu"'?>><a href="quienes-somos.php"><?= QUIENES_SOMOS ?></a></li>
					<li <? if($currentPage==4) echo'class="activeMenu"'?>><a href="productos.php"><?= PRODUCTOS ?></a></li>
					<li <? if($currentPage==3) echo'class="activeMenu"'?>><a href="puntos-de-venta.php"><?= PUNTOS_VENTA ?></a></li>
					<li <? if($currentPage==6) echo'class="activeMenu"'?>><a href="proteccion-ambiental.php"><?= PROTECCION_AMBIENTAL ?> <img src="images/reciclable.png" style="vertical-align: middle;"></a></li>
					<li class="noBorder <? if($currentPage==5) echo'activeMenu'?>"><a href="contactenos.php"><?= CONTACTENOS ?></a></li>
				</ul>
				<div id="langSelector"><?
					/* Idiomas */
					if($numLanguages>1)
					{
						$vars = '';
						foreach($_GET as $key=>$value)
						{
							if($key!='lang')
								$vars .= $key.'='.$value.'&amp;';
						}
						foreach($languages as $key=>$value)
						{
							if($conf['modules']->howDisplayLang=='name')
								$langStr = $value['name'];
							elseif($conf['modules']->howDisplayLang=='abbreviation')
								$langStr = $value['abbreviation'];
							else
								$langStr = "<img src=\"images/".mb_strtolower($value['abbreviation'])."-flag.png\" alt=\"\">"; ?>
							<a href="<?= $_SERVER['PHP_SELF'].'?'.$vars.'lang='.$key; ?>" title="<?= $value['name']; ?>" id="<?= $langStr; ?>"><?= $langStr; ?></a> <?
						}
					}?>
				</div>
				<div class="clear"></div>
			</nav>
			<div class="phoneMenu">
				<ul>
					<li><a href="index.php"><?= INICIO ?></a></li>
					<li><a href="testimonios.php"><?= TESTIMONIOS ?></a></li>
					<li><a href="quienes-somos.php"><?= QUIENES_SOMOS ?></a></li>
					<li><a href="productos.php"><?= PRODUCTOS ?></a></li>
					<li><a href="puntos-de-venta.php"><?= PUNTOS_VENTA ?></a></li>
					<li><a href="proteccion-ambiental.php"><?= PROTECCION_AMBIENTAL ?> <img src="images/reciclable.png" style="vertical-align: middle;"></a></li>
					<li><a href="contactenos.php"><?= CONTACTENOS ?></a></li>
				</ul>
			</div>
		</header>
		</div><?
		/* Slider */
		if($showSlider)
		{ ?>
			<div id="sliderArea">
				<a href="productos.php" id="productsBtn"><?= VER_PRODUCTOS ?></a>
				<ul class="bxslider"><?
					for($i=0; $i<$sliderNumImg; $i++)
					{ ?>
						<li>
							<div class="slidehome" style="background-image:url(<?= 'uploads/slider/'.$sliderImgFile[$i]; ?>);"><?
								if(strpos($sliderImgInfo[$i]['title'], 'concurso-terpel')!==FALSE)
								{ ?>
									<a href="concurso-terpel.php"></a><?
								}
								elseif(strpos($sliderImgInfo[$i]['title'], 'concurso-cencosud')!==FALSE)
								{ ?>
									<a href="concurso-cencosud.php"></a><?
								}?>
								<div class="sTitle">
									<div class="sTitleContent">
										<h2><?= $sliderImgInfo[$i]['title']; ?></h2>
										<h3><?= $sliderImgInfo[$i]['description']; ?></h3>
									</div>
								</div>
							</div>
						</li><?
					} ?>
				</ul>
			</div><?
		} ?>
