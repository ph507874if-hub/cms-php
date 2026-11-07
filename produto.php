<?php
require_once __DIR__ . '/includes/sessao.php';
$id = max(0, (int)($_GET['id'] ?? 0));
$stmt = $pdo->prepare("SELECT p.*, c.nome AS categoria_nome FROM produtos p INNER JOIN categorias c ON c.id=p.categoria_id WHERE p.id=? AND p.status=1 AND c.status=1 LIMIT 1");
$stmt->execute([$id]);
$p = $stmt->fetch();
if (!$p) { http_response_code(404); exit('Produto não encontrado ou indisponível. <a href="loja.php">Voltar à loja</a>'); }
function precoProduto($valor): string { return 'R$ ' . number_format((float)$valor, 2, ',', '.'); }
$totalCarrinho = array_sum($_SESSION['carrinho'] ?? []);
?>
<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title><?= e($p['nome']) ?> | Elite Store</title><link rel="stylesheet" href="css/vendor/bootstrap-icons/bootstrap-icons.min.css"><link rel="stylesheet" href="css/style.css"><link rel="stylesheet" href="css/loja.css"></head><body class="storefront">
<header class="store-header"><a class="store-brand" href="loja.php"><img src="img/elitestore.png" alt=""><span>Elite Store<small>Produtos selecionados para você</small></span></a><nav class="store-nav"><a href="loja.php">Início</a><a href="loja.php#catalogo">Produtos</a></nav><a class="btn btn-primary store-cart" href="carrinho.php"><i class="bi bi-bag"></i> Carrinho <span><?= (int)$totalCarrinho ?></span></a></header>
<main class="store-main"><p class="store-breadcrumb"><a href="loja.php">Loja</a> / <?= e($p['categoria_nome']) ?> / <?= e($p['nome']) ?></p><section class="product-detail">
<div class="detail-image"><?php if($p['imagem']): ?><img src="uploads/produtos/<?= rawurlencode(basename($p['imagem'])) ?>" alt="<?= e($p['nome']) ?>"><?php else: ?><i class="bi bi-image"></i><?php endif; ?></div>
<div class="detail-info"><span class="product-category"><?= e($p['categoria_nome']) ?></span><h1><?= e($p['nome']) ?></h1><div class="detail-price"><?= precoProduto($p['preco']) ?></div><p><?= nl2br(e($p['descricao'] ?? '')) ?></p><p class="stock-note"><i class="bi bi-check-circle"></i> <?= (int)$p['estoque'] ?> unidade(s) disponível(is)</p><form method="post" action="carrinho.php" class="detail-buy"><?= csrfCampo() ?><input type="hidden" name="acao" value="adicionar"><input type="hidden" name="produto_id" value="<?= (int)$p['id'] ?>"><label for="quantidade">Quantidade</label><input id="quantidade" class="form-control" type="number" name="quantidade" min="1" max="<?= (int)$p['estoque'] ?>" value="1"><button class="btn btn-primary btn-lg" type="submit"><i class="bi bi-bag-plus"></i> Adicionar ao carrinho</button></form><a class="back-store" href="loja.php"><i class="bi bi-arrow-left"></i> Continuar comprando</a></div>
</section></main><footer class="store-footer"><span>&copy; <?= date('Y') ?> Elite Store.</span><span><i class="bi bi-shield-check"></i> Compra simples e segura</span></footer></body></html>
