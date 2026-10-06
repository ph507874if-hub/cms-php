-- ============================================================================
--  PAINEL CMS — Dados de exemplo (OPCIONAL)
-- ----------------------------------------------------------------------------
--  Insere usuários fictícios para testar a listagem, a busca, os filtros e a
--  paginação. Importe DEPOIS do arquivo banco.sql.
--
--  Todos os usuários abaixo usam a senha:  123456
--  Para testar o perfil "Editor", entre com:  editor@cms.com / 123456
-- ============================================================================

SET NAMES utf8mb4;

USE cms;

INSERT INTO usuarios (nome, email, usuario, senha, telefone, biografia, perfil, status, criado_em) VALUES
('Ana Beatriz Souza',   'ana.souza@cms.com',         'ana.souza',         '$2y$10$d0QBjfVCxWsp0hbO6umz6.JHdFhXnZ1qjo8joywe4azH8szA9RECu', '(77) 98801-1020', '<p>Coordenadora de comunicação e responsável pelo painel.</p>', 'admin',  1, '2026-03-12 09:15:00'),
('Carlos Eduardo Lima', 'editor@cms.com',            'carlos.lima',       '$2y$10$d0QBjfVCxWsp0hbO6umz6.JHdFhXnZ1qjo8joywe4azH8szA9RECu', '(77) 98812-4410', '<h3>Sobre mim</h3><p>Sou <strong>jornalista</strong> e cuido da revisão dos textos publicados no site. Trabalho com produção de conteúdo há mais de <em>oito anos</em>.</p><ul><li>Revisão e padronização de textos</li><li>Publicação de notícias e eventos</li><li>Atualização da página institucional</li></ul><blockquote>Conteúdo bom é conteúdo atualizado.</blockquote>', 'editor', 1, '2026-03-18 14:02:00'),
('Fernanda Oliveira',   'fernanda.oliveira@cms.com', 'fernanda.oliveira', '$2y$10$d0QBjfVCxWsp0hbO6umz6.JHdFhXnZ1qjo8joywe4azH8szA9RECu', NULL,              '<p>Redatora de notícias.</p>',                                  'editor', 0, '2026-04-02 10:40:00'),
('João Pedro Santos',   'joao.santos@cms.com',       'joao.santos',       '$2y$10$d0QBjfVCxWsp0hbO6umz6.JHdFhXnZ1qjo8joywe4azH8szA9RECu', '(77) 99134-7788', '<p>Suporte técnico do site.</p>',                               'admin',  1, '2026-04-15 08:22:00'),
('Mariana Costa',       'mariana.costa@cms.com',     'mariana.costa',     '$2y$10$d0QBjfVCxWsp0hbO6umz6.JHdFhXnZ1qjo8joywe4azH8szA9RECu', '(77) 98877-3021', '<p>Fotógrafa e editora de imagens.</p>',                        'editor', 1, '2026-05-28 16:05:00'),
('Rafael Almeida',      'rafael.almeida@cms.com',    'rafael.almeida',    '$2y$10$d0QBjfVCxWsp0hbO6umz6.JHdFhXnZ1qjo8joywe4azH8szA9RECu', NULL,              '<p>Colaborador eventual.</p>',                                  'editor', 0, '2026-06-09 11:30:00'),
('Juliana Ferreira',    'juliana.ferreira@cms.com',  'juliana.ferreira',  '$2y$10$d0QBjfVCxWsp0hbO6umz6.JHdFhXnZ1qjo8joywe4azH8szA9RECu', '(77) 99902-6654', '<p>Responsável pela agenda de eventos.</p>',                    'editor', 1, '2026-07-21 13:48:00'),
('Lucas Martins',       'lucas.martins@cms.com',     'lucas.martins',     '$2y$10$d0QBjfVCxWsp0hbO6umz6.JHdFhXnZ1qjo8joywe4azH8szA9RECu', '(77) 98120-4477', '<p>Desenvolvedor do website.</p>',                              'admin',  1, '2026-09-04 09:00:00'),
('Patrícia Gomes',      'patricia.gomes@cms.com',    'patricia.gomes',    '$2y$10$d0QBjfVCxWsp0hbO6umz6.JHdFhXnZ1qjo8joywe4azH8szA9RECu', NULL,              '<p>Revisora de textos.</p>',                                    'editor', 1, '2026-09-10 15:12:00'),
('Bruno Carvalho',      'bruno.carvalho@cms.com',    'bruno.carvalho',    '$2y$10$d0QBjfVCxWsp0hbO6umz6.JHdFhXnZ1qjo8joywe4azH8szA9RECu', '(77) 99655-1203', '<p>Editor da seção de esportes.</p>',                           'editor', 1, '2026-09-17 10:27:00'),
('Camila Rocha',        'camila.rocha@cms.com',      'camila.rocha',      '$2y$10$d0QBjfVCxWsp0hbO6umz6.JHdFhXnZ1qjo8joywe4azH8szA9RECu', NULL,              '<p>Estagiária de comunicação.</p>',                             'editor', 0, '2026-09-24 08:55:00');
