<?php
require_once '../includes/auth.php';
require_once '../config/conexao.php';
exigirAdmin();

$id = (int)get('id');
if ($id <= 0) { definirMensagem('Pagamento inválido.', 'danger'); redirecionar('pagamentos.php'); }

$sql = $pdo->prepare('SELECT id, pedido_id, valor, metodo, status, criado_em FROM pagamentos WHERE id = ?');
$sql->execute([$id]);
$pagamento = $sql->fetch();

if (!$pagamento) {
    definirMensagem('Pagamento não encontrado.', 'danger');
    redirecionar('pagamentos.php');
}

$titulo = 'Detalhes do pagamento'; $menuAtivo = 'pagamentos';
require_once '../includes/header.php';
?>
<main class="content">
  <div class="page-header"><div><ol class="breadcrumb"><li><a href="dashboard.php">Dashboard</a></li><li><a href="pagamentos.php">Pagamentos</a></li><li>Detalhes</li></ol><h1>Pagamento #<?= (int)$pagamento['id'] ?></h1><p>Informações do pagamento associado ao pedido.</p></div><div class="page-header__actions"><a href="pagamentos.php" class="btn btn-light"><i class="bi bi-arrow-left"></i> Voltar</a></div></div>
  <section class="card"><div class="card__body">
    <p><strong>ID do pagamento:</strong> #<?= (int)$pagamento['id'] ?></p>
    <p><strong>Pedido:</strong> #<?= (int)$pagamento['pedido_id'] ?></p>
    <p><strong>Valor:</strong> R$ <?= number_format((float)$pagamento['valor'],2,',','.') ?></p>
    <p><strong>Método:</strong> <?= e($pagamento['metodo'] ?? 'Não informado') ?></p>
    <p><strong>Status:</strong> <?= e(ucfirst((string)$pagamento['status'])) ?></p>
    <p><strong>Data do registro:</strong> <?= e($pagamento['criado_em'] ?? 'Não informada') ?></p>
  </div></section>
</main>
<?php require_once '../includes/footer.php'; ?>
