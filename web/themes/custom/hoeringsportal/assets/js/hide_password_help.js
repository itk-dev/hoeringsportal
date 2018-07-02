$ = require('jquery')

// Function for hiding password confirmation text.
var passwordConfirm = () => {
  if (!$('input.js-password-field').val()) {
    $('div.js-password-confirm').css("visibility", "hidden");
  }
}

$(() => {
  passwordConfirm();
})
