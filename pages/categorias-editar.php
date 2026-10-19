```php
<?php
/* Edição de categorias */

require_once '../includes/auth.php';
require_once '../config/conexao.php';

exigirAdmin();

$id = (int) get('id');

if ($id <= 0) {
    definirMensagem('Categoria inválida.', 'danger');
    redirecionar('categorias.php');
}

$sql = $pdo->prepare(
    'SELECT id, nome, descricao, status
       FROM categorias
      WHERE id = ?'
);

$sql->execute([$id]);
$categoria = $sql->fetch();

if (!$categoria) {
    definirMensagem('Categoria não encontrada.', 'danger');
    redirecionar('categorias.php');
}

$dados = $categoria;
$erros = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfValidar();

    $dados['nome'] = trim(post('nome'));
    $dados['descricao'] = trim(post('descricao'));
    $dados['status'] = post('status') === '0' ? 0 : 1;

    if ($dados['nome'] === '') {
        $erros['nome'] = 'Informe o nome da categoria.';
    } elseif (mb_strlen($dados['nome']) > 100) {
        $erros['nome'] = 'O nome deve ter no máximo 100 caracteres.';
    }

    if (!$erros) {
        $sql = $pdo->prepare(
            'UPDATE categorias
                SET nome = :nome,
                    descricao = :descricao,
                    status = :status
              WHERE id = :id'
        );

        $sql->execute([
            ':nome' => $dados['nome'],
            ':descricao' => $dados['descricao'] !== ''
                ? $dados['descricao']
                : null,
            ':status' => $dados['status'],
            ':id' => $id,
        ]);

        definirMensagem('Categoria atualizada com sucesso!');
        redirecionar('categorias.php');
    }
}

$titulo = 'Editar categoria';
$menuAtivo = 'categorias';

require_once '../includes/header.php';
?>

<main class="content">

    <div class="page-header">
        <div>
            <ol class="breadcrumb">
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="categorias.php">Categorias</a></li>
                <li>Editar</li>
            </ol>

            <h1>Editar categoria</h1>
            <p>Atualize as informações da categoria.</p>
        </div>

        <div class="page-header__actions">
            <a href="categorias.php" class="btn btn-light">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </div>
    </div>

    <?php exibirMensagem(); ?>

    <section class="card">
        <form action="categorias-editar.php?id=<?= $id ?>" method="post">
            <?= csrfCampo() ?>

            <div class="card__body">

                <div class="form-group">
                    <label for="nome">Nome da categoria *</label>

                    <input type="text"
                           id="nome"
                           name="nome"
                           class="form-control"
                           maxlength="100"
                           required
                           value="<?= e($dados['nome']) ?>">

                    <?php if (isset($erros['nome'])): ?>
                        <p class="text-danger">
                            <?= e($erros['nome']) ?>
                        </p>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="descricao">Descrição</label>

                    <textarea id="descricao"
                              name="descricao"
                              class="form-control"
                              rows="4"><?= e($dados['descricao'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label for="status">Status</label>

                    <select id="status"
                            name="status"
                            class="form-select">
                        <option value="1"
                            <?= (int) $dados['status'] === 1 ? 'selected' : '' ?>>
                            Ativa
                        </option>

                        <option value="0"
                            <?= (int) $dados['status'] === 0 ? 'selected' : '' ?>>
                            Inativa
                        </option>
                    </select>
                </div>

            </div>

            <div class="card__footer">
                <a href="categorias.php" class="btn btn-light">
                    Cancelar
                </a>

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg"></i> Salvar alterações
                </button>
            </div>
        </form>
    </section>

</main>

<?php require_once '../includes/footer.php'; ?>
``` 