window.addEventListener('load', function() {
    var config = {
        fields: {
            address: 'edit-address',
            postalCode: 'edit-postal-code',
            geolocation: 'edit-geolocation'
        }
    };

    var address = document.querySelector('[data-drupal-selector="'+config.fields.address+'"]');
    var postalCode = document.querySelector('[data-drupal-selector="'+config.fields.postalCode+'"]');
    var geolocation = document.querySelector('[data-drupal-selector="'+config.fields.geolocation+'"]');

    if (null !== address) {
      // Address autocomplete using https://dawa.aws.dk/.
      var addressWrapper = document.createElement('div');
        addressWrapper.setAttribute('class', 'dawa-autocomplete-container');
        address.parentNode.replaceChild(addressWrapper, address);
        addressWrapper.appendChild(address);

        dawaAutocomplete.dawaAutocomplete(address, {
            select: function(selected) {
                fetch(selected.data.href)
                    .then(function(response) {
                        return response.json();
                    })
                    .then(function(json) {
                        try {
                            if (null !== geolocation) {
                                var coords = json.adgangsadresse.adgangspunkt.koordinater;
                                geolocation.value = coords[0]+', '+coords[1];
                            }
                            if (null !== postalCode) {
                                postalCode.value = json.adgangsadresse.postnummer.nr;
                            }
                        } catch (e) {}
                    });
            }
        });
    }

    // @TODO: Add class when form is valid and is submitted.
    // document.querySelectorAll('#hearing-ticket-add-form').addEventListener('submit', function() {
    //     this.classList.add('is-submitted');
    // }
});
