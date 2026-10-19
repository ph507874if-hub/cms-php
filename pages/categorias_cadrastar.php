```php
<?php
/* Cadastro de categorias */

require_once '../includes/auth.php';
require_once '../config/conexao.php';

exigirAdmin();

$dados = [
    'nome' => '',
    'descricao' => '',
    'status' => 1,
];

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
            'INSERT INTO categorias (nome, descricao, status)
             VALUES (:nome, :descricao, :status)'
        );

        $sql->execute([
            ':nome' => $dados['nome'],
            ':descricao' => $dados['descricao'] !== ''
                ? $dados['descricao']
                : null,
            ':status' => $dados['status'],
        ]);

        definirMensagem('Categoria cadastrada com sucesso!');
        redirecionar('categorias.php');
    }
}

$titulo = 'Nova categoria';
$menuAtivo = 'categorias';

require_once '../includes/header.php';
?>

<main class="content">

    <div class="page-header">
        <div>
            <ol class="breadcrumb">
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="categorias.php">Categorias</a></li>
                <li>Cadastrar</li>
            </ol>

            <h1>Nova categoria</h1>
            <p>Preencha os dados da categoria.</p>
        </div>

        <div class="page-header__actions">
            <a href="categorias.php" class="btn btn-light">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </div>
    </div>

    <?php exibirMensagem(); ?>

    <section class="card">
        <form action="categorias-cadastrar.php" method="post">
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
                              rows="4"><?= e($dados['descricao']) ?></textarea>
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
                    <i class="bi bi-check-lg"></i> Salvar categoria
                </button>
            </div>
        </form>
    </section>

</main>

<?php require_once '../includes/footer.php'; ?>
```