<?php
/* --- Header --- */
define('INICIO','HOME');
define('TESTIMONIOS','Testimonials');
define('QUIENES_SOMOS','ABOUT US');
define('PRODUCTOS','Products');
define('PUNTOS_VENTA','SALES POINTS');
define('PROTECCION_AMBIENTAL','ENVIRONMENTAL PROTECTION');
define('CONTACTENOS','CONTACT US');
define('NOTICIAS_EVENTOS','Noti-Petrolabs');
define('TOP_CONTACT','Carrera 13 # 7-50<br>
	Phone: +57 (6) 758 6358 - 758 6315 Mobile: +57 318 827 5233<br>
	Circasia - Quindío - Colombia - South America');
define('VER_PRODUCTOS','view all products');
define('TXT_USELO','Try it');

/* --- Footer --- */
define('BUSCA_ESTACIONES','WANT TO BUY OUR ADDITIVES?');
define('ENCUENTRELAS_AQUI','FIND THEM HERE');
define('TXT_CREDITS','&copy; Copyright '.date('Y').' Aditivos Petrolabs - All rights reserved');
define('LNK_HAGGEN','Design and development by Haggen IT');
define('CALENDAR_OF','Calendar of');
define('FULL_LIST','Full list');

/* --- Contact --- */
define('CONTACT_NAME','Full name');
define('CONTACT_PHONE','Phone');
define('CONTACT_CITY','City');
define('CONTACT_MESSAGE','Message');
define('CONTACT_SEND','Send');
define('CONTACT_SUCCESS','Your message has been sent. Thank you for contacting.');
define('CONTACT_FAILURE','Your message could not be sent. Please try again.');

/* Globales */
define('ULTIMAS_NOTICIAS','Latest Noti-Petrolabs');
define('VER_MAS','View more');
define('CONTACTO_MAS_INFO','Contact us for more information');
define('COMENTARIOS','Comments');
define('DEMO_LINK','View products demo');
define('CATALOGO_DESOS','Wish list');

/* --- Páginas --- */
switch(basename($_SERVER['PHP_SELF']))
{
	case 'contactenos.php':
		define('INFO_CONTACTO','Contact information');
		define('UBICACION','Location');
		break;
	case 'inicio.php':
		define('VER_NOTICIAS','VIEW ALL Noti-Petrolabs');
		break;
	case 'noticias-y-eventos-single.php':
		define('IMAGENES','Images');
		define('ADJUNTOS','Attachments');
		break;
	case 'productos-single.php':
		define('USO','Use in');
		define('MAS_INFO','Want more information about this product?');
		define('CONTACTO_PRODUCTO','Contact us');
		define('DESCRIPCION','Detailed Description');
		define('DEMO','Demonstration');
		define('FICHA','Datasheet');
		define('BENEFICIOS','Benefits');
		define('USOS','Applications');
		define('SULFURO','Sulfide');
		define('INFO_TECNICA','Technical Information');
		define('ADJUNTOS','Related Files');
		break;
	case 'productos.php':
		define('NUESTROS_PRODUCTOS','Our Products');
		define('TXT_PRODUCTOS','<strong>Petrolabs de Colombia S.A.</strong> wants you to know its range of additives for all types of engines and for all kinds of uses, from cars to industrial application.');
		break;
	case 'proteccion-ambiental.php':
		define('GALERIA','Image Gallery');
		define('COMPARTIR','Share');
		break;
	case 'testimonios-single.php':
		define('IMAGENES','Images');
		define('ADJUNTOS','Attachments');
		break;
	case 'testimonios.php':
		define('VER_TESTIMONIO','Read full testimonial');
		break;
	case 'catalogo-de-deseos.php':
		define('TXT_CATALOGODESEOS','Si es asesor de una de las Estaciones de Servicio aditivadas con nuestros productos, podrá acumular recipientes y canjearlos por regalos para usted o su familia. A continuación encontrará nuestro catálogo de productos:');		
}

if(!defined('PAGER_FROM')) define('PAGER_FROM','From');
if(!defined('PAGER_TO')) define('PAGER_TO','to');
if(!defined('PAGER_OF')) define('PAGER_OF','of');
if(!defined('PAGER_RECORDS')) define('PAGER_RECORDS','records');
if(!defined('PAGER_PREVIOUS')) define('PAGER_PREVIOUS','Previous');
if(!defined('PAGER_NEXT')) define('PAGER_NEXT','Next');
?>