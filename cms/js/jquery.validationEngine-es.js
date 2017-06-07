(function($){
	$.fn.validationEngineLanguage = function(){
	};
	$.validationEngineLanguage = {
		newLang: function(){
			$.validationEngineLanguage.allRules = {
				"required": { // Add your regex rules here, you can take telephone as an example
					"regex": "none",
					"alertText": "* Este campo es requerido",
					"alertTextCheckboxMultiple": "* Por favor seleccione una opción",
					"alertTextCheckboxe": "* Este checkbox es requerido",
					"alertTextDateRange": "* Los rangos de fecha son requeridos"
				},
				"minSize": {
					"regex": "none",
					"alertText": "* Mínimo de ",
					"alertText2": " caracteres autorizados"
				},
				"maxSize": {
					"regex": "none",
					"alertText": "* Máximo de ",
					"alertText2": " caracteres autorizados"
				},
				"min": {
					"regex": "none",
					"alertText": "* Valor mínimo es "
				},
				"max": {
					"regex": "none",
					"alertText": "* Valor máximo es "
				},
				"groupRequired": {
					"regex": "none",
					"alertText": "* Debe diligenciar uno de los siguientes campos"
				},
				"past": {
					"regex": "none",
					"alertText": "* Fecha anterior a "
				},
				"future": {
					"regex": "none",
					"alertText": "* Fecha posterior a "
				},
				"maxCheckbox": {
					"regex": "none",
					"alertText": "* Se ha excedido el número de opciones permitidas"
				},
				"minCheckbox": {
					"regex": "none",
					"alertText": "* Por favor seleccione ",
					"alertText2": " opción(es)"
				},
				"equals": {
					"regex": "none",
					"alertText": "* Los campos no coinciden"
				},
				"creditCard": {
					"regex": "none",
					"alertText": "* Número de tarjeta de crédito inválido"
				},
				"phone": {
					// credit: jquery.h5validate.js / orefalo
					"regex": /^([\+][0-9]{1,3}[ \.\-])?([\(]{1}[0-9]{2,6}[\)])?([0-9 \.\-\/]{3,20})((x|ext|extension)[ ]?[0-9]{1,4})?$/,
					"alertText": "* Número de teléfono inválido"
				},
				"email": {
					// Shamelessly lifted from Scott Gonzalez via the Bassistance Validation plugin http://projects.scottsplayground.com/email_address_validation/
					"regex": /^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i,
					"alertText": "* Correo inválido"
				},
				"integer": {
					"regex": /^[\-\+]?\d+$/,
					"alertText": "* No es un valor entero válido"
				},
				"number": {
					// Number, including positive, negative, and floating decimal. credit: orefalo
					"regex": /^[\-\+]?(([0-9]+)([\.,]([0-9]+))?|([\.,]([0-9]+))?)$/,
					"alertText": "* No es un valor decimal válido"
				},
				"date": {
					"regex": /^(0?[1-9]|[12][0-9]|3[01])[\/\-](0?[1-9]|1[012])[\/\-]\d{4}$/,
					"alertText": "* Fecha inválida, por favor utilice el formato DD/MM/AAAA"
				},
				"ipv4": {
					"regex": /^((([01]?[0-9]{1,2})|(2[0-4][0-9])|(25[0-5]))[.]){3}(([0-1]?[0-9]{1,2})|(2[0-4][0-9])|(25[0-5]))$/,
					"alertText": "* Direccion IP inválida"
				},
				"url": {
					"regex": /^(https?|ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(\#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i,
					"alertText": "* URL Inválida"
				},
				"youtubeUrl": {
					"regex": /^\s*(?:https?:\/\/)?(?:www\.)?(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))((\w|-){11})(?:\S+)?\s*$/,
					"alertText": "* Debe ingresar una URL de Youtube válida"
				},
				"onlyNumberSp": {
					"regex": /^[0-9\ ]+$/,
					"alertText": "* Sólo números"
				},
				"onlyLetterSp": {
					"regex": /^[a-zA-Z\ \']+$/,
					"alertText": "* Sólo letras"
				},
				"onlyLetterNumber": {
					"regex": /^[0-9a-zA-Z]+$/,
					"alertText": "* No se permiten caracteres especiales"
				},
				"subdomain": {
					"regex": /^[0-9a-z]+$/,
					"alertText": "* Sólo letras en minúscula y números"
				},
				"filename": {
					"regex": /^((?!^index$)[0-9a-z\-])*$/,
					"alertText": "* Sólo letras en minúscula, números y guiones (-). No se permite 'index'."
				},
				"dbname": {
					"regex": /^[0-9a-zA-Z_\-]+$/,
					"alertText": "* Sólo letras, números, guiones y guiones bajos."
				},
				// --- CUSTOM RULES -- Those are specific to the demos, they can be removed or changed to your likings
				"ajaxUserPasswordValidate": {
					"url": "cms/home/passwordValidate",
					// if you provide an "alertTextOk", it will show as a green prompt when the field validates
					"alertTextOk": "* Contraseña Válida",
					"alertText": "* Contraseña Incorrecta",
					"alertTextLoad": "* Validando, por favor espere"
				},
				"ajaxUserCall": {
					"url": "ajaxValidateFieldUser",
					"extraData": "name=eric",  // you may want to pass extra data on the ajax call
					"alertTextLoad": "* Cargando, espere por favor",
					"alertText": "* Este nombre de usuario ya se encuentra en uso"
				},
				"ajaxNameCall": {
					"url": "ajaxValidateFieldName",  // remote json service location
					"alertText": "* Este nombre ya se encuentra en uso",  // error
					// if you provide an "alertTextOk", it will show as a green prompt when the field validates
					"alertTextOk": "* Este nombre está disponible",
					"alertTextLoad": "* Cargando, espere por favor"  // speaks by itself
				},
				"validate2fields": {
					"alertText": "* Por favor entrar HELLO"
				}
			};
		}
	};
	$.validationEngineLanguage.newLang();
})(jQuery);
