<?php
require_once __DIR__ . '/includes/sessao.php';

$busca = trim((string)($_GET['busca'] ?? ''));
$categoriaId = max(0, (int)($_GET['categoria'] ?? 0));
$categorias = $pdo->query("SELECT id, nome FROM categorias WHERE status = 1 ORDER BY nome")->fetchAll();

$sql = "SELECT p.id, p.nome, p.descricao, p.imagem, p.preco, p.estoque, c.nome AS categoria_nome
          FROM produtos p INNER JOIN categorias c ON c.id = p.categoria_id
         WHERE p.status = 1 AND c.status = 1 AND p.estoque > 0";
$params = [];
if ($busca !== '') {
    $sql .= " AND (p.nome LIKE :busca1 OR p.descricao LIKE :busca2)";
    $termo = '%' . addcslashes($busca, '%_\\') . '%';
    $params[':busca1'] = $termo;
    $params[':busca2'] = $termo;
}
if ($categoriaId > 0) {
    $sql .= " AND p.categoria_id = :categoria";
    $params[':categoria'] = $categoriaId;
}
$sql .= " ORDER BY p.criado_em DESC, p.nome";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$produtos = $stmt->fetchAll();
$totalCarrinho = array_sum($_SESSION['carrinho'] ?? []);
function precoLoja($valor): string { return 'R$ ' . number_format((float)$valor, 2, ',', '.'); }
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Loja | <?= e('Elite Store') ?></title>
  <link rel="icon" type="image/svg+xml" href="img/favicon.svg">
  <link rel="stylesheet" href="css/vendor/bootstrap-icons/bootstrap-icons.min.css">
  <link rel="stylesheet" href="css/style.css"><link rel="stylesheet" href="css/loja.css">
</head>
<body class="storefront">
<header class="store-header">
  <a class="store-brand" href="loja.php"><img src="img/elitestore.png" alt=""><span>Elite Store<small>Produtos selecionados para você</small></span></a>
  <nav class="store-nav"><a href="loja.php">Início</a><a href="#catalogo">Produtos</a></nav>
  <div class="store-actions"><a class="store-admin-link" href="admin.php"><i class="bi bi-person-lock"></i> Área do administrador</a><a class="btn btn-primary store-cart" href="carrinho.php"><i class="bi bi-bag"></i> Carrinho <span><?= (int)$totalCarrinho ?></span></a></div>
</header>
<main class="store-main">
  <section class="store-hero">
    <div><span class="store-eyebrow">BEM-VINDO À NOSSA LOJA</span><h1>Encontre o que você procura.</h1><p>Explore nosso catálogo e escolha seus produtos favoritos.</p><a href="#catalogo" class="btn btn-primary btn-lg">Ver produtos <i class="bi bi-arrow-down"></i></a></div>
    <div class="store-hero-art"><i class="bi bi-bag-heart"></i><span>Qualidade e praticidade<br>em um só lugar.</span></div>
  </section>
  <section class="store-catalog" id="catalogo">
    <div class="store-section-heading"><div><span class="store-eyebrow">CATÁLOGO</span><h2>Nossos produtos</h2><p>Confira os itens disponíveis para compra.</p></div></div>
    <form class="store-filters" method="get" action="loja.php">
      <label class="store-search"><i class="bi bi-search"></i><input type="search" name="busca" placeholder="Buscar produtos..." value="<?= e($busca) ?>"></label>
      <select name="categoria" class="form-select"><option value="0">Todas as categorias</option><?php foreach ($categorias as $cat): ?><option value="<?= (int)$cat['id'] ?>" <?= $categoriaId === (int)$cat['id'] ? 'selected' : '' ?>><?= e($cat['nome']) ?></option><?php endforeach; ?></select>
      <button class="btn btn-light" type="submit"><i class="bi bi-funnel"></i> Filtrar</button>
    </form>
    <?php if (!$produtos): ?><div class="store-empty"><i class="bi bi-search"></i><h3>Nenhum produto encontrado</h3><p>Tente outra busca ou selecione uma categoria diferente.</p><a href="loja.php" class="btn btn-light">Limpar filtros</a></div>
    <?php else: ?><div class="product-grid">
      <?php foreach ($produtos as $p): ?>
      <article class="store-product">
        <a class="store-product-image" href="produto.php?id=<?= (int)$p['id'] ?>">
          <?php if (!empty($p['imagem'])): ?><img src="uploads/produtos/<?= rawurlencode(basename($p['imagem'])) ?>" alt="<?= e($p['nome']) ?>" loading="lazy"><?php else: ?><span class="product-placeholder"><i class="bi bi-image"></i></span><?php endif; ?>
        </a>
        <div class="store-product-info"><span class="product-category"><?= e($p['categoria_nome']) ?></span><h3><a href="produto.php?id=<?= (int)$p['id'] ?>"><?= e($p['nome']) ?></a></h3><p><?= e(mb_strimwidth(strip_tags((string)$p['descricao']), 0, 95, '…')) ?></p>
          <div class="store-product-bottom"><strong><?= precoLoja($p['preco']) ?></strong><form method="post" action="carrinho.php"><?= csrfCampo() ?><input type="hidden" name="acao" value="adicionar"><input type="hidden" name="produto_id" value="<?= (int)$p['id'] ?>"><input type="hidden" name="quantidade" value="1"><button class="btn btn-primary btn-sm" type="submit"><i class="bi bi-bag-plus"></i> Comprar</button></form></div>
        </div>
      </article>
      <?php endforeach; ?>
    </div><?php endif; ?>
  </section>
</main>
<footer class="store-footer"><span>&copy; <?= date('Y') ?> Elite Store. Todos os direitos reservados.</span><span><i class="bi bi-shield-check"></i> Compra simples e segura</span></footer>
</body></html>
