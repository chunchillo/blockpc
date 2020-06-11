/* control.js */
var ruta = url + "usuarios/acl/ajax";
$("#role").on("change", function(e) {
  var idRole = $(this).val();
  var token = $("#token").val();
  var datos = new FormData();
  datos.append("idRole", idRole);
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
      console.log(respuesta);
      if (!respuesta.ok) {
        $( "#cambiarPermisos" ).prop( "disabled", true );
        $("#permisos").html(respuesta.texto);
      } else {
        $( "#cambiarPermisos" ).prop( "disabled", false );
        $("#permisos").html(respuesta.texto);
      }
    }
  });
});