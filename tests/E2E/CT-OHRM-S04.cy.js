describe('OrangeHRM Demo - Solicitação de Férias (RT-OHRM-S04)', () => {
  const baseUrl = 'https://opensource-demo.orangehrmlive.com/web/index.php/auth/login'

  beforeEach(() => {
    cy.visit(baseUrl)
    cy.get('input[name="username"]').type('Danieli')
    cy.get('input[name="password"]').type('123teste')
    cy.get('button[type="submit"]').click()
    cy.url().should('include', '/dashboard')
  })

it('CT-OHRM-S04.1 - Solicitação de férias válida deve ser registrada com sucesso', () => {
    cy.intercept('GET', '**/leave/leave-types/eligible').as('carregarTiposLicenca')

    cy.contains('Leave').click()
    cy.contains('Apply').click()

    cy.wait('@carregarTiposLicenca')

    cy.contains('.oxd-input-group', 'Leave Type')
      .find('.oxd-select-wrapper')
      .should('be.visible')
      .click()
    
    cy.contains('.oxd-select-option', 'US - Vacation').click()

    const hoje = new Date()
    const amanha = new Date(hoje)
    amanha.setDate(hoje.getDate() + 1)
    const depoisAmanha = new Date(hoje)
    depoisAmanha.setDate(hoje.getDate() + 2)

    const dataInicio = amanha.toISOString().split('T')[0]
    const dataFim = depoisAmanha.toISOString().split('T')[0]

    cy.get('input[placeholder="yyyy-dd-mm"]').first().click().clear().type(dataInicio + '{enter}')

    cy.wait(500)
    cy.get('input[placeholder="yyyy-dd-mm"]').last().click().clear().type(dataFim + '{enter}')
    
    cy.contains('button', 'Apply').click({ force: true })

    cy.contains('Successfully Saved').should('be.visible')
    cy.contains('My Leave').click()
    cy.contains('Pending Approval').should('exist')
  })
})