(function ($) {
  $(function() {
    // Add "Select all" and "Select none" button for "Area".
    $checkboxes = $('.field--type-entity-reference.field--name-field-area .fieldset-wrapper .form-checkboxes')
    $buttons = $('<div class="buttons"/>')
    $checkboxes.before($buttons)

    $selectAll = $('<button type="button" class="button button--small">' + Drupal.t('Select all') + '</button>')
    $selectAll.on('click', function() {
      $checkboxes.find('input[type="checkbox"]').prop('checked', true)
    })
    $buttons.append($selectAll)

    $selectNone = $('<button type="button" class="button button--small">' + Drupal.t('Select none') + '</button>')
    $selectNone.on('click', function() {
      $checkboxes.find('input[type="checkbox"]').prop('checked', false)
    })
    $buttons.append($selectNone)
  })
}(jQuery));
