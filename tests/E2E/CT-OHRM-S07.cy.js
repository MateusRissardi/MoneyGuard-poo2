describe('RT-OHRM-S07 - Busca de Empregado Inexistente (PIM)', () => {

  beforeEach(() => {
    cy.viewport(1920, 1080) 
    
    // Login como Admin
    cy.visit('https://opensource-demo.orangehrmlive.com/web/index.php/auth/login')
    cy.get('input[name="username"]').type('Admin')
    cy.get('input[name="password"]').type('admin123')
    cy.get('button[type="submit"]').click()
    cy.url().should('include', '/dashboard')
  })

  it('CT-OHRM-S07.1 - Exibir mensagem "No Records Found" ao buscar por empregado inexistente', () => {
    cy.intercept('GET', '**/pim/employees*').as('buscaAPI')

    cy.get('input[placeholder="Search"]').type('PIM')
    cy.get('.oxd-main-menu-item').click()

    cy.contains('h6', 'PIM').should('be.visible')

    const nomeInexistente = 'XYZ_Nome_Nao_Existe_' + new Date().getTime()

    cy.contains('.oxd-label', 'Employee Name')
      .parents('.oxd-input-group')
      .find('input')
      .type(nomeInexistente)

    cy.get('.oxd-topbar-header-title').click()

    cy.get('button[type="submit"]').click()

    cy.wait('@buscaAPI')

    cy.contains('No Records Found').should('be.visible')

    cy.get('.oxd-table-card').should('not.exist')
  })
})