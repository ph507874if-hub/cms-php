
-- ==========================================
-- BANCO DE DADOS: LOJA_PEDRO
-- ==========================================

CREATE DATABASE IF NOT EXISTS loja_pedro
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE loja_pedro;


-- ==========================================
-- 1. TABELA: CATEGORIAS
-- ==========================================

CREATE TABLE categorias (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT,
    imagem VARCHAR(255),
    status TINYINT(1) NOT NULL DEFAULT 1,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY uk_categorias_nome (nome)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ==========================================
-- 2. TABELA: PRODUTOS
-- ==========================================

CREATE TABLE produtos (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    categoria_id INT UNSIGNED NOT NULL,
    nome VARCHAR(150) NOT NULL,
    descricao TEXT,
    imagem VARCHAR(255),
    preco DECIMAL(10,2) NOT NULL,
    estoque INT UNSIGNED NOT NULL DEFAULT 0,
    codigo_barras VARCHAR(50),
    status TINYINT(1) NOT NULL DEFAULT 1,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY uk_produtos_codigo_barras (codigo_barras),
    KEY idx_produtos_categoria (categoria_id),

    CONSTRAINT fk_produtos_categorias
        FOREIGN KEY (categoria_id)
        REFERENCES categorias(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT chk_produtos_preco
        CHECK (preco >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ==========================================
-- 3. TABELA: PEDIDOS
-- ==========================================

CREATE TABLE pedidos (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    usuario_id INT UNSIGNED NOT NULL,
    data_pedido DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    status ENUM(
        'pendente',
        'confirmado',
        'enviado',
        'entregue',
        'cancelado'
    ) NOT NULL DEFAULT 'pendente',
    subtotal DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    frete DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    desconto DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    observacao TEXT,

    PRIMARY KEY (id),
    KEY idx_pedidos_usuario (usuario_id),
    KEY idx_pedidos_data (data_pedido),

    CONSTRAINT fk_pedidos_usuarios
        FOREIGN KEY (usuario_id)
        REFERENCES usuarios(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT chk_pedidos_valores
        CHECK (
            subtotal >= 0 AND
            frete >= 0 AND
            desconto >= 0 AND
            total >= 0
        )
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ==========================================
-- 4. TABELA: ITENS_PEDIDO
-- ==========================================

CREATE TABLE itens_pedido (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    pedido_id INT UNSIGNED NOT NULL,
    produto_id INT UNSIGNED NOT NULL,
    quantidade INT UNSIGNED NOT NULL,
    preco_unitario DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,

    PRIMARY KEY (id),
    KEY idx_itens_pedido (pedido_id),
    KEY idx_itens_produto (produto_id),

    CONSTRAINT fk_itens_pedidos
        FOREIGN KEY (pedido_id)
        REFERENCES pedidos(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    CONSTRAINT fk_itens_produtos
        FOREIGN KEY (produto_id)
        REFERENCES produtos(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT chk_itens_quantidade
        CHECK (quantidade > 0),

    CONSTRAINT chk_itens_precos
        CHECK (preco_unitario >= 0 AND subtotal >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ==========================================
-- 5. TABELA: PAGAMENTOS
-- ==========================================

CREATE TABLE pagamentos (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    pedido_id INT UNSIGNED NOT NULL,
    forma_pagamento ENUM(
        'pix',
        'cartao_credito',
        'cartao_debito',
        'boleto',
        'dinheiro'
    ) NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    status ENUM(
        'pendente',
        'aprovado',
        'recusado',
        'estornado'
    ) NOT NULL DEFAULT 'pendente',
    codigo_transacao VARCHAR(150),
    data_pagamento DATETIME NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    KEY idx_pagamentos_pedido (pedido_id),

    CONSTRAINT fk_pagamentos_pedidos
        FOREIGN KEY (pedido_id)
        REFERENCES pedidos(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    CONSTRAINT chk_pagamentos_valor
        CHECK (valor > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ==========================================
-- FIM DO SCRIPT
-- ==========================================

-- Verificar as tabelas criadas
SHOW TABLES;
