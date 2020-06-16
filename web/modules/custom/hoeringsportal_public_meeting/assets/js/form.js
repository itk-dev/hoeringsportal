(function ($) {
  $(function() {
    // Add "Select all" and "Select none" button for "Area".
    $wrapper = $('.field--type-entity-reference.field--name-field-area .fieldset-wrapper')

    $selectAll = $('<button type="button" class="button button--small">' + Drupal.t('Select all') + '</button>')
    $selectAll.on('click', function() {
      $(this).parent().find('input[type="checkbox"]').prop('checked', true)
    })
    $wrapper.prepend($selectAll)

    $selectNone = $('<button type="button" class="button button--small">' + Drupal.t('Select none') + '</button>')
    $selectNone.on('click', function() {
      $(this).parent().find('input[type="checkbox"]').prop('checked', false)
    })
    $selectAll.after($selectNone)
  })
}(jQuery));
