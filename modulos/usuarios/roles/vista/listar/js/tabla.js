let limite = document.getElementById('limite');
let search = document.getElementById('search');
let pagina = document.getElementById('pagina');
let orden = document.getElementById('orden');
let campo = document.getElementById('campo');
let ordenadores = document.getElementsByClassName('fa-sort');
let paginacion = document.getElementById('paginacion');
let leyenda = document.getElementById('leyenda');
let datos = document.getElementById('datos');

limite.addEventListener('change', function() {
    tabla();
});

search.addEventListener('input', function() {
    tabla();
});

for (const ordenador of ordenadores) {
    ordenador.addEventListener('click', function() {
        let data_orden = this.dataset.orden;
        let data_campo = this.dataset.campo;
        if ( data_orden == "ASC" ) {
            orden.value = "DESC";
            this.dataset.orden = "DESC";
        } else {
            orden.value = "ASC";
            this.dataset.orden = "ASC";
        }
        campo.value = data_campo;
        tabla();
    })
}

const tabla = async () => {
    try {
        const token = $("#token").val();
        const formData = new FormData();
        formData.append('token', token);
        formData.append('limite', limite.value);
        formData.append('pagina', pagina.value);
        formData.append('search', search.value);
        formData.append('orden', orden.value);
        formData.append('campo', campo.value);
        const response = await fetch( url + 'usuarios/roles/listar/tabla', { method: 'POST', body: formData });
        const data = await response.json();
        if ( !data.ok ) {
            mensajeModal(0, data.error)
        } else {
            paginacion.innerHTML = data.paginacion;
            leyenda.innerHTML = data.leyenda;
            datos.innerHTML = data.datos;
            pagina.value = data.pagina;
        }
    } catch(error) {
        console.log(error)
    }
}