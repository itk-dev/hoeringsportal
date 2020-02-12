/**
 * @file
 * Global fetch.
 */

require('../css/form-ticket-add.css')

const $ = require('jquery')
require('jquery-validation')
// IE 11 does not support Object.assign.
require('es6-object-assign/auto');
const dawaAutocomplete = require('dawa-autocomplete2')

$(() => {
  var config = {
    fields: {
      address: 'edit-address',
      postalCode: 'edit-postal-code',
      geolocation: 'edit-geolocation'
    }
  }

  var address = document.querySelector('[data-drupal-selector="' + config.fields.address + '"]')
  var postalCode = document.querySelector('[data-drupal-selector="' + config.fields.postalCode + '"]')
  var geolocation = document.querySelector('[data-drupal-selector="' + config.fields.geolocation + '"]')

  if (address !== null) {
    // Address autocomplete using https://dawa.aws.dk/.
    var addressWrapper = document.createElement('div')
    addressWrapper.setAttribute('class', 'dawa-autocomplete-container')
    address.parentNode.replaceChild(addressWrapper, address)
    addressWrapper.appendChild(address)

    dawaAutocomplete.dawaAutocomplete(address, {
      select: function (selected) {
        fetch(selected.data.href)
          .then(function (response) {
            return response.json()
          })
          .then(function (json) {
            try {
              if (geolocation !== null) {
                var coords = json.adgangsadresse.adgangspunkt.koordinater
                geolocation.value = coords[0] + ', ' + coords[1]
              }
              if (postalCode !== null) {
                postalCode.value = json.adgangsadresse.postnummer.nr
              }
            }
            catch (e) {

            }
          })
      }
    })
  }

  document.querySelector('form').addEventListener('submit', function () {
    if ($(this).valid()) {
      $(this).addClass('is-submitted')
    }
  })
})
