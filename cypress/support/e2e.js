// @see https://www.npmjs.com/package/cypress-mailhog
import 'cypress-mailhog'

// @see https://glebbahmutov.com/cypress-examples/recipes/form-input-by-label.html#reusable-function
Cypress.Commands.add('getByLabel', (label) => {
  // cy.log('**getByLabel**')

  cy.contains('label', label)
    .invoke('attr', 'for')
    .then((id) => {
      cy.get('#' + id)
    })
})
