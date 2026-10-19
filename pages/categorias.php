```php
<?php
/* ==========================================================
   Categorias — listagem, busca, filtros e paginação
   ========================================================== */

require_once '../includes/auth.php';
require_once '../config/conexao.php';

exigirAdmin();

$porPagina = 8;

$busca  = get('busca');
$status = get('status');
$pagina = max(1, (int) get('pagina'));

$condicoes = [];
$parametros = [];

if ($busca !== '') {
    $condicoes[] = '(nome LIKE :busca1 OR descricao LIKE :busca2)';
    $termo = '%' . addcslashes($busca, '%_\\') . '%';

    $parametros[':busca1'] = $termo;
    $parametros[':busca2'] = $termo;
}

if ($status === 'ativo' || $status === 'inativo') {
    $condicoes[] = 'status = :status';
    $parametros[':status'] = $status === 'ativo' ? 1 : 0;
}

$where = $condicoes
    ? 'WHERE ' . implode(' AND ', $condicoes)
    : '';

/* Total de categorias */
$sql = $pdo->prepare(
    "SELECT COUNT(*) FROM categorias $where"
);
$sql->execute($parametros);

$total = (int) $sql->fetchColumn();

$totalPaginas = max(
    1,
    (int) ceil($total / $porPagina)
);

$pagina = min($pagina, $totalPaginas);
$offset = ($pagina - 1) * $porPagina;

/* Categorias da página atual */
$sql = $pdo->prepare(
    "SELECT id, nome, descricao, status, criado_em
       FROM categorias
       $where
      ORDER BY nome
      LIMIT :limite OFFSET :offset"
);

foreach ($parametros as $nome => $valor) {
    $sql->bindValue($nome, $valor);
}

$sql->bindValue(':limite', $porPagina, PDO::PARAM_INT);
$sql->bindValue(':offset', $offset, PDO::PARAM_INT);

$sql->execute();
$categorias = $sql->fetchAll();

$temFiltro = $busca !== '' || $status !== '';

function linkPagina(int $numero): string
{
    $parametros = array_filter([
        'busca' => get('busca'),
        'status' => get('status'),
    ], 'strlen');

    $parametros['pagina'] = $numero;

    return 'categorias.php?' .
        e(http_build_query($parametros));
}

$titulo = 'Categorias';
$menuAtivo = 'categorias';

require_once '../includes/header.php';
?>

<main class="content">

    <div class="page-header">
        <div>
            <ol class="breadcrumb">
                <li><a href="dashboard.php">Dashboard</a></li>
                <li>Categorias</li>
            </ol>

            <h1>Categorias</h1>
            <p>Gerencie as categorias dos produtos da loja.</p>
        </div>

        <div class="page-header__actions">
            <a href="categorias-cadastrar.php"
               class="btn btn-primary">
                <i class="bi bi-plus-lg"></i>
                Nova categoria
            </a>
        </div>
    </div>

    <?php exibirMensagem(); ?>

    <section class="card">

        <form class="filters"
              action="categorias.php"
              method="get">

            <div class="input-icon">
                <i class="bi bi-search"></i>

                <input type="search"
                       class="form-control"
                       name="busca"
                       placeholder="Buscar categoria..."
                       aria-label="Buscar categoria"
                       value="<?= e($busca) ?>">
            </div>

            <select class="form-select"
                    name="status"
                    aria-label="Filtrar por status">

                <option value="">Todos os status</option>

                <option value="ativo"
                    <?= $status === 'ativo' ? 'selected' : '' ?>>
                    Ativas
                </option>

                <option value="inativo"
                    <?= $status === 'inativo' ? 'selected' : '' ?>>
                    Inativas
                </option>
            </select>

            <button type="submit" class="btn btn-light">
                <i class="bi bi-funnel"></i> Filtrar
            </button>

            <?php if ($temFiltro): ?>
                <a href="categorias.php" class="btn btn-ghost">
                    <i class="bi bi-x-circle"></i> Limpar
                </a>
            <?php endif; ?>

        </form>

        <div class="table-responsive">
            <table class="table table--stack">

                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Descrição</th>
                        <th>Status</th>
                        <th>Cadastro</th>
                        <th class="col-actions">Ações</th>
                    </tr>
                </thead>

                <tbody>

                <?php foreach ($categorias as $c): ?>
                    <tr>
                        <td data-label="Nome">
                            <strong><?= e($c['nome']) ?></strong>
                        </td>

                        <td data-label="Descrição">
                            <?= e($c['descricao'] ?? '') ?>
                        </td>

                        <td data-label="Status">
                            <?php if ((int) $c['status'] === 1): ?>
                                <span class="badge badge-success">
                                    Ativa
                                </span>
                            <?php else: ?>
                                <span class="badge badge-secondary">
                                    Inativa
                                </span>
                            <?php endif; ?>
                        </td>

                        <td data-label="Cadastro" class="text-muted">
                            <?= e($c['criado_em'] ?? '') ?>
                        </td>

                        <td class="col-actions">
                            <a href="categorias-editar.php?id=<?= (int) $c['id'] ?>"
                               class="action-btn"
                               title="Editar categoria">
                                <i class="bi bi-pencil"></i>
                            </a>

                            <form action="categorias-excluir.php"
                                  method="post"
                                  style="display:inline"
                                  onsubmit="return confirm('Deseja excluir esta categoria?');">

                                <?= csrfCampo() ?>

                                <input type="hidden"
                                       name="id"
                                       value="<?= (int) $c['id'] ?>">

                                <button type="submit"
                                        class="action-btn action-btn--danger"
                                        title="Excluir categoria">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php if (!$categorias): ?>
                    <tr>
                        <td colspan="5" class="table-empty">
                            <i class="bi bi-search"></i>

                            <?= $temFiltro
                                ? 'Nenhuma categoria encontrada.'
                                : 'Nenhuma categoria cadastrada.' ?>
                        </td>
                    </tr>
                <?php endif; ?>

                </tbody>
            </table>
        </div>

        <div class="card__footer">

            <span class="pagination-info">
                Mostrando <strong><?= count($categorias) ?></strong>
                de <strong><?= $total ?></strong>
                <?= $total === 1 ? 'categoria' : 'categorias' ?>
            </span>

            <?php if ($totalPaginas > 1): ?>
                <nav aria-label="Paginação">
                    <ul class="pagination">

                        <?php if ($pagina > 1): ?>
                            <li>
                                <a href="<?= linkPagina($pagina - 1) ?>">
                                    <i class="bi bi-chevron-left"></i>
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                            <li class="<?= $i === $pagina ? 'is-active' : '' ?>">
                                <?php if ($i === $pagina): ?>
                                    <span><?= $i ?></span>
                                <?php else: ?>
                                    <a href="<?= linkPagina($i) ?>">
                                        <?= $i ?>
                                    </a>
                                <?php endif; ?>
                            </li>
                        <?php endfor; ?>

                        <?php if ($pagina < $totalPaginas): ?>
                            <li>
                                <a href="<?= linkPagina($pagina + 1) ?>">
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                            </li>
                        <?php endif; ?>

                    </ul>
                </nav>
            <?php endif; ?>

        </div>
    </section>

</main>

<?php require_once '../includes/footer.php'; ?>
```