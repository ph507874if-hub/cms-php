-- ============================================================================
--  PAINEL CMS — Banco de dados
--  Projeto Prático: CMS com PHP e MySQL (Parte 2)
-- ----------------------------------------------------------------------------
--  Como importar:
--    • phpMyAdmin: aba "Importar" → escolha este arquivo → Executar
--    • Terminal:   mysql -u root -p < sql/banco.sql
--
--  Compatível com MySQL 5.7+ / 8.x e MariaDB 10.x (XAMPP, Laragon, WAMP).
--
--  ATENÇÃO: este script APAGA e recria as tabelas do painel.
-- ============================================================================

-- Este arquivo está em UTF-8: avisa o servidor para os acentos não serem trocados
SET NAMES utf8mb4;

CREATE DATABASE IF NOT EXISTS cms
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE cms;

-- A tabela "filha" (que tem a chave estrangeira) precisa ser apagada primeiro
DROP TABLE IF EXISTS usuarios_tokens;
DROP TABLE IF EXISTS usuarios;


-- ----------------------------------------------------------------------------
--  Tabela: usuarios
--  Pessoas que podem acessar o painel administrativo.
-- ----------------------------------------------------------------------------
CREATE TABLE usuarios (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nome          VARCHAR(100) NOT NULL,
  email         VARCHAR(150) NOT NULL,
  usuario       VARCHAR(30)  NOT NULL,                  -- nome de usuário (ex.: maria.silva)
  senha         VARCHAR(255) NOT NULL,                  -- hash gerado por password_hash()
  telefone      VARCHAR(20)  NULL,
  foto          VARCHAR(255) NULL,                      -- caminho do arquivo (ex.: uploads/usuarios/abc.jpg)
  biografia     MEDIUMTEXT   NULL,                      -- HTML do editor rich text (pode conter imagens em Base64)
  perfil        ENUM('admin','editor') NOT NULL DEFAULT 'editor',
  status        TINYINT(1)   NOT NULL DEFAULT 1,        -- 1 = ativo, 0 = inativo
  criado_em     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizado_em DATETIME     NULL ON UPDATE CURRENT_TIMESTAMP,
  ultimo_acesso DATETIME     NULL,

  PRIMARY KEY (id),
  UNIQUE KEY uk_usuarios_email   (email),
  UNIQUE KEY uk_usuarios_usuario (usuario),
  KEY idx_usuarios_perfil_status (perfil, status)       -- acelera os filtros da listagem
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ----------------------------------------------------------------------------
--  Tabela: usuarios_tokens
--  Guarda os tokens da opção "Manter conectado" da tela de login.
--
--  O navegador recebe um cookie no formato  seletor:token
--    • seletor     → serve para localizar a linha (é guardado como está)
--    • token_hash  → hash SHA-256 do token (o token "puro" nunca fica no banco,
--                    assim como acontece com a senha)
--
--  ON DELETE CASCADE: ao excluir um usuário, seus tokens são apagados juntos.
-- ----------------------------------------------------------------------------
CREATE TABLE usuarios_tokens (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  usuario_id INT UNSIGNED NOT NULL,
  seletor    CHAR(24)     NOT NULL,
  token_hash CHAR(64)     NOT NULL,
  expira_em  DATETIME     NOT NULL,
  criado_em  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  UNIQUE KEY uk_tokens_seletor (seletor),
  KEY idx_tokens_usuario (usuario_id),
  CONSTRAINT fk_tokens_usuario
    FOREIGN KEY (usuario_id) REFERENCES usuarios (id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ----------------------------------------------------------------------------
--  Usuário administrador inicial
--    E-mail: admin@cms.com
--    Senha:  123456        (troque depois do primeiro acesso!)
--
--  O valor da coluna "senha" foi gerado no PHP com:
--    echo password_hash('123456', PASSWORD_DEFAULT);
-- ----------------------------------------------------------------------------
INSERT INTO usuarios (nome, email, usuario, senha, telefone, biografia, perfil, status)
VALUES (
  'Administrador do Sistema',
  'admin@cms.com',
  'admin',
  '$2y$10$d0QBjfVCxWsp0hbO6umz6.JHdFhXnZ1qjo8joywe4azH8szA9RECu',
  NULL,
  '<p>Conta de <strong>administrador</strong> criada na instalação do sistema.</p>',
  'admin',
  1
);
