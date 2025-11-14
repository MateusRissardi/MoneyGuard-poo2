DROP TABLE IF EXISTS "ExpenseSplit", "Settlement", "Expense", "GroupMember", "Group", "User";

-- Tabela User (ED01)
CREATE TABLE "User" (
    "id_usuario" SERIAL PRIMARY KEY,
    "nome" VARCHAR(255) NOT NULL,
    "email" VARCHAR(255) UNIQUE NOT NULL,
    "senha_hash" VARCHAR(255) NOT NULL,
    "data_nascimento" DATE
);

-- Tabela Group (ED02)
CREATE TABLE "Group" (
    "id_grupo" SERIAL PRIMARY KEY,
    "nome_grupo" VARCHAR(255) NOT NULL,
    "id_admin" INTEGER NOT NULL REFERENCES "User"("id_usuario"),
    "codigo_convite" VARCHAR(50) UNIQUE
);

-- Tabela GroupMember (ED03)
CREATE TABLE "GroupMember" (
    "id_usuario" INTEGER NOT NULL REFERENCES "User"("id_usuario"),
    "id_grupo" INTEGER NOT NULL REFERENCES "Group"("id_grupo"),
    "data_entrada" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY ("id_usuario", "id_grupo")
);

-- Tabela Expense (ED04)
CREATE TABLE "Expense" (
    "id_despesa" SERIAL PRIMARY KEY,
    "id_grupo" INTEGER NOT NULL REFERENCES "Group"("id_grupo") ON DELETE CASCADE,
    "id_pagador" INTEGER NOT NULL REFERENCES "User"("id_usuario"),
    "valor_total" DECIMAL(10, 2) NOT NULL,
    "categoria" VARCHAR(100) NOT NULL,
    "data_despesa" DATE NOT NULL,
    "descricao" TEXT,
    "url_recibo" VARCHAR(255),
    "tipo_divisao" VARCHAR(50)
);

-- Tabela ExpenseSplit (ED05)
CREATE TABLE "ExpenseSplit" (
    "id_divisao" SERIAL PRIMARY KEY,
    "id_despesa" INTEGER NOT NULL REFERENCES "Expense"("id_despesa") ON DELETE CASCADE,
    "id_participante" INTEGER NOT NULL REFERENCES "User"("id_usuario"),
    "valor_devido" DECIMAL(10, 2) NOT NULL
);

-- Tabela Settlement (ED06)
CREATE TABLE "Settlement" (
    "id_acerto" SERIAL PRIMARY KEY,
    "id_grupo" INTEGER NOT NULL REFERENCES "Group"("id_grupo") ON DELETE CASCADE,
    "id_devedor" INTEGER NOT NULL REFERENCES "User"("id_usuario"),
    "id_credor" INTEGER NOT NULL REFERENCES "User"("id_usuario"),
    "valor" DECIMAL(10, 2) NOT NULL,
    "data_pagamento" DATE NOT NULL,
    CHECK ("id_devedor" <> "id_credor")
);

ALTER TABLE "Group" ADD COLUMN "codigo_data_geracao" TIMESTAMP;

ALTER TABLE "User" ADD COLUMN "ultimo_grupo_acessado_id" INTEGER DEFAULT NULL;