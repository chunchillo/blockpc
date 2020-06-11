/* eliminar.js */
$(document).ajaxStop($.unblockUI);
$(document).on("click", "#truncar", function(e) {
	e.preventDefault();
	var r = confirm("Esta seguro que desea eliminar las tablas e iniciar ")
	if ( r == true ) {
		const token = $("#token").val();
		$.ajax({
			url: url + 'usuarios/perfil/truncar',
			data: {token},
			type: "post",
			beforeSend: function(){
				$("#modalTruncarDB").modal('hide');
				$.blockUI(mensaje("<h3>Eliminando Datos.... Espere por favor</h3>"));
			},
			success: function(respuesta) {
				mensajeModal(respuesta.ok, respuesta.mensaje);
			}
		});
	}
});
const mensaje = (msj) => {
	return {
		message: msj,
		css: {
			border: 'none', 
			padding: '15px', 
			backgroundColor: '#000', 
			'-webkit-border-radius': '10px', 
			'-moz-border-radius': '10px', 
			opacity: .5, 
			color: '#fff' 
		}
	}
}