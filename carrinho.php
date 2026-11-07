<?php
require_once __DIR__ . '/includes/sessao.php';
if (!isset($_SESSION['carrinho']) || !is_array($_SESSION['carrinho'])) $_SESSION['carrinho'] = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfValidar('carrinho.php');
    $acao = post('acao');
    $id = (int)($_POST['produto_id'] ?? 0);
    if ($acao === 'adicionar' && $id > 0) {
        $q = $pdo->prepare('SELECT estoque FROM produtos WHERE id=? AND status=1'); $q->execute([$id]); $estoque = $q->fetchColumn();
        if ($estoque === false || (int)$estoque < 1) definirMensagem('Este produto está indisponível.', 'warning');
        else {
            $quantidade = max(1, (int)($_POST['quantidade'] ?? 1));
            $_SESSION['carrinho'][$id] = min((int)$estoque, (int)($_SESSION['carrinho'][$id] ?? 0) + $quantidade);
            definirMensagem('Produto adicionado ao carrinho.');
        }
    } elseif ($acao === 'atualizar') {
        foreach (($_POST['quantidades'] ?? []) as $produtoId => $quantidade) {
            $produtoId = (int)$produtoId; $quantidade = (int)$quantidade;
            if ($quantidade <= 0) { unset($_SESSION['carrinho'][$produtoId]); continue; }
            $q = $pdo->prepare('SELECT estoque FROM produtos WHERE id=? AND status=1'); $q->execute([$produtoId]); $estoque = $q->fetchColumn();
            if ($estoque === false) unset($_SESSION['carrinho'][$produtoId]);
            else $_SESSION['carrinho'][$produtoId] = min($quantidade, (int)$estoque);
        }
        definirMensagem('Carrinho atualizado.');
    } elseif ($acao === 'remover' && $id > 0) { unset($_SESSION['carrinho'][$id]); definirMensagem('Produto removido do carrinho.'); }
    redirecionar('carrinho.php');
}
$itens=[]; $subtotal=0.0;
if ($_SESSION['carrinho']) {
    $ids = array_map('intval', array_keys($_SESSION['carrinho']));
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $q=$pdo->prepare("SELECT id,nome,preco,imagem,estoque FROM produtos WHERE status=1 AND id IN ($placeholders)"); $q->execute($ids);
    foreach($q->fetchAll() as $p) {
        $quantidade=min((int)$_SESSION['carrinho'][$p['id']],(int)$p['estoque']);
        if($quantidade<1) { unset($_SESSION['carrinho'][$p['id']]); continue; }
        $_SESSION['carrinho'][$p['id']]=$quantidade;
        $p['quantidade']=$quantidade; $p['total']=$quantidade*(float)$p['preco']; $subtotal += $p['total']; $itens[]=$p;
    }
}
function moedaCarrinho($v): string { return 'R$ '.number_format((float)$v,2,',','.'); }
?>
<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Carrinho | Elite Store</title><link rel="stylesheet" href="css/vendor/bootstrap-icons/bootstrap-icons.min.css"><link rel="stylesheet" href="css/style.css"><link rel="stylesheet" href="css/loja.css"></head><body class="storefront">
<header class="store-header"><a class="store-brand" href="loja.php"><img src="img/elitestore.png" alt=""><span>Elite Store<small>Produtos selecionados para você</small></span></a><nav class="store-nav"><a href="loja.php">Continuar comprando</a></nav><a class="btn btn-primary store-cart" href="carrinho.php"><i class="bi bi-bag"></i> Carrinho <span><?= array_sum($_SESSION['carrinho']) ?></span></a></header>
<main class="store-main"><div class="store-section-heading"><div><span class="store-eyebrow">SUA SACOLA</span><h1>Carrinho de compras</h1><p>Confira os produtos antes de finalizar o pedido.</p></div></div><?php exibirMensagem(); ?>
<?php if(!$itens): ?><div class="store-empty"><i class="bi bi-bag"></i><h3>Seu carrinho está vazio</h3><p>Adicione produtos para continuar.</p><a href="loja.php" class="btn btn-primary">Explorar produtos</a></div><?php else: ?>
<div class="cart-layout"><section class="cart-items"><form method="post" action="carrinho.php"><?= csrfCampo() ?><input type="hidden" name="acao" value="atualizar"><?php foreach($itens as $p): ?><article class="cart-item"><div class="cart-item-image"><?php if($p['imagem']): ?><img src="uploads/produtos/<?= rawurlencode(basename($p['imagem'])) ?>" alt=""><?php else: ?><i class="bi bi-image"></i><?php endif; ?></div><div class="cart-item-info"><h3><?= e($p['nome']) ?></h3><strong><?= moedaCarrinho($p['preco']) ?></strong><label>Quantidade <input class="form-control" type="number" min="0" max="<?= (int)$p['estoque'] ?>" name="quantidades[<?= (int)$p['id'] ?>]" value="<?= (int)$p['quantidade'] ?>"></label></div><div class="cart-item-total"><strong><?= moedaCarrinho($p['total']) ?></strong><button class="link-danger" type="submit" form="remover-<?= (int)$p['id'] ?>">Remover</button></div></article><?php endforeach; ?><button class="btn btn-light" type="submit"><i class="bi bi-arrow-repeat"></i> Atualizar carrinho</button></form><?php foreach($itens as $p): ?><form id="remover-<?= (int)$p['id'] ?>" method="post" action="carrinho.php"><?= csrfCampo() ?><input type="hidden" name="acao" value="remover"><input type="hidden" name="produto_id" value="<?= (int)$p['id'] ?>"></form><?php endforeach; ?></section>
<aside class="cart-summary"><h2>Resumo do pedido</h2><div><span>Subtotal</span><strong><?= moedaCarrinho($subtotal) ?></strong></div><div><span>Frete</span><span>Calculado posteriormente</span></div><div class="summary-total"><span>Total</span><strong><?= moedaCarrinho($subtotal) ?></strong></div><a href="finalizar-compra.php" class="btn btn-primary btn-lg btn-block">Continuar para pagamento <i class="bi bi-arrow-right"></i></a><small>O valor do frete poderá ser combinado com a loja.</small></aside></div><?php endif; ?></main>
<footer class="store-footer"><span>&copy; <?= date('Y') ?> Elite Store.</span><span><i class="bi bi-shield-check"></i> Compra simples e segura</span></footer></body></html>
