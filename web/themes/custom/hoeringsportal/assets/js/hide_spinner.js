/**
 * @file
 * Set timer for iframe.
 */

var timer = setInterval(function () {
  var element = document.querySelector('.node--type-hearing iframe')
  if (!element) {
    clearInterval(timer)
    return
  }
  var compStyles = window.getComputedStyle(element)
  var height = parseInt(compStyles.height, 10)
  if (height > 300) {
    document.querySelector('.spinner').remove()
    clearInterval(timer)
  }
}, 100)
