require('../css/single-date.css')

document.querySelectorAll('.date-to-element input.form-date')[0].disabled = true
let tableRow = document.querySelectorAll('#field-pretix-dates-values tr')
for (var i = 0; i < tableRow.length; i++) {
  tableRow[i].onchange = function () {
    this.querySelectorAll('.date-to-element .form-date')[0].value =
      this.querySelectorAll('.date-from-element .form-date')[0].value
  }
}
