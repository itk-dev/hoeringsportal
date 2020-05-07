require('../css/form-ticket-add.css')

const $ = require('jquery')
require('jquery-ui/ui/widgets/autocomplete')

// @see https://stackoverflow.com/a/11845718
$.ui.autocomplete.prototype._resizeMenu = function () {
  var ul = this.menu.element
  ul.outerWidth(this.element.outerWidth())
}

$(() => {
  const config = {
    fields: {
      postalCodeAndCity: 'edit-postal-code-and-city',
      streetAndNumber: 'edit-street-and-number'
    }
  }

  const postalCodeAndCity = $('[data-drupal-selector="' + config.fields.postalCodeAndCity + '"]')
  const streetAndNumber = $('[data-drupal-selector="' + config.fields.streetAndNumber + '"]')

  if (postalCodeAndCity && streetAndNumber) {
    // @see https://codepen.io/mi2oon/pen/zpNMoL
    const API = 'https://dawa.aws.dk'

    const getData = (path, query = {}) => {
      const url = `${API}/${path}?` + Object.entries(query)
        .filter(e => e[1] !== null)
        .map(e => encodeURIComponent(e[0]) + '=' + encodeURIComponent(e[1])).join('&')

      return $.ajax({
        url: url
      })
    }

    const address = {
      postnr: null,
      vejnavn: null,
      husnr: null
    }

    // Autocomplete postal code
    getData('postnumre')
      .then(data => {
        const autocompleteData = data.map(postnr => ({
          label: `${postnr.nr} ${postnr.navn}`,
          dataValue: postnr.nr
        }))

        postalCodeAndCity.autocomplete({
          source: autocompleteData,
          autoFocus: true,
          minLength: 1,
          select: (e, ui) => {
            address.postnr = ui.item.dataValue
            address.vejnavn = null
            address.husnr = null
          }
        })
      })

    const autocompleteStreetNumber = function () {
      getData('adresser', address)
        .then((data) => {
          const autocompleteData = data
            .map(adresse => adresse.adgangsadresse)
            .map(adgangsadresse => ({
              label: `${adgangsadresse.vejstykke.navn} ${adgangsadresse.husnr}`,
              dataValue: adgangsadresse.husnr
            }))

          $(this).autocomplete({
            source: autocompleteData,
            autoFocus: true,
            minLength: 1,
            select: (e, ui) => { address.husnr = ui.item.dataValue }
          })
        })
    }

    const autocompleteStreet = function () {
      if (address.postalCode === null) {
        return
      }

      getData('vejnavne', address)
        .then((data) => {
          const autocompleteData = data.map(vej => ({
            label: vej.navn,
            dataValue: vej.navn
          }))

          $(this).autocomplete({
            source: autocompleteData,
            autoFocus: true,
            minLength: 1,
            select: (e, ui) => {
              address.vejnavn = ui.item.dataValue
              address.husnr = null

              autocompleteStreetNumber.apply(this)
            }
          })
        })
    }

    streetAndNumber
      .on('focus', autocompleteStreet)
      .on('keyup', function (event) {
        switch (event.key) {
          // Ignore keys used to navigate autocomplete suggestions.
          case 'ArrowUp':
          case 'ArrowDown':
          case 'Tab':
          case 'Enter':
            break

          default:
          // Decide if we're completing street or street number.
            const value = $(this).val()
            if (address.vejnavn !== null) {
            // If a street has been selected and the current value if a prefix of the selected value, we autocomplete (a new) street.
              if (value === '' || address.vejnavn.indexOf(value) > -1) {
                autocompleteStreet.apply(this)
              } else {
                autocompleteStreetNumber.apply(this)
              }
            }
            break
        }
      })
  }

  document.querySelector('form').addEventListener('submit', function () {
    if ($(this).valid()) {
      $(this).addClass('is-submitted')
    }
  })

  $('[data-drupal-selector="edit-representation"]').on('change', function () {
    const text = $('option:selected', this).text()
    if (text) {
      $('[for="edit-organization"]').text(`Navn på ${text.toLowerCase()}`)
    }
  })
})
