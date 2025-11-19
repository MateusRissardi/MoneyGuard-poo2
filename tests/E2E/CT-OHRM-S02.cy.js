describe('OrangeHRM Demo - Validação de Força da Senha (RT-OHRM-S02)', () => {
  const baseUrl = 'https://opensource-demo.orangehrmlive.com/web/index.php/auth/login'

  beforeEach(() => {
    cy.visit(baseUrl)
    cy.get('input[name="username"]').type('Admin')
    cy.get('input[name="password"]').type('admin123')
    cy.get('button[type="submit"]').click()
    cy.url().should('include', '/dashboard')
    
    cy.contains('PIM').click()
    cy.contains('Add Employee').click()
    
    cy.get('.oxd-switch-input').click()
    cy.contains('label', 'Username').should('be.visible') 
  })

  it('CT-OHRM-S02.1 - Senha com menos de 7 caracteres deve ser rejeitada', () => {
    cy.get('input[name="firstName"]').type('Maria')
    cy.get('input[name="lastName"]').type('Teste')

    cy.contains('.oxd-label', 'Username').parents('.oxd-input-group').find('input').type('senhaMenor')
    cy.contains('.oxd-label', 'Password').parents('.oxd-input-group').find('input').type('Senha1')
    cy.contains('.oxd-label', 'Confirm Password').parents('.oxd-input-group').find('input').type('Senha1')

    cy.get('button[type="submit"]').click()

    cy.contains('Should have at least 7 characters').should('be.visible')
  })

  it('CT-OHRM-S02.2 - Senha de 7 caracteres sem número deve ser rejeitada', () => {
    cy.get('input[name="firstName"]').type('Joao')
    cy.get('input[name="lastName"]').type('Teste')

    // CORREÇÃO AQUI
    cy.contains('.oxd-label', 'Username').parents('.oxd-input-group').find('input').type('senhaSemNumero')
    cy.contains('.oxd-label', 'Password').parents('.oxd-input-group').find('input').type('SenhaAbc')
    cy.contains('.oxd-label', 'Confirm Password').parents('.oxd-input-group').find('input').type('SenhaAbc')

    cy.get('button[type="submit"]').click()

    cy.contains('Your password must contain minimum 1 number').should('be.visible')
  })

  it('CT-OHRM-S02.3 - Senha válida deve ser aceita', () => {
    cy.get('input[name="firstName"]').type('Fulano')
    cy.get('input[name="lastName"]').type('Valido')

    // CORREÇÃO AQUI
    cy.contains('.oxd-label', 'Username').parents('.oxd-input-group').find('input').type('userValido')
    cy.contains('.oxd-label', 'Password').parents('.oxd-input-group').find('input').type('Admin123')
    cy.contains('.oxd-label', 'Confirm Password').parents('.oxd-input-group').find('input').type('Admin123')

    cy.get('button[type="submit"]').click()

    cy.contains('Successfully Saved').should('be.visible')
  })
})