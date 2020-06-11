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
$(document).on("click", ".question-mark", function(e) {
    e.preventDefault();
    console.log("Hoa");
	mensajeModal(1, $(this).prop('title'));
});