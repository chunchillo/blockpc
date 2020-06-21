/* nuevo.js */
let token = document.getElementById('token');
let generarClave = document.getElementById('generarClave');
let clave = document.getElementById('clave');
let repetir = document.getElementById('repetir');
let error = document.getElementById('errorGeneraClave');
let region = document.getElementById('region');
let provincia = document.getElementById('provincia');
let comuna = document.getElementById('comuna');

generarClave.addEventListener('click', async function() {
    try {
        error.innerHTML = "";
        const t = token.value;
        const formData = new FormData();
        formData.append('token', t);
        const response = await fetch( url + 'ajax/clave', { method: 'POST', body: formData });
        const data = await response.json();
        if ( !data.ok ) {
            mensajeModal(0, data.error)
        } else {
            clave.value = data.clave;
            repetir.value = data.clave;
            error.innerHTML = data.mensaje;
        }
    } catch(error) {
        console.log(error)
    }
});

region.addEventListener('change', async function() {
    let r = this.value;
    try {
        const t = token.value;
        const formData = new FormData();
        formData.append('token', t);
        formData.append('region', r);
        const response = await fetch( url + 'ajax/chile/provincia', { method: 'POST', body: formData });
        const data = await response.json();
        if ( !data.ok ) {
            mensajeModal(0, data.error)
        } else {
            provincia.innerHTML = data.vista;
        }
    } catch(error) {
        console.log(error)
    }
});

provincia.addEventListener('change', async function() {
    let p = this.value;
    try {
        const t = token.value;
        const formData = new FormData();
        formData.append('token', t);
        formData.append('provincia', p);
        const response = await fetch( url + 'ajax/chile/comuna', { method: 'POST', body: formData });
        const data = await response.json();
        if ( !data.ok ) {
            mensajeModal(0, data.error)
        } else {
            comuna.innerHTML = data.vista;
        }
    } catch(error) {
        console.log(error)
    }
});

function fakeUser () {

    // make name contextual to username and email
    let nombre = faker.name.firstName();
    let first = nombre.normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLocaleLowerCase();
    let apellido = faker.name.lastName();
    let last = apellido.normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLocaleLowerCase();
    let alias = first;
    let correo = `${first}.${last}@mail.com`;
    let direccion = faker.address.streetAddress();
    let telefono = faker.phone.phoneNumber().replace(/\D/g, "");
    let clave = '123456';
    let rut = formatRut(generateRut(), RutFormat.DOTS_DASH);

    $("#alias").val(alias);
    $("#email").val(correo);
    $("#clave").val(clave);
    $("#repetir").val(clave);
    $("#nombre").val(nombre);
    $("#apellido").val(apellido);
    $("#rut").val(rut);
    $("#direccion").val(direccion);
    $("#celular").val(telefono);
};

$('#fakeUser').click(function(){
    fakeUser();
});