/* revisar.js */
$(document).ajaxStop($.unblockUI); 
let estado_actual = parseInt($("#estado_actual").val())
let estado_usuario = parseInt(estado_actual) ? "El usuario esta activado" : "El usuario no esta activado"
var mediumRegex = new RegExp("^(((?=.*[a-z])(?=.*[A-Z]))|((?=.*[a-z])(?=.*[0-9]))|((?=.*[A-Z])(?=.*[0-9])))(?=.{8,})");
let mensajes = []
const show_messages = () => {
    $("#mensajes").empty()
    $("#alert-sistema").empty()
    if ( mensajes.length ) {
        mensajes.forEach( msg => {
            $("#mensajes").append(`
                <li class="list-group-item list-group-item-success small p-1" id="${msg.estado}">${msg.mensaje}</li>
            `)
        })
    }
}
const borrar_mensaje = (tipo) => {
    const i = mensajes.findIndex( mensaje => mensaje.estado == tipo)
    if ( i != -1 ) {
        mensajes.splice(i,1)
    }
}
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
$("#activado").on("click", function(e) {
    let valor = $(this).val()
    let estado_temporal = ""
    if ( parseInt(valor) ) {
        estado_temporal = parseInt(valor) == estado_actual ? "El usuario sera desactivado" : estado_usuario
        $("#estado_usuario").text(estado_temporal)
        $("#activado").val(0)
    } else {
        estado_temporal = parseInt(valor) == estado_actual ? "El usuario sera activado" : estado_usuario
        $("#estado_usuario").text(estado_temporal)
        $("#activado").val(1)
    }
    valor = $(this).val()
    if ( estado_temporal && estado_temporal != estado_usuario ) {
        mensajes.push({'estado': 'activar', 'mensaje': estado_temporal})
    } else {
        if ( estado_temporal ) {
            borrar_mensaje("activar")
        }
    }
    if ( parseInt(valor) ) {
        $("#link_activacion").prop("disabled", true)
    } else {
        $("#link_activacion").prop("disabled", false)
    }
    show_messages()
})
$("#generar").on("click", function(e) {
    var token = $("#token").val();
    $.ajax({
        url: url + "ajax/clave",
        method: "POST",
        data: {token},
        cache: false,
        beforeSend: function() {},
        success: function(respuesta) {
            borrar_mensaje("clave")
            if (!respuesta.ok) {
                $("#cambiar_clave").removeClass(["text-muted", "text-info"]).addClass("text-danger").html(respuesta.texto);
            } else {
                $("#password").val(respuesta.clave);
                $("#cambiar_clave").removeClass(["text-muted", "text-danger"]).addClass("text-info").html(respuesta.texto);
                mensajes.push({'estado': 'clave', 'mensaje': "Se actualizara la <b>Clave</b>"});
                $("#link_activacion").prop("checked", true);
            }
            show_messages()
        }
    });
});
$("#password").on("input", function(e) {
    const valor = $(this).val();
    const i = mensajes.findIndex( mensaje => mensaje.estado == "clave");
    if ( !valor ) {
        borrar_mensaje("clave");
        $("#cambiar_clave").empty();
        $("#link_activacion").prop("checked", false);
    } else {
        let valido = mediumRegex.test(valor)
        if ( valido ) {
            if ( i == -1 ) {
                mensajes.push({'estado': 'clave', 'mensaje': "Se actualizara la <b>Clave</b>"})
            }
            $("#cambiar_clave").removeClass(["text-muted", "text-danger"]).addClass("text-info").html("Password valido");
            $("#link_activacion").prop("checked", true);
        } else {
            borrar_mensaje("clave")
            $("#cambiar_clave").removeClass(["text-muted", "text-info"]).addClass("text-danger").html("Password no valido");
            $("#link_activacion").prop("checked", false);
        }
    }
    show_messages()
})
$("#password").on('blur', function() {
    const valor = $(this).val()
    if ( valor ) {
        let valido = mediumRegex.test(valor)
        const i = mensajes.findIndex( mensaje => mensaje.estado == "clave")
        if ( valido ) {
            if ( i == -1 ) {
                mensajes.push({'estado': 'clave', 'mensaje': "Se actualizara la <b>Clave</b>"})
            }
            $("#cambiar_clave").removeClass(["text-muted", "text-danger"]).addClass("text-info").html("Password valido");
            $("#link_activacion").prop("checked", true);
        } else {
            $("#cambiar_clave").removeClass(["text-muted", "text-info"]).addClass("text-danger").html("Password no valido");
            $("#link_activacion").prop("checked", false);
        }
    }
    show_messages()
})
$("#roles").on("change", function() {
    let valor = $(this).val()
    let cargo = $( "#roles option:selected" ).text();
    let rol = $("#rol_actual").val()
    const i = mensajes.findIndex( mensaje => mensaje.estado == "rol")
    if ( valor == "0" ) {
        borrar_mensaje("rol")
        mensajeModal(0, "Error Cambiar Rol|Debes seleccionar un ROL")
        $("#estado_rol").removeClass(["text-muted", "text-info"]).addClass("text-danger").html("Debes seleccionar un ROL");
    } else if ( valor != rol ) {
        if ( i == -1 ) {
            mensajes.push({'estado': 'rol', 'mensaje': "Se actualizara el <b>ROL</b>"})
        }
        $("#estado_rol").removeClass(["text-muted", "text-danger"]).addClass("text-info").html(`El Rol cambiara a <b>${cargo}</b>`);
    } else {
        borrar_mensaje("rol")
        $("#estado_rol").removeClass(["text-danger", "text-info"]).addClass("text-muted").html(`Rol actual <b>${cargo}</b>`);
    }
    show_messages()
})
$("#link_activacion").on("change", function(e) {
    let check = $(this).is(":checked");
    let pass = $("#password").val();
    if ( check ) {
        $("#envio_correo").prop("checked", true);
        $("#sendMessage").show();
    } else {
        if ( pass ) {
            $(this).prop("checked", true);
            return false;
        }
        $("#envio_correo").prop("checked", false);
        $("#sendMessage").hide();
    }
});
$(document).on("change", "#envio_correo", function() {
    let check = $(this).is(":checked");
    if ( check ) {
        $("#sendMessage").show();
    } else {
        $("#sendMessage").hide();
    }
});
$("#guardar").on("click", function() {
    const token = $("#token").val();
    const usuario_id = $("#usuario_id").val();
    const activar = $("#activado").val();
    const rol = $("#roles").val();
    const password = $("#password").val();
    const adicional = $("#adicional").val();
    const link = $("#link_activacion").is(":checked") ? 1 : 0;
    const correo = $("#envio_correo").is(":checked") ? 1 : 0;
    $.ajax({
        url: `${url}usuarios/revisar/guardar`,
        data: {token, usuario_id, activar, rol, password, link, correo, adicional},
        type: "post",
        error: function(XMLHttpRequest, textStatus, errorThrown) {
            console.log(errorThrown);
        },
        beforeSend: function(){
            if ( correo ) $.blockUI(mensaje("<h3>Enviando Correo.... Espere por favor</h3>"));
        },
        success: function(respuesta) {
            if ( respuesta.ok ) {
                redireccionar("sistema/dashboard");
            } else {
                mensajeModal(0, respuesta.error);
            }
        }
    });
})