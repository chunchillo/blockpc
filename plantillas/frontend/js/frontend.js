/* frontend.js */
let url = window.location.protocol + "//" + window.location.host;
if ( window.location.host === "localhost" )
	url = url + '/base/';
else
	url = url + '/';

function redireccionar(ruta){
	window.location=url + ruta;
}
function mensajeModal(ok, mensaje) {
    var modal = '';
    var titulo = '';
	if ( !mensaje ) {
		mensaje = "Un error ha ocurrido. Consulte con el administrador!";
	}
	mensaje = mensaje.trim();
	mensaje = mensaje.split("|");
	var mensajes = mensaje.length;
	if ( mensajes > 1 ) {
		titulo = mensaje.shift();
	}
	mensaje = mensaje.join("<br>");
	titulo = titulo ? titulo : ( ok ? 'Mensaje' : 'Error');
    if ( ok ) {
        modal = '<div class="modal_wrap"><div class="mensaje_modal"><h3 class="text-success">'+titulo+'</h3><p>'+mensaje+'</p><div id="btnCloseModal">Cerrar</div></div></div>';
    } else {
        modal = '<div class="modal_wrap"><div class="mensaje_modal"><h3 class="text-danger">'+titulo+'</h3><p>'+mensaje+'</p><div id="btnCloseModal">Cerrar</div></div></div>';
    }
	if ( $('.modal').hasClass('show') ) {
		$('.modal').modal('hide');
	}
    $('html').append(modal);
}
$(document).on("click", "#btnCloseModal", function(){
    $('.modal_wrap').remove();
});