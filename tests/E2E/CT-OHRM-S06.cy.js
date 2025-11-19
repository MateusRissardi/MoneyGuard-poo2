describe('RT-OHRM-S06 - Validação de Datas de Férias (Regra Negativa)', () => {

  beforeEach(() => {
    cy.viewport(1920, 1080) 
    
    cy.visit('https://opensource-demo.orangehrmlive.com/web/index.php/auth/login')
    cy.get('input[name="username"]').type('Danieli') 
    cy.get('input[name="password"]').type('123teste')
    cy.get('button[type="submit"]').click()
    cy.url().should('include', '/dashboard')
  })

  it('CT-OHRM-S06.1 - Impedir solicitação de férias com data final anterior à data inicial', () => {
    cy.intercept('GET', '**/leave/leave-types/eligible').as('carregarTipos')

    cy.visit('https://opensource-demo.orangehrmlive.com/web/index.php/leave/applyLeave')
    
    cy.wait('@carregarTipos')

    cy.contains('.oxd-input-group', 'Leave Type').find('.oxd-select-wrapper').click()
    cy.contains('.oxd-select-option', 'US - Vacation').click()
    
    cy.wait(1000)

    const hoje = new Date()
    const amanha = new Date(hoje)
    amanha.setDate(hoje.getDate() + 1)

    const dataInicio = amanha.toISOString().split('T')[0]
    const dataFimInvalida = hoje.toISOString().split('T')[0]

    cy.get('.oxd-date-input input').first().click().clear().type(dataInicio + '{enter}')

    cy.wait(500)

    cy.get('.oxd-date-input input').last().click().clear().type(dataFimInvalida + '{enter}')

    cy.contains('span.oxd-input-field-error-message', 'To date should be after from date')
      .should('be.visible')

    cy.contains('button', 'Apply').click({ force: true })

    cy.contains('Successfully Submitted').should('not.exist')
    
    cy.contains('To date should be after from date').should('be.visible')
  })
})