require('../css/hearing-edit.scss')
const dawaAutocomplete2 = require('dawa-autocomplete2');

window.addEventListener('load', function() {
  const config = {
    fields: {
      address: 'edit-field-map-0-address',
    }
  };

  const address = document.querySelector('[data-drupal-selector="'+config.fields.address+'"]');

  if (null !== address) {
    // Address autocomplete using https://dawa.aws.dk/.
    var addressWrapper = document.createElement('div');
    addressWrapper.setAttribute('class', 'dawa-autocomplete-container');
    address.parentNode.replaceChild(addressWrapper, address);
    addressWrapper.appendChild(address);

    dawaAutocomplete2.dawaAutocomplete(address, {
      select: function(selected) {
        fetch(selected.data.href)
          .then(function(response) {
            return response.json();
          })
          .then(function(json) {
            try {
              const coords = json.adgangsadresse.adgangspunkt.koordinater;
            } catch (e) {}
          });
      }
    });
  }
});
