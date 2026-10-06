<?php
/* ==========================================================================
   Início do layout do painel (cabeçalho, menu lateral e barra superior)
   --------------------------------------------------------------------------
   Antes de incluir este arquivo, a página pode definir:
     $titulo     → título da aba do navegador            (ex.: 'Usuários')
     $menuAtivo  → item destacado no menu lateral         (ex.: 'usuarios')
     $usaEditor  → true para carregar o editor rich text  (Summernote)
   ========================================================================== */

$titulo    = $titulo    ?? 'Painel';
$menuAtivo = $menuAtivo ?? '';
$usaEditor = $usaEditor ?? false;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($titulo) ?> | Painel CMS</title>
  <link rel="icon" type="image/svg+xml" href="../img/favicon.svg">

  <!-- Ícones (Bootstrap Icons) -->
  <link rel="stylesheet" href="../css/vendor/bootstrap-icons/bootstrap-icons.min.css">
<?php if ($usaEditor): ?>
  <!-- Editor rich text (Summernote Lite) -->
  <link rel="stylesheet" href="../css/vendor/summernote/summernote-lite.min.css">
<?php endif; ?>
  <!-- Estilos do CMS -->
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>

  <div class="app">

    <?php require __DIR__ . '/sidebar.php'; ?>

    <div class="main">

      <!-- ===== Barra superior ===== -->
      <header class="topbar">
        <button type="button" class="icon-btn topbar__toggle" data-sidebar-toggle aria-label="Abrir menu">
          <i class="bi bi-list"></i>
        </button>

        <?php if (ehAdmin()): ?>
        <form class="topbar__search" action="usuarios.php" method="get" role="search">
          <i class="bi bi-search"></i>
          <input type="search" class="form-control" name="busca" placeholder="Pesquisar usuários..." aria-label="Pesquisar usuários">
        </form>
        <?php endif; ?>

        <div class="topbar__actions">
          <!-- Parte 3 do projeto: aponte para o endereço do website -->
          <a href="#" class="icon-btn" title="Ver o site" target="_blank" rel="noopener"><i class="bi bi-globe2"></i></a>

          <!-- Dados de quem está logado ($usuarioLogado vem do auth.php) -->
          <div class="dropdown">
            <button type="button" class="user-menu" data-dropdown-toggle aria-haspopup="true" aria-expanded="false">
              <?= avatar($usuarioLogado, 'avatar-sm') ?>
              <span class="user-menu__info">
                <span class="user-menu__name"><?= e($usuarioLogado['nome']) ?></span>
                <span class="user-menu__role"><?= nomePerfil($usuarioLogado['perfil']) ?></span>
              </span>
              <i class="bi bi-chevron-down text-muted"></i>
            </button>
            <div class="dropdown__menu">
              <div class="dropdown__header">
                <strong><?= e($usuarioLogado['nome']) ?></strong>
                <span><?= e($usuarioLogado['email']) ?></span>
              </div>
              <a href="usuarios-editar.php?id=<?= $usuarioLogado['id'] ?>" class="dropdown__item"><i class="bi bi-person"></i> Meu perfil</a>
              <a href="usuarios-editar.php?id=<?= $usuarioLogado['id'] ?>#secao-acesso" class="dropdown__item"><i class="bi bi-key"></i> Alterar senha</a>
              <a href="../logout.php" class="dropdown__item dropdown__item--danger"><i class="bi bi-box-arrow-left"></i> Sair</a>
            </div>
          </div>
        </div>
      </header>
