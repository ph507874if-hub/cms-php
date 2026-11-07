<?php
require_once '../includes/auth.php';
require_once '../config/conexao.php';

$totais = [
    'produtos' => (int) $pdo->query('SELECT COUNT(*) FROM produtos')->fetchColumn(),
    'categorias' => (int) $pdo->query('SELECT COUNT(*) FROM categorias')->fetchColumn(),
    'usuarios' => (int) $pdo->query('SELECT COUNT(*) FROM usuarios')->fetchColumn(),
    'pedidos' => (int) $pdo->query('SELECT COUNT(*) FROM pedidos')->fetchColumn(),
];

$ultimosProdutos = $pdo->query(
    'SELECT p.id, p.nome, p.preco, p.estoque, c.nome AS categoria
       FROM produtos p
       INNER JOIN categorias c ON c.id = p.categoria_id
      ORDER BY p.criado_em DESC, p.id DESC
      LIMIT 5'
)->fetchAll();

$titulo = 'Dashboard';
$menuAtivo = 'dashboard';
require_once '../includes/header.php';
?>
<main class="content">
  <div class="page-header">
    <div>
      <ol class="breadcrumb"><li>Principal</li><li>Dashboard</li></ol>
      <h1>Dashboard</h1>
      <p>Visão geral da loja e do painel administrativo.</p>
    </div>
  </div>

  <?php exibirMensagem(); ?>

  <div class="stats-grid">
    <section class="card"><div class="card__body">
      <p class="text-muted">Produtos</p><h2><?= $totais['produtos'] ?></h2>
      <a href="produtos.php">Gerenciar produtos</a>
    </div></section>
    <section class="card"><div class="card__body">
      <p class="text-muted">Categorias</p><h2><?= $totais['categorias'] ?></h2>
      <a href="categorias.php">Gerenciar categorias</a>
    </div></section>
    <section class="card"><div class="card__body">
      <p class="text-muted">Usuários</p><h2><?= $totais['usuarios'] ?></h2>
      <a href="usuarios.php">Gerenciar usuários</a>
    </div></section>
    <section class="card"><div class="card__body">
      <p class="text-muted">Pedidos</p><h2><?= $totais['pedidos'] ?></h2>
      <span class="text-muted">Total de pedidos cadastrados</span>
    </div></section>
  </div>

  <section class="card">
    <div class="card__header"><h2>Produtos recentes</h2><a href="produtos.php" class="btn btn-light btn-sm">Ver todos</a></div>
    <div class="table-responsive"><table class="table table--stack">
      <thead><tr><th>Produto</th><th>Categoria</th><th>Preço</th><th>Estoque</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($ultimosProdutos as $produto): ?>
        <tr>
          <td data-label="Produto"><?= e($produto['nome']) ?></td>
          <td data-label="Categoria"><?= e($produto['categoria']) ?></td>
          <td data-label="Preço">R$ <?= number_format((float) $produto['preco'], 2, ',', '.') ?></td>
          <td data-label="Estoque"><?= (int) $produto['estoque'] ?></td>
          <td><a class="action-btn" href="produtos-editar.php?id=<?= (int) $produto['id'] ?>" title="Editar"><i class="bi bi-pencil"></i></a></td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$ultimosProdutos): ?>
        <tr><td colspan="5" class="table-empty">Nenhum produto cadastrado ainda.</td></tr>
      <?php endif; ?>
      </tbody>
    </table></div>
  </section>
</main>
<?php require_once '../includes/footer.php'; ?>
