/* chile.js */
let region = document.getElementById('region');
let provincia = document.getElementById('provincia');
let comuna = document.getElementById('comuna');

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