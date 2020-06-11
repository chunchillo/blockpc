const correo = document.getElementById('correo');

correo.addEventListener('click', function() {
    const token = $("#token").val();
    $.ajax({
        url: `${url}usuarios/perfil/correo`,
        data: {token},
        type: "post",
        beforeSend: function(){},
        success: function(respuesta) {
            console.log(respuesta)
        }
    });
});