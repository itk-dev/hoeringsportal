(function ($) {
  $(function() {
    // Emphasize "Hele kommunen" in "Area" selection.
    $('.field--type-entity-reference.field--name-field-area .fieldset-wrapper .form-checkboxes label').filter(function () {
      if ('Hele kommunen' === $(this).text()) {
        $(this).wrapInner('<strong/>')
      }
    })

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
