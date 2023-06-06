/* global describe it cy before */
describe('Citizen proposal', () => {
  it('Create proposal', () => {
    cy.viewport(1000, 1000)

    cy.visit('/citizen_proposal/add')

    cy.getByLabel('Email')
      .type('borger87@eksemple.dk')

    cy.getByLabel('Overskrift')
      .type('Sådan løser vi alle problemer!')

    cy.getByLabel('Proposal')
      .type('Jeg synes det er et stort problem at …')

    cy.getByLabel('Remarks')
      .type('Dette vil også løse problemet med at …')

    cy.get('.button').contains('Create proposal')
      .click()

    cy.url()
      .should('include', '/citizen_proposal/approve')

    cy.get('input[type="submit"]').contains('Approve proposal')
    cy.get('a').contains('Edit proposal')
    cy.get('input[type="submit"]').contains('Cancel proposal')

    cy
      .contains('borger87@eksemple.dk')

    cy.get('a').contains('Edit proposal')
      .click()

    cy.getByLabel('Email')
      .should('have.value', 'borger87@eksemple.dk')
      .clear()
      .type('borger87@eksempel.dk')

    cy.get('.button').contains('Update proposal')
      .click()

    cy
      .contains('borger87@eksempel.dk')

    cy.get('input[type="submit"]').contains('Approve proposal')
      .click()

    cy.contains('Thank you for you submission.')
  })

  it('Cancel proposal', () => {
    cy.viewport(1000, 1000)

    cy.visit('/citizen_proposal/add')

    cy.getByLabel('Email')
      .type('borger87@eksemple.dk')

    cy.getByLabel('Overskrift')
      .type('Sådan løser vi alle problemer!')

    cy.getByLabel('Proposal')
      .type('Jeg synes det er et stort problem at …')

    cy.getByLabel('Remarks')
      .type('Dette vil også løse problemet med at …')

    cy.get('.button').contains('Create proposal')
      .click()

    cy.url()
      .should('include', '/citizen_proposal/approve')

    cy.get('input[type="submit"]').contains('Cancel proposal')
      .click()

    cy.contains('Your submission has been cancelled.');
  })
})
