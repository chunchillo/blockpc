/* eliminar.js */
var ruta = url + "usuarios/ajax/clave";
$("#generarClave").on("click", function(e) {
  var token = $("#token").val();
  var datos = new FormData();
  datos.append("token", token);
  $.ajax({
    url: ruta,
    method: "POST",
    data: datos,
    cache: false,
    contentType: false,
    processData: false,
    dataType: "json",
    success: function(respuesta) {
      if (!respuesta.ok) {
        $("#errorHelpBlock").removeClass("text-info").addClass("text-danger").html(respuesta.texto);
      } else {
        $("#txtClave").val(respuesta.clave);
        $("#txtClaveDos").val(respuesta.clave);
        $("#errorHelpBlock").removeClass("text-danger").addClass("text-info").html(respuesta.texto);
      }
    }
  });
});