<?php
/* Produtos — listagem, busca, filtros e paginação */
require_once '../includes/auth.php';
require_once '../config/conexao.php';
exigirAdmin();

$porPagina = 8;
$busca = get('busca');
$status = get('status');
$categoriaId = (int) get('categoria_id');
$pagina = max(1, (int) get('pagina'));

$condicoes = [];
$parametros = [];

if ($busca !== '') {
    $condicoes[] = '(p.nome LIKE :busca1 OR p.descricao LIKE :busca2)';
    $termo = '%' . addcslashes($busca, '%_\\') . '%';
    $parametros[':busca1'] = $termo;
    $parametros[':busca2'] = $termo;
}
if ($status === 'ativo' || $status === 'inativo') {
    $condicoes[] = 'p.status = :status';
    $parametros[':status'] = $status === 'ativo' ? 1 : 0;
}
if ($categoriaId > 0) {
    $condicoes[] = 'p.categoria_id = :categoria_id';
    $parametros[':categoria_id'] = $categoriaId;
}
$where = $condicoes ? 'WHERE ' . implode(' AND ', $condicoes) : '';

$sql = $pdo->prepare("SELECT COUNT(*) FROM produtos p $where");
$sql->execute($parametros);
$total = (int) $sql->fetchColumn();
$totalPaginas = max(1, (int) ceil($total / $porPagina));
$pagina = min($pagina, $totalPaginas);
$offset = ($pagina - 1) * $porPagina;

$sql = $pdo->prepare(
    "SELECT p.id, p.nome, p.descricao, p.preco, p.estoque, p.imagem,
            p.status, p.criado_em, p.categoria_id, c.nome AS categoria_nome
       FROM produtos p
       LEFT JOIN categorias c ON c.id = p.categoria_id
       $where
      ORDER BY p.nome
      LIMIT :limite OFFSET :offset"
);
foreach ($parametros as $nome => $valor) $sql->bindValue($nome, $valor);
$sql->bindValue(':limite', $porPagina, PDO::PARAM_INT);
$sql->bindValue(':offset', $offset, PDO::PARAM_INT);
$sql->execute();
$produtos = $sql->fetchAll();

$categorias = $pdo->query('SELECT id, nome FROM categorias ORDER BY nome')->fetchAll();
$temFiltro = $busca !== '' || $status !== '' || $categoriaId > 0;

function linkPagina(int $numero): string {
    $params = array_filter([
        'busca' => get('busca'),
        'status' => get('status'),
        'categoria_id' => get('categoria_id'),
    ], 'strlen');
    $params['pagina'] = $numero;
    return 'produtos.php?' . e(http_build_query($params));
}

$titulo = 'Produtos';
$menuAtivo = 'produtos';
require_once '../includes/header.php';
?>
<main class="content">
  <div class="page-header">
    <div>
      <ol class="breadcrumb"><li><a href="dashboard.php">Dashboard</a></li><li>Produtos</li></ol>
      <h1>Produtos</h1><p>Gerencie o catálogo de produtos da loja.</p>
    </div>
    <div class="page-header__actions">
      <a href="produtos-cadastrar.php" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Novo produto</a>
    </div>
  </div>
  <?php exibirMensagem(); ?>
  <section class="card">
    <form class="filters" action="produtos.php" method="get">
      <div class="input-icon"><i class="bi bi-search"></i>
        <input type="search" class="form-control" name="busca" placeholder="Buscar produto..." value="<?= e($busca) ?>" aria-label="Buscar produto">
      </div>
      <select class="form-select" name="categoria_id" aria-label="Filtrar por categoria">
        <option value="">Todas as categorias</option>
        <?php foreach ($categorias as $cat): ?>
          <option value="<?= (int)$cat['id'] ?>" <?= $categoriaId === (int)$cat['id'] ? 'selected' : '' ?>><?= e($cat['nome']) ?></option>
        <?php endforeach; ?>
      </select>
      <select class="form-select" name="status" aria-label="Filtrar por status">
        <option value="">Todos os status</option>
        <option value="ativo" <?= $status === 'ativo' ? 'selected' : '' ?>>Ativos</option>
        <option value="inativo" <?= $status === 'inativo' ? 'selected' : '' ?>>Inativos</option>
      </select>
      <button type="submit" class="btn btn-light"><i class="bi bi-funnel"></i> Filtrar</button>
      <?php if ($temFiltro): ?><a href="produtos.php" class="btn btn-ghost"><i class="bi bi-x-circle"></i> Limpar</a><?php endif; ?>
    </form>
    <div class="table-responsive">
      <table class="table table--stack">
        <thead><tr><th>Produto</th><th>Categoria</th><th>Preço</th><th>Estoque</th><th>Status</th><th class="col-actions">Ações</th></tr></thead>
        <tbody>
        <?php foreach ($produtos as $p): ?>
          <tr>
            <td data-label="Produto">
              <div class="user-cell">
                <?php if (!empty($p['imagem'])): ?>
                  <img src="../uploads/produtos/<?= rawurlencode(basename($p['imagem'])) ?>" alt="" style="width:48px;height:48px;object-fit:cover;border-radius:8px">
                <?php else: ?><i class="bi bi-image" style="font-size:1.6rem"></i><?php endif; ?>
                <div><span class="user-cell__name"><?= e($p['nome']) ?></span><span class="user-cell__email"><?= e(mb_strimwidth((string)$p['descricao'], 0, 70, '…')) ?></span></div>
              </div>
            </td>
            <td data-label="Categoria"><?= e($p['categoria_nome'] ?? 'Sem categoria') ?></td>
            <td data-label="Preço">R$ <?= number_format((float)$p['preco'], 2, ',', '.') ?></td>
            <td data-label="Estoque"><?= (int)$p['estoque'] ?></td>
            <td data-label="Status"><?= (int)$p['status'] === 1 ? 'Ativo' : 'Inativo' ?></td>
            <td class="col-actions">
              <a href="produtos-editar.php?id=<?= (int)$p['id'] ?>" class="action-btn" title="Editar"><i class="bi bi-pencil"></i></a>
              <form action="produtos-excluir.php" method="post" style="display:inline" onsubmit="return confirm('Deseja excluir este produto?')">
                <?= csrfCampo() ?>
                <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                <button type="submit" class="action-btn action-btn--danger" title="Excluir"><i class="bi bi-trash3"></i></button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$produtos): ?><tr><td colspan="6" class="table-empty"><?= $temFiltro ? 'Nenhum produto encontrado.' : 'Nenhum produto cadastrado.' ?></td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
    <div class="card__footer">
      <span class="pagination-info">Mostrando <strong><?= count($produtos) ?></strong> de <strong><?= $total ?></strong> produtos</span>
      <?php if ($totalPaginas > 1): ?><nav aria-label="Paginação"><ul class="pagination">
        <?php if ($pagina > 1): ?><li><a href="<?= linkPagina($pagina-1) ?>"><i class="bi bi-chevron-left"></i></a></li><?php endif; ?>
        <?php for ($i=1; $i <= $totalPaginas; $i++): ?><li class="<?= $i === $pagina ? 'is-active' : '' ?>"><?php if ($i === $pagina): ?><span><?= $i ?></span><?php else: ?><a href="<?= linkPagina($i) ?>"><?= $i ?></a><?php endif; ?></li><?php endfor; ?>
        <?php if ($pagina < $totalPaginas): ?><li><a href="<?= linkPagina($pagina+1) ?>"><i class="bi bi-chevron-right"></i></a></li><?php endif; ?>
      </ul></nav><?php endif; ?>
    </div>
  </section>
</main>
<?php require_once '../includes/footer.php'; ?>
