describe('OrangeHRM Demo - Testes de Login', () => {
  const baseUrl = 'https://opensource-demo.orangehrmlive.com/web/index.php/auth/login'

  beforeEach(() => {
    cy.visit(baseUrl)
  })

  it('CT-OHRM-S01.1 - Usuário válido + senha inválida', () => {
    cy.get('input[name="username"]').type('Admin')
    cy.get('input[name="password"]').type('123invalida')
    cy.get('button[type="submit"]').click()

    cy.get('.oxd-alert-content-text')
      .should('be.visible')
      .and('contain.text', 'Invalid credentials')
  })

  it('CT-OHRM-S01.2 - Usuário inválido + senha válida', () => {
    cy.get('input[name="username"]').type('UserInvalido')
    cy.get('input[name="password"]').type('admin123')
    cy.get('button[type="submit"]').click()

    cy.get('.oxd-alert-content-text')
      .should('be.visible')
      .and('contain.text', 'Invalid credentials')
  })

  it('CT-OHRM-S01.3 - Campos vazios', () => {
    cy.get('button[type="submit"]').click()

    cy.get('span').contains('Required').should('be.visible')
    cy.get('span').contains('Required').should('be.visible')
  })

  it('CT-OHRM-S01.4 - Credenciais válidas (Login com sucesso)', () => {
    cy.get('input[name="username"]').type('Admin')
    cy.get('input[name="password"]').type('admin123')
    cy.get('button[type="submit"]').click()

    cy.url().should('include', '/dashboard')
    cy.get('h6').should('contain.text', 'Dashboard')
  })
})
