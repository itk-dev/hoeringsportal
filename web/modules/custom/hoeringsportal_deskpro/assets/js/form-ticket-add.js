/* global Drupal */
require('../css/form-ticket-add.css')

const $ = require('jquery')
require('jquery-validation')
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

  const updateUI = () => {
    const $representation = $('[data-drupal-selector="edit-representation"]')
    const value = $representation.val()
    const isPersonal = parseInt(value) === 5
    const text = $representation.find('option:selected').text()

    if (text) {
      // Set labels
      const labels = {
        // Field name => label
        'name': isPersonal ? Drupal.t('Your name') : Drupal.t('Name (@organization)', { '@organization': text }),
        'email': isPersonal ? Drupal.t('Your email address') : Drupal.t('Email address (@organization)', { '@organization': text }),
        'postal-code-and-city': isPersonal ? Drupal.t('Your postal code and city') : Drupal.t('Postal code and city (@organization)', { '@organization': text }),
        'street-and-number': isPersonal ? Drupal.t('Your street and number') : Drupal.t('Street and number (@organization)', { '@organization': text })
      }
      for (let [key, label] of Object.entries(labels)) {
        const selector = `[for="edit-${key}"]`
        $(selector).text(label)
      }
    }
  }

  $('[data-drupal-selector="edit-representation"]').on('change', updateUI)
  updateUI()
})
