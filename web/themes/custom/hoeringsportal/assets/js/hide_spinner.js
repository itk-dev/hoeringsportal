/**
 * @file
 * Set timer for iframe.
 */

const timer = setInterval(function () {
  const element = document.querySelector(".node--type-hearing iframe");
  if (!element) {
    clearInterval(timer);
    return;
  }
  const compStyles = window.getComputedStyle(element);
  const height = parseInt(compStyles.height, 10);
  if (height > 300) {
    document.querySelector(".spinner").remove();
    clearInterval(timer);
  }
}, 100);
