```php
<?php
/* Exclusão de categorias */

require_once '../includes/auth.php';
require_once '../config/conexao.php';

exigirAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirecionar('categorias.php');
}

csrfValidar('categorias.php');

$id = (int) post('id');

$sql = $pdo->prepare(
    'SELECT id, nome FROM categorias WHERE id = ?'
);
$sql->execute([$id]);

$categoria = $sql->fetch();

if (!$categoria) {
    definirMensagem('Categoria não encontrada.', 'danger');
    redirecionar('categorias.php');
}

try {
    $sql = $pdo->prepare(
        'DELETE FROM categorias WHERE id = ?'
    );

    $sql->execute([$id]);

} catch (PDOException $erro) {
    if ($erro->getCode() !== '23000') {
        throw $erro;
    }

    definirMensagem(
        'Não é possível excluir esta categoria porque existem registros vinculados a ela.',
        'danger'
    );

    redirecionar('categorias.php');
}

definirMensagem(
    'Categoria "' . $categoria['nome'] . '" excluída com sucesso!'
);

redirecionar('categorias.php');
```