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

    // Copy date from meeting start time to meeting end time.
    var $meetingStartTimes = $('[name^="field_last_meeting_time"][name$="[value][date]"')
    $meetingStartTimes.on('change', function () {
      var match = /\[(\d+)\]/.exec($(this).attr('name'))
      if (match) {
        var index = parseInt(match[1])
        var endTime = $('[name="field_last_meeting_time_end['+index+'][value][date]"')
        $(endTime).val($(this).val())
      }
    })
    $meetingStartTimes.trigger('change')
    // Hide date part of the meeting end time.
    $('[name^="field_last_meeting_time_end"][name$="[value][date]"').hide()
  })
}(jQuery));
