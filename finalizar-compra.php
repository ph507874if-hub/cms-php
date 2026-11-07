<?php
require_once __DIR__ . '/includes/sessao.php';
if (empty($_SESSION['carrinho'])) redirecionar('carrinho.php');
$ids=array_map('intval',array_keys($_SESSION['carrinho'])); $marks=implode(',',array_fill(0,count($ids),'?'));
$q=$pdo->prepare("SELECT id,nome,preco,estoque FROM produtos WHERE status=1 AND id IN ($marks)"); $q->execute($ids); $produtos=$q->fetchAll();
$itens=[]; $subtotal=0;
foreach($produtos as $p){$qt=min((int)$_SESSION['carrinho'][$p['id']],(int)$p['estoque']); if($qt>0){$p['quantidade']=$qt;$p['subtotal']=$qt*(float)$p['preco'];$subtotal+=$p['subtotal'];$itens[]=$p;}}
if(!$itens) redirecionar('carrinho.php');
$erros=[];
if($_SERVER['REQUEST_METHOD']==='POST'){
 csrfValidar('finalizar-compra.php');
 $nome=post('nome');$email=post('email');$telefone=post('telefone');$endereco=post('endereco');$forma=post('forma_pagamento');$observacao=post('observacao');
 if(mb_strlen($nome)<3)$erros['nome']='Informe seu nome completo.';
 if(!filter_var($email,FILTER_VALIDATE_EMAIL))$erros['email']='Informe um e-mail válido.';
 if($telefone==='')$erros['telefone']='Informe um telefone para contato.';
 if($endereco==='')$erros['endereco']='Informe o endereço de entrega ou retirada.';
 $formas=['pix','cartao_credito','cartao_debito','boleto','dinheiro'];if(!in_array($forma,$formas,true))$erros['forma_pagamento']='Selecione uma forma de pagamento.';
 if(!$erros){
  try{$pdo->beginTransaction();
   $cliente=$pdo->prepare('INSERT INTO clientes (nome,email,telefone,endereco) VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE nome=VALUES(nome), telefone=VALUES(telefone), endereco=VALUES(endereco)');$cliente->execute([$nome,$email,$telefone,$endereco]);
   $c=$pdo->prepare('SELECT id FROM clientes WHERE email=?');$c->execute([$email]);$clienteId=(int)$c->fetchColumn();
   $pedido=$pdo->prepare("INSERT INTO pedidos (usuario_id,cliente_id,subtotal,frete,desconto,total,observacao) VALUES (NULL,?,?,0,0,?,?)");$pedido->execute([$clienteId,$subtotal,$subtotal,$observacao]);$pedidoId=(int)$pdo->lastInsertId();
   $itemStmt=$pdo->prepare('INSERT INTO itens_pedido (pedido_id,produto_id,quantidade,preco_unitario,subtotal) VALUES (?,?,?,?,?)');
   $estoqueStmt=$pdo->prepare('UPDATE produtos SET estoque=estoque-? WHERE id=? AND estoque>=?');
   foreach($itens as $item){$itemStmt->execute([$pedidoId,$item['id'],$item['quantidade'],$item['preco'],$item['subtotal']]);$estoqueStmt->execute([$item['quantidade'],$item['id'],$item['quantidade']]);if($estoqueStmt->rowCount()!==1)throw new RuntimeException('Estoque insuficiente para um dos produtos.');}
   $pg=$pdo->prepare("INSERT INTO pagamentos (pedido_id,forma_pagamento,valor,status) VALUES (?,?,?,'pendente')");$pg->execute([$pedidoId,$forma,$subtotal]);
   $pdo->commit();$_SESSION['carrinho']=[];$_SESSION['ultimo_pedido']=$pedidoId;redirecionar('pedido-confirmado.php');
  }catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();error_log('Falha ao finalizar pedido: '.$e->getMessage());$erros['geral']='Não foi possível concluir o pedido. Verifique o estoque e tente novamente.';}
 }
}
function moedaFinal($v):string{return 'R$ '.number_format((float)$v,2,',','.');}
?>
<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Finalizar compra | Elite Store</title><link rel="stylesheet" href="css/vendor/bootstrap-icons/bootstrap-icons.min.css"><link rel="stylesheet" href="css/style.css"><link rel="stylesheet" href="css/loja.css"></head><body class="storefront"><header class="store-header"><a class="store-brand" href="loja.php"><img src="img/elitestore.png" alt=""><span>Elite Store<small>Produtos selecionados para você</small></span></a><a class="btn btn-light" href="carrinho.php"><i class="bi bi-arrow-left"></i> Voltar ao carrinho</a></header><main class="store-main"><div class="store-section-heading"><div><span class="store-eyebrow">ÚLTIMA ETAPA</span><h1>Finalizar compra</h1><p>Preencha seus dados e escolha como deseja pagar.</p></div></div>
<?php if(isset($erros['geral'])): ?><div class="alert alert-danger"><?= e($erros['geral']) ?></div><?php endif; ?>
<div class="checkout-layout"><form class="checkout-form" method="post"><?= csrfCampo() ?><section class="checkout-panel"><h2><i class="bi bi-person"></i> Dados para contato</h2><div class="form-grid"><div class="form-group col-6"><label class="form-label" for="nome">Nome completo</label><input class="form-control" id="nome" name="nome" required value="<?= e(post('nome')) ?>"><small class="field-error"><?= e($erros['nome']??'') ?></small></div><div class="form-group col-6"><label class="form-label" for="email">E-mail</label><input class="form-control" type="email" id="email" name="email" required value="<?= e(post('email')) ?>"><small class="field-error"><?= e($erros['email']??'') ?></small></div><div class="form-group col-6"><label class="form-label" for="telefone">Telefone / WhatsApp</label><input class="form-control" id="telefone" name="telefone" required value="<?= e(post('telefone')) ?>"><small class="field-error"><?= e($erros['telefone']??'') ?></small></div><div class="form-group col-12"><label class="form-label" for="endereco">Endereço de entrega ou local de retirada</label><input class="form-control" id="endereco" name="endereco" required value="<?= e(post('endereco')) ?>"><small class="field-error"><?= e($erros['endereco']??'') ?></small></div></div></section>
<section class="checkout-panel"><h2><i class="bi bi-credit-card"></i> Forma de pagamento</h2><div class="payment-options"><?php foreach(['pix'=>'Pix','cartao_credito'=>'Cartão de crédito','cartao_debito'=>'Cartão de débito','boleto'=>'Boleto bancário','dinheiro'=>'Dinheiro / na retirada'] as $v=>$label): ?><label class="payment-option"><input type="radio" name="forma_pagamento" value="<?= e($v) ?>" <?= post('forma_pagamento')===$v?'checked':'' ?> required><span><strong><?= e($label) ?></strong><small><?= $v==='pix'?'Pagamento via Pix (confirmação manual)':($v==='dinheiro'?'Pagamento combinado com a loja':'Pagamento a combinar / confirmação manual') ?></small></span></label><?php endforeach; ?></div><small class="field-error"><?= e($erros['forma_pagamento']??'') ?></small><p class="payment-disclaimer"><i class="bi bi-info-circle"></i> Esta versão registra o pedido e a forma de pagamento escolhida. Para cobrar automaticamente, será necessário integrar um gateway de pagamento (ex.: Mercado Pago ou PagBank).</p></section>
<section class="checkout-panel"><h2><i class="bi bi-chat-left-text"></i> Observações (opcional)</h2><textarea class="form-control" name="observacao" rows="3" placeholder="Informações adicionais sobre o pedido... "><?= e(post('observacao')) ?></textarea></section><button type="submit" class="btn btn-primary btn-lg btn-block">Confirmar pedido <i class="bi bi-check2"></i></button></form>
<aside class="cart-summary checkout-summary"><h2>Seu pedido</h2><?php foreach($itens as $i): ?><div><span><?= (int)$i['quantidade'] ?>x <?= e($i['nome']) ?></span><strong><?= moedaFinal($i['subtotal']) ?></strong></div><?php endforeach; ?><div class="summary-total"><span>Total</span><strong><?= moedaFinal($subtotal) ?></strong></div><small>Frete não incluído. A loja combinará a entrega ou retirada.</small></aside></div></main><footer class="store-footer"><span>&copy; <?= date('Y') ?> Elite Store.</span><span><i class="bi bi-shield-check"></i> Compra simples e segura</span></footer></body></html>
