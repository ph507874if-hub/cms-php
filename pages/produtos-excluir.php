<?php
require_once '../includes/auth.php';
require_once '../config/conexao.php';
require_once 'produto-form-funcoes.php';
exigirAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirecionar('produtos.php');
csrfValidar('produtos.php');

$id = (int)post('id');
$sql = $pdo->prepare('SELECT id, nome, imagem FROM produtos WHERE id = ?');
$sql->execute([$id]); $produto = $sql->fetch();

if (!$produto) {
    definirMensagem('Produto não encontrado.', 'danger');
    redirecionar('produtos.php');
}

try {
    $sql = $pdo->prepare('DELETE FROM produtos WHERE id = ?');
    $sql->execute([$id]);
} catch (PDOException $erro) {
    if ($erro->getCode() !== '23000') throw $erro;
    definirMensagem('Não é possível excluir este produto porque ele está vinculado a pedidos ou outros registros.', 'danger');
    redirecionar('produtos.php');
}

apagarImagemProduto($produto['imagem']);
definirMensagem('Produto "' . $produto['nome'] . '" excluído com sucesso!');
redirecionar('produtos.php');
