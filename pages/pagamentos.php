<?php
/* Listagem de pagamentos associados a pedidos.
   Ajuste os nomes das colunas se o esquema do banco for diferente. */
require_once '../includes/auth.php';
require_once '../config/conexao.php';
exigirAdmin();

$porPagina = 10;
$busca = get('busca');
$status = get('status');
$pagina = max(1, (int)get('pagina'));

$condicoes = [];
$params = [];
if ($busca !== '' && ctype_digit($busca)) {
    $condicoes[] = 'pg.id = :id_busca';
    $params[':id_busca'] = (int)$busca;
} elseif ($busca !== '') {
    $condicoes[] = '(CAST(pg.pedido_id AS CHAR) LIKE :busca OR CAST(pg.metodo AS CHAR) LIKE :busca2)';
    $params[':busca'] = '%' . addcslashes($busca, '%_\\') . '%';
    $params[':busca2'] = '%' . addcslashes($busca, '%_\\') . '%';
}
if ($status !== '' && in_array($status, ['pendente','pago','aprovado','recusado','cancelado','estornado'], true)) {
    $condicoes[] = 'pg.status = :status';
    $params[':status'] = $status;
}
$where = $condicoes ? 'WHERE ' . implode(' AND ', $condicoes) : '';

$sql = $pdo->prepare("SELECT COUNT(*) FROM pagamentos pg $where");
$sql->execute($params);
$total = (int)$sql->fetchColumn();
$totalPaginas = max(1, (int)ceil($total / $porPagina));
$pagina = min($pagina, $totalPaginas);
$offset = ($pagina - 1) * $porPagina;

$sql = $pdo->prepare(
    "SELECT pg.id, pg.pedido_id, pg.valor, pg.metodo, pg.status, pg.criado_em
       FROM pagamentos pg
       $where
      ORDER BY pg.criado_em DESC, pg.id DESC
      LIMIT :limite OFFSET :offset"
);
foreach ($params as $key => $value) $sql->bindValue($key, $value);
$sql->bindValue(':limite', $porPagina, PDO::PARAM_INT);
$sql->bindValue(':offset', $offset, PDO::PARAM_INT);
$sql->execute();
$pagamentos = $sql->fetchAll();

$temFiltro = $busca !== '' || $status !== '';
function linkPaginaPagamento(int $numero): string {
    $params = array_filter(['busca'=>get('busca'), 'status'=>get('status')], 'strlen');
    $params['pagina'] = $numero;
    return 'pagamentos.php?' . e(http_build_query($params));
}

$titulo = 'Pagamentos'; $menuAtivo = 'pagamentos';
require_once '../includes/header.php';
?>
<main class="content">
  <div class="page-header"><div><ol class="breadcrumb"><li><a href="dashboard.php">Dashboard</a></li><li>Pagamentos</li></ol><h1>Pagamentos</h1><p>Consulte os pagamentos vinculados aos pedidos.</p></div></div>
  <?php exibirMensagem(); ?>
  <section class="card">
    <form class="filters" action="pagamentos.php" method="get">
      <div class="input-icon"><i class="bi bi-search"></i><input type="search" class="form-control" name="busca" placeholder="Buscar por ID, pedido ou método..." value="<?= e($busca) ?>"></div>
      <select class="form-select" name="status"><option value="">Todos os status</option>
        <?php foreach (['pendente'=>'Pendente','pago'=>'Pago','aprovado'=>'Aprovado','recusado'=>'Recusado','cancelado'=>'Cancelado','estornado'=>'Estornado'] as $valor=>$rotulo): ?>
          <option value="<?= e($valor) ?>" <?= $status===$valor?'selected':'' ?>><?= e($rotulo) ?></option>
        <?php endforeach; ?>
      </select>
      <button class="btn btn-light" type="submit"><i class="bi bi-funnel"></i> Filtrar</button>
      <?php if($temFiltro): ?><a href="pagamentos.php" class="btn btn-ghost"><i class="bi bi-x-circle"></i> Limpar</a><?php endif; ?>
    </form>
    <div class="table-responsive"><table class="table table--stack">
      <thead><tr><th>Pagamento</th><th>Pedido</th><th>Valor</th><th>Método</th><th>Status</th><th>Data</th><th>Ações</th></tr></thead>
      <tbody>
      <?php foreach($pagamentos as $pg): ?>
        <tr>
          <td data-label="Pagamento">#<?= (int)$pg['id'] ?></td>
          <td data-label="Pedido">#<?= (int)$pg['pedido_id'] ?></td>
          <td data-label="Valor">R$ <?= number_format((float)$pg['valor'],2,',','.') ?></td>
          <td data-label="Método"><?= e($pg['metodo'] ?? '—') ?></td>
          <td data-label="Status"><?= e(ucfirst((string)$pg['status'])) ?></td>
          <td data-label="Data"><?= e($pg['criado_em'] ?? '—') ?></td>
          <td><a class="action-btn" href="pagamentos-detalhes.php?id=<?= (int)$pg['id'] ?>" title="Ver detalhes"><i class="bi bi-eye"></i></a></td>
        </tr>
      <?php endforeach; ?>
      <?php if(!$pagamentos): ?><tr><td colspan="7" class="table-empty"><?= $temFiltro?'Nenhum pagamento encontrado.':'Nenhum pagamento cadastrado.' ?></td></tr><?php endif; ?>
      </tbody>
    </table></div>
    <div class="card__footer"><span class="pagination-info">Mostrando <strong><?= count($pagamentos) ?></strong> de <strong><?= $total ?></strong> pagamentos</span>
      <?php if($totalPaginas>1): ?><nav aria-label="Paginação"><ul class="pagination">
        <?php if($pagina>1): ?><li><a href="<?= linkPaginaPagamento($pagina-1) ?>"><i class="bi bi-chevron-left"></i></a></li><?php endif; ?>
        <?php for($i=1;$i<=$totalPaginas;$i++): ?><li class="<?= $i===$pagina?'is-active':'' ?>"><?php if($i===$pagina): ?><span><?= $i ?></span><?php else: ?><a href="<?= linkPaginaPagamento($i) ?>"><?= $i ?></a><?php endif; ?></li><?php endfor; ?>
        <?php if($pagina<$totalPaginas): ?><li><a href="<?= linkPaginaPagamento($pagina+1) ?>"><i class="bi bi-chevron-right"></i></a></li><?php endif; ?>
      </ul></nav><?php endif; ?>
    </div>
  </section>
</main>
<?php require_once '../includes/footer.php'; ?>
