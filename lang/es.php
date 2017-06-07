<?php
/* --- Header --- */
define('INICIO','INICIO');
define('TESTIMONIOS','Testimonios');
define('QUIENES_SOMOS','QUIÉNES SOMOS');
define('PRODUCTOS','Productos');
define('PUNTOS_VENTA','PUNTOS DE VENTA');
define('PROTECCION_AMBIENTAL','PROTECCIÓN AMBIENTAL');
define('CONTACTENOS','CONTÁCTENOS');
define('NOTICIAS_EVENTOS','Noti-Petrolabs');
define('TOP_CONTACT','Carrera 13 # 7-50<br>
	Teléfonos: +57 (6) 758 6358 - 758 6315 Celular: +57 318 827 5233<br>
	Circasia - Quindío - Colombia - Suramérica');
define('VER_PRODUCTOS','ver todos los productos');
define('TXT_USELO','Úselo');

/* --- Footer --- */
define('BUSCA_ESTACIONES','¿DESEA COMPRAR NUESTROS ADITIVOS?');
define('ENCUENTRELAS_AQUI','ENCUÉNTRELOS AQUÍ');
define('TXT_CREDITS','&copy; Copyright '.date('Y').' Aditivos Petrolabs - Todos los derechos reservados');
define('LNK_HAGGEN','Dise&ntilde;o y desarrollo por Haggen IT');
define('CALENDAR_OF','Calendario de');
define('FULL_LIST','Listado completo');

/* --- Contact --- */
define('CONTACT_NAME','Nombre');
define('CONTACT_PHONE','Teléfono');
define('CONTACT_CITY','Ciudad');
define('CONTACT_MESSAGE','Mensaje');
define('CONTACT_SEND','Enviar');
define('CONTACT_SUCCESS','Su mensaje ha sido enviado. Gracias por comunicarse.');
define('CONTACT_FAILURE','Su mensaje no ha podido ser enviado. Por favor, intente nuevamente.');

/* Globales */
define('ULTIMAS_NOTICIAS','Noti-Petrolabs más recientes');
define('VER_MAS','Ver más');
define('CONTACTO_MAS_INFO','Contáctenos para más información');
define('COMENTARIOS','Comentarios');
define('DEMO_LINK','Ver demostración de productos');
define('CATALOGO_DESOS','Catálogo de deseos');

/* --- Páginas --- */
switch(basename($_SERVER['PHP_SELF']))
{
	case 'contactenos.php':
		define('INFO_CONTACTO','Información de contacto');
		define('UBICACION','Ubicación');
		break;
	case 'inicio.php':
		define('VER_NOTICIAS','VER Noti-Petrolabs');
		break;
	case 'noticias-y-eventos-single.php':
		define('IMAGENES','Imágenes');
		define('ADJUNTOS','Archivos Adjuntos');
		break;
	case 'productos-single.php':
		define('USO','Uso en');
		define('MAS_INFO','¿Desea más información de este producto?');
		define('CONTACTO_PRODUCTO','Contáctenos');
		define('DESCRIPCION','Descripción Detallada');
		define('DEMO','Demostración');
		define('FICHA','Ficha Técnica');
		define('BENEFICIOS','Beneficios');
		define('USOS','Usos');
		define('SULFURO','Sulfuro');
		define('INFO_TECNICA','Información Técnica');
		define('ADJUNTOS','Archivos Relacionados');
		break;
	case 'productos.php':
		define('NUESTROS_PRODUCTOS','Nuestros Productos');
		define('TXT_PRODUCTOS','<strong>Petrolabs de Colombia S.A.</strong> desea que usted conozca su amplia gama de aditivos para todo tipo de motores y para todo tipo de usos, desde automóviles hasta su aplicación industrial.');
		break;
	case 'proteccion-ambiental.php':
		define('GALERIA','Galería de Imágenes');
		define('COMPARTIR','Compartir');
		break;
	case 'testimonios-single.php':
		define('IMAGENES','Imágenes');
		define('ADJUNTOS','Archivos Adjuntos');
		break;
	case 'testimonios.php':
		define('VER_TESTIMONIO','Ver testimonio completo');
		break;
	case 'catalogo-de-deseos.php':
		define('TXT_CATALOGODESEOS','Si es asesor de una de las Estaciones de Servicio aditivadas con nuestros productos, podrá acumular recipientes y canjearlos por regalos para usted o su familia. A continuación encontrará nuestro catálogo de productos:');
}

if(!defined('PAGER_FROM')) define('PAGER_FROM','Viendo del');
if(!defined('PAGER_TO')) define('PAGER_TO','al');
if(!defined('PAGER_OF')) define('PAGER_OF','de');
if(!defined('PAGER_RECORDS')) define('PAGER_RECORDS','registros');
if(!defined('PAGER_PREVIOUS')) define('PAGER_PREVIOUS','Anterior');
if(!defined('PAGER_NEXT')) define('PAGER_NEXT','Siguiente');
?>