/* editar.js */
$("#generarClave").on("click", function(e) {
    var token = $("#token").val();
    $.ajax({
        url: url + "usuarios/ajax/clave",
        method: "POST",
        data: {token},
        cache: false,
        beforeSend: function() {},
        success: function(respuesta) {
            if (!respuesta.ok) {
                $("#errorHelpBlock").removeClass("text-info").addClass("text-danger").html(respuesta.texto);
            } else {
                $("#password").val(respuesta.clave);
                $("#confirm").val(respuesta.clave);
                $("#errorHelpBlock").removeClass("text-danger").addClass("text-info").html(respuesta.texto);
            }
        }
    });
});
$(document).on('input', '#password', function() {
    var value = $(this).val();
    console.log(value);
    if ( !value.trim() ) {
        $("#password").val("");
        $("#confirm").val("");
        $("#errorHelpBlock").removeClass("text-danger").addClass("text-info").html("");
    }
});
$(document).on('change', '.custom-file-input:file', function() {
    var input = $(this),
        label = input.val().replace(/\\/g, '/').replace(/.*\//, '');
    if ( input.length ) {
        $(".custom-file-label").text(label);
    } else {
        $(".custom-file-label").text("Choose Image");
    }
});