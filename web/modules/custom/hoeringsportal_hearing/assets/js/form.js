(function ($) {
  $(function() {
    // Emphasize "Hele kommunen" in "Area" selection.
    $('.field--type-entity-reference.field--name-field-area .fieldset-wrapper .form-checkboxes label').filter(function () {
      if ('Hele kommunen' === $(this).text()) {
        $(this).wrapInner('<strong/>')
      }
    })
  })
}(jQuery));
