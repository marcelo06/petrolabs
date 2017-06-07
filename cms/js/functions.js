// Hoja de estilo principal easywebsite/cms
$(document).ready(function(){
	// Intercalar colores de las filas del listado
	$('.dataTable tr:odd td').addClass('whiteTd');
	

	// Cambio de input file
	$('input[type=file]').each(function(){
		$(this).before('<span class="fileContainer"><span class="fcWrapper"><span class="fcText">Seleccionar archivo...</span></span></span>');
		$(this).css({visibility:'hidden'}).wrap('<div class="hiddenInput"></div>');
	});
	
	$('.fileContainer').click(function(){
		tmpIndex = $('.fileContainer').index($(this));
		$('input[type=file]').eq(tmpIndex).trigger('click');
	});
	$('input[type=file]').change(function(){
		tmpIndex=$('input[type=file]').index($(this));
		$('.fileContainer span.fcText').eq(tmpIndex).html($(this).val());
	});	
});

