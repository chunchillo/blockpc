let btn_print = document.getElementById('btn-print');
let btn_pdf = document.getElementById('btn-pdf');
let btn_xls = document.getElementById('btn-xls');
let btn_word = document.getElementById('btn-word');
let btn_csv = document.getElementById('btn-csv');

btn_print.addEventListener('click', function(e) {
    e.preventDefault();
    $('#tabla').printThis();
});

btn_pdf.addEventListener('click', function(e) {
    console.log("PDF");
});

btn_xls.addEventListener('click', function(e) {
    console.log("Excel");
});

btn_word.addEventListener('click', function(e) {
    console.log("Word");
});

btn_csv.addEventListener('click', function(e) {
    console.log("CSV");
});

$(document).on("click", ".question-mark", function(e) {
    e.preventDefault();
    console.log("Hoa");
	mensajeModal(1, $(this).prop('title'));
});