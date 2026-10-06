<?php
/* ==========================================================================
   Menu lateral (incluído pelo header.php)
   $menuAtivo indica qual item fica destacado.
   ========================================================================== */

/** Atributos do link do menu: destaca o item da página atual. */
function linkMenu(string $item, string $menuAtivo): string
{
    return $item === $menuAtivo
        ? 'class="sidebar__link is-active" aria-current="page"'
        : 'class="sidebar__link"';
}

// Total de usuários exibido ao lado do item "Usuários"
$totalUsuariosMenu = ehAdmin() ? (int) $pdo->query('SELECT COUNT(*) FROM usuarios')->fetchColumn() : 0;
?>
    <aside class="sidebar" id="sidebar">
      <a href="dashboard.php" class="sidebar__brand">
        <img src="../img/logo.svg" alt="">
        <span>Painel CMS <small>Gerenciador de conteúdo</small></span>
      </a>

      <nav class="sidebar__nav" aria-label="Menu principal">
        <p class="sidebar__label">Principal</p>
        <ul class="sidebar__menu">
          <li><a href="dashboard.php" <?= linkMenu('dashboard', $menuAtivo) ?>><i class="bi bi-grid-1x2"></i> Dashboard</a></li>
        </ul>

        <?php if (ehAdmin()): // só administradores gerenciam usuários ?>
        <p class="sidebar__label">Administração</p>
        <ul class="sidebar__menu">
          <li><a href="usuarios.php" <?= linkMenu('usuarios', $menuAtivo) ?>><i class="bi bi-people"></i> Usuários <span class="badge badge-primary"><?= $totalUsuariosMenu ?></span></a></li>
        </ul>
        <?php endif; ?>

        <!-- =============================================================
             CONTEÚDO DO SITE: substitua os itens abaixo pelas entidades
             do tema da sua dupla (ex.: Notícias, Produtos, Eventos...).
             Cada item deve apontar para a listagem do CRUD correspondente
             e usar linkMenu('nome-do-item', $menuAtivo) para o destaque.
             ============================================================= -->
        <p class="sidebar__label">Conteúdo do site</p>
        <ul class="sidebar__menu">
          <li><a href="#" <?= linkMenu('categorias', $menuAtivo) ?>><i class="bi bi-tags"></i> Categorias <span class="badge badge-gray">exemplo</span></a></li>
          <li><a href="#" <?= linkMenu('publicacoes', $menuAtivo) ?>><i class="bi bi-file-earmark-richtext"></i> Publicações <span class="badge badge-gray">exemplo</span></a></li>
        </ul>

        <p class="sidebar__label">Sistema</p>
        <ul class="sidebar__menu">
          <!-- Parte 3 do projeto: aponte para o endereço do website -->
          <li><a href="#" class="sidebar__link" target="_blank" rel="noopener"><i class="bi bi-box-arrow-up-right"></i> Ver o site</a></li>
          <li><a href="../logout.php" class="sidebar__link"><i class="bi bi-box-arrow-left"></i> Sair</a></li>
        </ul>
      </nav>

      <div class="sidebar__footer">
        <div class="sidebar__help">
          <strong>Precisa de ajuda?</strong>
          Consulte o README do projeto para ver como criar os CRUDs do seu tema.
        </div>
      </div>
    </aside>
    <div class="sidebar-backdrop"></div>
