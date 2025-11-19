describe('RT-OHRM-S08 - Validação de Permissões de Acesso (Security)', () => {

  beforeEach(() => {
    cy.viewport(1920, 1080)
    cy.visit('https://opensource-demo.orangehrmlive.com/web/index.php/auth/login')
    
    cy.get('input[name="username"]').type('Danieli')
    cy.get('input[name="password"]').type('123teste')
    cy.get('button[type="submit"]').click()
    
   
    cy.url().should('include', '/dashboard')
  })

  it('CT-OHRM-S08.1 - Impedir acesso de usuário ESS à tela de Admin', () => {
  
    cy.visit('https://opensource-demo.orangehrmlive.com/web/index.php/admin/viewSystemUsers', { 
      failOnStatusCode: false 
    })

    cy.get('.oxd-alert--error').should('be.visible')
   
    cy.contains('Credential Required').should('be.visible') 

    cy.get('.oxd-table').should('not.exist')
  })
})