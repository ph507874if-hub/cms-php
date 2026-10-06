<?php
/* ==========================================================================
   Usuários — exclusão (o "D" do CRUD: Delete)
   --------------------------------------------------------------------------
   Recebe o id por POST (formulário do modal de confirmação), executa o
   DELETE e volta para a listagem com uma mensagem.

   Por que POST e não um link (GET)? Porque páginas que ALTERAM dados não
   devem funcionar por link: um link pode ser aberto sem querer, pelo
   histórico do navegador ou por outro site.
   ========================================================================== */

require_once '../includes/auth.php';
require_once '../config/conexao.php';

exigirAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirecionar('usuarios.php');
}

csrfValidar('usuarios.php');

$id = (int) post('id');

$sql = $pdo->prepare('SELECT id, nome, foto FROM usuarios WHERE id = ?');
$sql->execute([$id]);
$usuario = $sql->fetch();

if (!$usuario) {
    definirMensagem('Usuário não encontrado.', 'danger');
    redirecionar('usuarios.php');
}

// Ninguém exclui a própria conta. Como só administradores chegam até aqui,
// essa regra também garante que o painel nunca fique sem administrador.
if ($usuario['id'] == $usuarioLogado['id']) {
    definirMensagem('Você não pode excluir a própria conta.', 'danger');
    redirecionar('usuarios.php');
}

try {
    $sql = $pdo->prepare('DELETE FROM usuarios WHERE id = ?');
    $sql->execute([$id]);
} catch (PDOException $erro) {
    // Código 23000 = violação de integridade. Acontece quando outra tabela tem
    // uma chave estrangeira apontando para este usuário (ex.: notícias cujo
    // autor é ele) e a chave não permite a exclusão.
    if ($erro->getCode() !== '23000') { throw $erro; }

    definirMensagem('Não é possível excluir "' . $usuario['nome'] . '": existem registros vinculados a este usuário. Desative-o em vez de excluir.', 'danger');
    redirecionar('usuarios.php');
}

// O registro saiu do banco; falta apagar o arquivo da foto na pasta uploads
apagarImagem($usuario['foto']);

definirMensagem('Usuário "' . $usuario['nome'] . '" excluído com sucesso!');
redirecionar('usuarios.php');
