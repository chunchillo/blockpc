/* editar.js */
$("#generarClave").on("click", function(e) {
    var token = $("#token").val();
    $.ajax({
        url: url + "usuarios/ajax/clave",
        method: "POST",
        data: {token:token},
        cache: false,
        beforeSend: function() {
            console.log(token);
        },
        success: function(respuesta) {
            console.log(respuesta);
            if (!respuesta.ok) {
                $("#errorHelpBlock").removeClass("text-info").addClass("text-danger").html(respuesta.texto);
            } else {
                $("#clave").val(respuesta.clave);
                $("#txtClave").val(respuesta.clave);
                $("#errorHelpBlock").removeClass("text-danger").addClass("text-info").html(respuesta.texto);
            }
        }
    });
});