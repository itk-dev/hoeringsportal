window.addEventListener('load', function() {
    var config = {
        fields: {
            address: 'edit-address',
            geolocation: 'edit-geolocation'
        }
    };

    var address = document.querySelector('[data-drupal-selector="'+config.fields.address+'"]');
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
                            var coords = json.adgangsadresse.adgangspunkt.koordinater;
                            if (null !== geolocation) {
                                geolocation.value = coords[0]+', '+coords[1];
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
