/* usuarios.js */
$(document).on('click', '.btn-ver', function(){
    var id = $(this).val();
    $.ajax({
        url: url + "usuarios/ajax/usuario",
        data: {id:id},
        type: "post",
        beforeSend: function(){
            $("#modal-ver .modal-body").html("");
        },
        success: function(respuesta) {
            if ( !respuesta.ok ) {
                mensajeModal(0, `Error Perfil|${respuesta.error}`)
            } else {
                $("#modal-ver .modal-body").html(respuesta.perfil);
            }
        }
    });
});
$(document).on("click", ".btn-activar", function(e) {
    let r = confirm("¿Esta seguro que quiere activar este usuario?");
    if ( r ) {
        let id = $(this).data("id");
        const token = $("#token").val()
        $.ajax({
            url: `${url}usuarios/eliminados/activar`,
            data: {token, id},
            type: "post",
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                console.log(errorThrown)
            },
            beforeSend: function(){},
            success: function(respuesta) {
                if ( respuesta.ok ) {
                    tabla(1);
                } else {
                    mensajeModal(0, respuesta.error)
                }
            }
        });
    }
});
$(document).on("click", ".question-mark", function(e) {
    e.preventDefault();
    console.log("Hoa");
	mensajeModal(1, $(this).prop('title'));
});