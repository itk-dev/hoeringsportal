/**
 * @file
 * Hide password helper.
 */

const $ = require("jquery");

// Function for hiding password confirmation text.
const passwordConfirm = () => {
  if (!$("input.js-password-field").val()) {
    $("div.js-password-confirm").css("visibility", "hidden");
  }
};

$(() => {
  passwordConfirm();
});
