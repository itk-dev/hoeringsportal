/* eslint-env jquery */
(function ($) {
  document.addEventListener('keydown', function (event) {
    if (document.activeElement.id === 'edit-keys') {
      if (event.key === 'Enter') {
        event.preventDefault()
      }
    }
  })
})(jQuery)

// Focus search input when collapse shown
const collapseSearch = document.getElementById('collapseSearch')
collapseSearch.addEventListener('shown.bs.collapse', event => {
  document.querySelector('#search-block-form .form-search').focus()
})
