describe('OrangeHRM Demo - Cadastro de Novo Empregado (RT-OHRM-S03)', () => {
  const baseUrl = 'https://opensource-demo.orangehrmlive.com/web/index.php/auth/login'

  beforeEach(() => {
    cy.visit(baseUrl)
    cy.get('input[name="username"]').type('Admin')
    cy.get('input[name="password"]').type('admin123')
    cy.get('button[type="submit"]').click()
    cy.url().should('include', '/dashboard')
    cy.contains('PIM').click()
    cy.contains('Add Employee').click()
  })

  it('CT-OHRM-S03.1 - Cadastro com campos mínimos válidos', () => {
    cy.get('input[name="firstName"]').type('Fulano')
    cy.get('input[name="lastName"]').type('de Tal')
    cy.get('button[type="submit"]').click()

    cy.contains('Successfully Saved').should('be.visible')
  })

  it('CT-OHRM-S03.2 - Deve exibir erro se "First Name" estiver vazio', () => {
    cy.get('input[name="lastName"]').type('SemNome')
    cy.get('button[type="submit"]').click()

    cy.contains('Required').should('be.visible')
  })

  it('CT-OHRM-S03.3 - Deve exibir erro para ID duplicado', () => {
    cy.get('input[name="firstName"]').type('Duplicado')
    cy.get('input[name="lastName"]').type('Teste')

    cy.get('input.oxd-input').eq(4).clear().type('0100')

    cy.get('button[type="submit"]').click()
    cy.contains('Employee Id already exists').should('be.visible')
  })
})
