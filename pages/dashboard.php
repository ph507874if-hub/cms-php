<?php
/* ==========================================================================
   Dashboard — página inicial do painel
   ========================================================================== */

require_once '../includes/auth.php';   // protege a página
require_once '../config/conexao.php';
require_once '../includes/usuarios-funcoes.php'; // badgePerfil() e badgeStatus()

// Estatísticas: uma única consulta devolve os três totais
$totais = $pdo->query(
    "SELECT COUNT(*)                                                         AS total,
            COALESCE(SUM(status = 1), 0)                                     AS ativos,
            COALESCE(SUM(criado_em >= DATE_FORMAT(CURDATE(), '%Y-%m-01')), 0) AS no_mes
       FROM usuarios"
)->fetch();

$percentualAtivos = $totais['total'] > 0 ? round($totais['ativos'] * 100 / $totais['total']) : 0;

// Últimos usuários cadastrados
$recentes = $pdo->query(
    'SELECT id, nome, email, foto, perfil, status, criado_em
       FROM usuarios
      ORDER BY criado_em DESC, id DESC
      LIMIT 4'
)->fetchAll();

$titulo    = 'Dashboard';
$menuAtivo = 'dashboard';
require_once '../includes/header.php';
?>

      <main class="content">

        <div class="page-header">
          <div>
            <h1>Dashboard</h1>
            <p>Visão geral do seu site e do painel administrativo.</p>
          </div>
          <?php if (ehAdmin()): ?>
          <div class="page-header__actions">
            <a href="usuarios-cadastrar.php" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Novo usuário</a>
          </div>
          <?php endif; ?>
        </div>

        <?php exibirMensagem(); ?>

        <section class="welcome">
          <h2>Olá, <?= e(primeiroNome($usuarioLogado['nome'])) ?>! 👋</h2>
          <p>Este é o painel do seu CMS. Aqui você gerencia os usuários e todo o conteúdo que aparece no website.</p>
          <?php if (ehAdmin()): ?>
            <a href="usuarios.php" class="btn btn-light btn-sm"><i class="bi bi-people"></i> Gerenciar usuários</a>
          <?php else: ?>
            <a href="usuarios-editar.php?id=<?= $usuarioLogado['id'] ?>" class="btn btn-light btn-sm"><i class="bi bi-person"></i> Meu perfil</a>
          <?php endif; ?>
        </section>

        <section class="stats">
          <div class="card stat">
            <div class="stat__icon stat__icon--primary"><i class="bi bi-people"></i></div>
            <div>
              <div class="stat__label">Usuários cadastrados</div>
              <div class="stat__value"><?= (int) $totais['total'] ?></div>
              <?php if ($totais['no_mes'] > 0): ?>
                <div class="stat__trend stat__trend--up"><i class="bi bi-arrow-up-short"></i> <?= (int) $totais['no_mes'] ?> este mês</div>
              <?php else: ?>
                <div class="stat__trend text-muted">Nenhum este mês</div>
              <?php endif; ?>
            </div>
          </div>
          <div class="card stat">
            <div class="stat__icon stat__icon--success"><i class="bi bi-person-check"></i></div>
            <div>
              <div class="stat__label">Usuários ativos</div>
              <div class="stat__value"><?= (int) $totais['ativos'] ?></div>
              <div class="stat__trend text-muted"><?= $percentualAtivos ?>% do total</div>
            </div>
          </div>

          <!-- Cartões de exemplo: troque pelas entidades do tema da sua dupla.
               Ex.: $pdo->query('SELECT COUNT(*) FROM noticias')->fetchColumn() -->
          <div class="card stat">
            <div class="stat__icon stat__icon--warning"><i class="bi bi-file-earmark-richtext"></i></div>
            <div>
              <div class="stat__label">Publicações (exemplo)</div>
              <div class="stat__value">0</div>
              <div class="stat__trend text-muted">Módulo do tema da dupla</div>
            </div>
          </div>
          <div class="card stat">
            <div class="stat__icon stat__icon--info"><i class="bi bi-tags"></i></div>
            <div>
              <div class="stat__label">Categorias (exemplo)</div>
              <div class="stat__value">0</div>
              <div class="stat__trend text-muted">Módulo do tema da dupla</div>
            </div>
          </div>
        </section>

        <div class="grid-2">
          <section class="card">
            <div class="card__header">
              <div>
                <h2 class="card__title">Usuários recentes</h2>
                <p class="card__subtitle">Últimos cadastros realizados no painel</p>
              </div>
              <?php if (ehAdmin()): ?>
              <a href="usuarios.php" class="btn btn-light btn-sm">Ver todos <i class="bi bi-arrow-right"></i></a>
              <?php endif; ?>
            </div>
            <div class="table-responsive">
              <table class="table table--stack">
                <thead>
                  <tr><th>Usuário</th><th>Perfil</th><th>Status</th><th>Cadastro</th></tr>
                </thead>
                <tbody>
                  <?php foreach ($recentes as $u): ?>
                  <tr>
                    <td class="col-user" data-label="">
                      <div class="user-cell">
                        <?= avatar($u, 'avatar-sm') ?>
                        <div><span class="user-cell__name"><?= e($u['nome']) ?></span><span class="user-cell__email"><?= e($u['email']) ?></span></div>
                      </div>
                    </td>
                    <td data-label="Perfil"><?= badgePerfil($u['perfil']) ?></td>
                    <td data-label="Status"><?= badgeStatus($u['status']) ?></td>
                    <td data-label="Cadastro" class="text-muted"><?= formatarData($u['criado_em']) ?></td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </section>

          <section class="card">
            <div class="card__header">
              <h2 class="card__title">Ações rápidas</h2>
            </div>
            <div class="card__body quick-links">
              <?php if (ehAdmin()): ?>
              <a href="usuarios-cadastrar.php" class="quick-link">
                <i class="bi bi-person-plus"></i>
                <div><strong>Cadastrar usuário</strong><span>Novo administrador ou editor</span></div>
                <i class="bi bi-chevron-right"></i>
              </a>
              <a href="usuarios.php" class="quick-link">
                <i class="bi bi-list-ul"></i>
                <div><strong>Listar usuários</strong><span>Editar ou excluir cadastros</span></div>
                <i class="bi bi-chevron-right"></i>
              </a>
              <?php else: ?>
              <a href="usuarios-editar.php?id=<?= $usuarioLogado['id'] ?>" class="quick-link">
                <i class="bi bi-person"></i>
                <div><strong>Meu perfil</strong><span>Atualizar meus dados e minha senha</span></div>
                <i class="bi bi-chevron-right"></i>
              </a>
              <?php endif; ?>
              <!-- Exemplos: aponte para as páginas do tema da sua dupla -->
              <a href="#" class="quick-link">
                <i class="bi bi-file-earmark-plus"></i>
                <div><strong>Nova publicação</strong><span>Módulo do tema da dupla</span></div>
                <i class="bi bi-chevron-right"></i>
              </a>
              <a href="#" class="quick-link" target="_blank" rel="noopener">
                <i class="bi bi-globe2"></i>
                <div><strong>Visitar o site</strong><span>Veja as alterações publicadas</span></div>
                <i class="bi bi-chevron-right"></i>
              </a>
            </div>
          </section>
        </div>

      </main>

<?php require_once '../includes/footer.php'; ?>
