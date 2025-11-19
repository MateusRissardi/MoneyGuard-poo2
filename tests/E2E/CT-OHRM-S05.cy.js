describe('RT-OHRM-S05 - Aprovação e Contratação de Candidato', () => {
  
  beforeEach(() => {
    // 1. Resolução Full HD para evitar menu escondido
    cy.viewport(1920, 1080) 

    // 2. Login Administrativo
    cy.visit('https://opensource-demo.orangehrmlive.com/web/index.php/auth/login')
    cy.get('input[name="username"]').type('Admin')
    cy.get('input[name="password"]').type('admin123')
    cy.get('button[type="submit"]').click()
    cy.url().should('include', '/dashboard')
  })

  it('CT-OHRM-S05.1 - Criar candidato (com Vaga) e Contratar', () => {
    cy.wait(2000)
    cy.get('a[href*="Recruitment"]', { timeout: 10000 }).click({ force: true })

    cy.contains('button', 'Add').click()
    
    const uniqueName = 'Candidato_' + new Date().getTime()
    cy.get('input[name="firstName"]').type('Teste')
    cy.get('input[name="lastName"]').type(uniqueName)
    cy.get('input[placeholder="Type here"]').first().type('teste@cypress.com')

    cy.get('.oxd-select-text-input').click() 
    
    cy.get('.oxd-select-dropdown', { timeout: 5000 }).should('be.visible')
    cy.get('.oxd-select-option').last().click() 

    cy.get('button[type="submit"]').click()
    cy.contains('Successfully Saved').should('be.visible')

    cy.contains('.oxd-button', 'Shortlist', { timeout: 15000 })
      .should('be.visible')
      .click()
      
    cy.get('button[type="submit"]').click()
    cy.contains('Status: Shortlisted').should('be.visible')

    cy.contains('.oxd-button', 'Schedule Interview').click()
    
    cy.get('.oxd-input-group').contains('Interview Title').parent().parent().find('input').type('Entrevista Técnica')
    cy.get('input[placeholder="Type for hints..."]').type('a') 
    cy.wait(3000)
    cy.get('.oxd-autocomplete-option').first().click() 
    cy.get('input[placeholder="D, dd M yyyy"]').type('Fri, 28 Nov 2025')
    cy.get('button[type="submit"]').click()
    cy.contains('Status: Interview Scheduled').should('be.visible')

    cy.contains('.oxd-button', 'Mark Interview Passed').click()
    cy.get('button[type="submit"]').click()
    cy.contains('Status: Interview Passed').should('be.visible')

    cy.contains('.oxd-button', 'Offer Job').click()
    cy.get('button[type="submit"]').click()
    cy.contains('Status: Job Offered').should('be.visible')

    cy.contains('.oxd-button', 'Hire').click()
    cy.get('button[type="submit"]').click()
  
    cy.contains('Status: Hired', { timeout: 10000 }).should('be.visible')
  })
})