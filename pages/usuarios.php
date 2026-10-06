<?php
/* ==========================================================================
   Usuários — listagem com busca, filtros e paginação (o "R" do CRUD: Read)
   ========================================================================== */

require_once '../includes/auth.php';
require_once '../config/conexao.php';
require_once '../includes/usuarios-funcoes.php';

exigirAdmin(); // só administradores gerenciam usuários

$porPagina = 8; // registros exibidos em cada página

/* ---- 1. Filtros recebidos pela URL (?busca=...&perfil=...&status=...) ---- */
$busca  = get('busca');
$perfil = get('perfil');
$status = get('status');
$pagina = max(1, (int) get('pagina'));

/* ---- 2. Monta o WHERE conforme os filtros preenchidos --------------------
   As condições e os valores ficam em arrays separados: os valores NUNCA são
   concatenados no SQL, vão sempre pelos parâmetros do prepared statement. */
$condicoes  = [];
$parametros = [];

if ($busca !== '') {
    $condicoes[] = '(nome LIKE :busca1 OR email LIKE :busca2 OR usuario LIKE :busca3)';
    // addcslashes() faz % e _ digitados pelo usuário serem tratados como texto comum
    $termo = '%' . addcslashes($busca, '%_\\') . '%';
    $parametros[':busca1'] = $termo;
    $parametros[':busca2'] = $termo;
    $parametros[':busca3'] = $termo;
}
if ($perfil === 'admin' || $perfil === 'editor') {
    $condicoes[] = 'perfil = :perfil';
    $parametros[':perfil'] = $perfil;
}
if ($status === 'ativo' || $status === 'inativo') {
    $condicoes[] = 'status = :status';
    $parametros[':status'] = $status === 'ativo' ? 1 : 0;
}

$where = $condicoes ? 'WHERE ' . implode(' AND ', $condicoes) : '';

/* ---- 3. Total de registros (para calcular a quantidade de páginas) ------- */
$sql = $pdo->prepare("SELECT COUNT(*) FROM usuarios $where");
$sql->execute($parametros);
$total = (int) $sql->fetchColumn();

$totalPaginas = max(1, (int) ceil($total / $porPagina));
$pagina       = min($pagina, $totalPaginas);
$offset       = ($pagina - 1) * $porPagina;

/* ---- 4. Busca só os registros da página atual ---------------------------- */
$sql = $pdo->prepare(
    "SELECT id, nome, email, usuario, foto, perfil, status, criado_em
       FROM usuarios
       $where
      ORDER BY nome
      LIMIT :limite OFFSET :offset"
);
foreach ($parametros as $nome => $valor) {
    $sql->bindValue($nome, $valor);
}
// LIMIT e OFFSET precisam ser enviados como números inteiros
$sql->bindValue(':limite', $porPagina, PDO::PARAM_INT);
$sql->bindValue(':offset', $offset, PDO::PARAM_INT);
$sql->execute();
$usuarios = $sql->fetchAll();

$temFiltro = $busca !== '' || $perfil !== '' || $status !== '';

/** Monta o link de uma página mantendo os filtros atuais. */
function linkPagina(int $numero): string
{
    $parametros = array_filter([
        'busca'  => get('busca'),
        'perfil' => get('perfil'),
        'status' => get('status'),
    ], 'strlen');
    $parametros['pagina'] = $numero;
    return 'usuarios.php?' . e(http_build_query($parametros));
}

$titulo    = 'Usuários';
$menuAtivo = 'usuarios';
require_once '../includes/header.php';
?>

      <main class="content">

        <div class="page-header">
          <div>
            <ol class="breadcrumb">
              <li><a href="dashboard.php">Dashboard</a></li>
              <li>Usuários</li>
            </ol>
            <h1>Usuários</h1>
            <p>Gerencie quem pode acessar o painel administrativo.</p>
          </div>
          <div class="page-header__actions">
            <a href="usuarios-cadastrar.php" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Novo usuário</a>
          </div>
        </div>

        <!-- Mensagem de retorno (cadastrado, alterado, excluído...) -->
        <?php exibirMensagem(); ?>

        <section class="card">

          <!-- Filtros: o formulário envia via GET (?busca=...&perfil=...&status=...) -->
          <form class="filters" id="formFiltros" action="usuarios.php" method="get">
            <div class="input-icon">
              <i class="bi bi-search"></i>
              <input type="search" class="form-control" id="busca" name="busca" placeholder="Buscar por nome, e-mail ou usuário..." aria-label="Buscar" value="<?= e($busca) ?>">
            </div>
            <select class="form-select" id="filtroPerfil" name="perfil" aria-label="Filtrar por perfil">
              <option value="">Todos os perfis</option>
              <option value="admin"  <?= $perfil === 'admin'  ? 'selected' : '' ?>>Administrador</option>
              <option value="editor" <?= $perfil === 'editor' ? 'selected' : '' ?>>Editor</option>
            </select>
            <select class="form-select" id="filtroStatus" name="status" aria-label="Filtrar por status">
              <option value="">Todos os status</option>
              <option value="ativo"   <?= $status === 'ativo'   ? 'selected' : '' ?>>Ativo</option>
              <option value="inativo" <?= $status === 'inativo' ? 'selected' : '' ?>>Inativo</option>
            </select>
            <button type="submit" class="btn btn-light"><i class="bi bi-funnel"></i> Filtrar</button>
            <?php if ($temFiltro): ?>
              <a href="usuarios.php" class="btn btn-ghost"><i class="bi bi-x-circle"></i> <span class="filters__clear-text">Limpar</span></a>
            <?php endif; ?>
          </form>

          <div class="table-responsive">
            <table class="table table--stack" id="tabelaUsuarios">
              <thead>
                <tr>
                  <th>Nome</th>
                  <th>Usuário</th>
                  <th>Perfil</th>
                  <th>Status</th>
                  <th>Cadastro</th>
                  <th class="col-actions">Ações</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($usuarios as $u): ?>
                <tr>
                  <td class="col-user">
                    <div class="user-cell">
                      <?= avatar($u) ?>
                      <div><span class="user-cell__name"><?= e($u['nome']) ?></span><span class="user-cell__email"><?= e($u['email']) ?></span></div>
                    </div>
                  </td>
                  <td data-label="Usuário" class="text-muted">@<?= e($u['usuario']) ?></td>
                  <td data-label="Perfil"><?= badgePerfil($u['perfil']) ?></td>
                  <td data-label="Status"><?= badgeStatus($u['status']) ?></td>
                  <td data-label="Cadastro" class="text-muted"><?= formatarData($u['criado_em']) ?></td>
                  <td class="col-actions">
                    <a href="usuarios-editar.php?id=<?= $u['id'] ?>" class="action-btn" title="Editar" aria-label="Editar <?= e($u['nome']) ?>"><i class="bi bi-pencil"></i></a>
                    <?php if ($u['id'] != $usuarioLogado['id']): // ninguém exclui a própria conta ?>
                    <button type="button" class="action-btn action-btn--danger btn-excluir" data-id="<?= $u['id'] ?>" data-nome="<?= e($u['nome']) ?>" title="Excluir" aria-label="Excluir <?= e($u['nome']) ?>"><i class="bi bi-trash3"></i></button>
                    <?php endif; ?>
                  </td>
                </tr>
                <?php endforeach; ?>

                <?php if (!$usuarios): ?>
                <tr>
                  <td colspan="6" class="table-empty">
                    <i class="bi bi-search"></i>
                    <?= $temFiltro ? 'Nenhum usuário encontrado com os filtros informados.' : 'Nenhum usuário cadastrado.' ?>
                  </td>
                </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>

          <!-- Paginação -->
          <div class="card__footer">
            <span class="pagination-info">Mostrando <strong><?= count($usuarios) ?></strong> de <strong><?= $total ?></strong> <?= $total === 1 ? 'usuário' : 'usuários' ?></span>
            <?php if ($totalPaginas > 1): ?>
            <nav aria-label="Paginação">
              <ul class="pagination">
                <?php if ($pagina > 1): ?>
                  <li><a href="<?= linkPagina($pagina - 1) ?>" aria-label="Anterior"><i class="bi bi-chevron-left"></i></a></li>
                <?php else: ?>
                  <li class="is-disabled"><span aria-label="Anterior"><i class="bi bi-chevron-left"></i></span></li>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                  <?php if ($i === $pagina): ?>
                    <li class="is-active"><span aria-current="page"><?= $i ?></span></li>
                  <?php else: ?>
                    <li><a href="<?= linkPagina($i) ?>"><?= $i ?></a></li>
                  <?php endif; ?>
                <?php endfor; ?>

                <?php if ($pagina < $totalPaginas): ?>
                  <li><a href="<?= linkPagina($pagina + 1) ?>" aria-label="Próxima"><i class="bi bi-chevron-right"></i></a></li>
                <?php else: ?>
                  <li class="is-disabled"><span aria-label="Próxima"><i class="bi bi-chevron-right"></i></span></li>
                <?php endif; ?>
              </ul>
            </nav>
            <?php endif; ?>
          </div>
        </section>

      </main>

      <!-- =====================================================================
           MODAL DE CONFIRMAÇÃO DE EXCLUSÃO
           O usuarios.js preenche o nome e o id ao clicar na lixeira.
           O id vai no campo oculto e é enviado via POST.
           ===================================================================== -->
      <div class="modal" id="modalExcluir" role="dialog" aria-modal="true" aria-labelledby="modalExcluirTitulo" aria-hidden="true">
        <div class="modal__dialog">
          <form id="formExcluir" action="usuarios-excluir.php" method="post">
            <?= csrfCampo() ?>
            <input type="hidden" name="id" id="excluirId" value="">
            <div class="modal__body">
              <div class="modal__icon modal__icon--danger"><i class="bi bi-trash3"></i></div>
              <h3 class="modal__title" id="modalExcluirTitulo">Excluir usuário?</h3>
              <p class="modal__text">Você está prestes a excluir <strong id="excluirNome"></strong>. Esta ação não poderá ser desfeita.</p>
            </div>
            <div class="modal__footer">
              <button type="button" class="btn btn-light" data-modal-close>Cancelar</button>
              <button type="submit" class="btn btn-danger"><i class="bi bi-trash3"></i> Sim, excluir</button>
            </div>
          </form>
        </div>
      </div>

<?php
$scripts = ['usuarios.js'];
require_once '../includes/footer.php';
?>
