<?php
class modules
{
	var $content;
	var $pages;
	var $subpages;
	var $images;
	var $sliderImgMaxNum;
	var $sliderImgWidth;
	var $sliderImgHeight;
	var $sliderImgDesc;
	var $spanish;
	var $english;
	var $howDisplayLang;

	function __construct ()
	{
		$this->content = TRUE;  // Administración de contenidos
		$this->pages = TRUE;  // Administración de páginas
		$this->subpages = TRUE;  // Administración de subpáginas
		$this->images = TRUE;  // Gestor de imágenes
		$this->sliderImgMaxNum = 8;  // Límite de imágenes del slider
		$this->sliderImgWidth = 960;  // Anchura de cada imagen del slider
		$this->sliderImgHeight = 300;  // Altura de cada imagen del slider
		$this->sliderImgDesc = TRUE;  // Descripción de cada imagen del slider
		$this->howDisplayLang = 'abbreviation';  // Cómo mostrar el idioma: name ó abbreviation ó image
	}
}
?>