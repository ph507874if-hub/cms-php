<?php
/* ==========================================================================
   Processa o login (recebe o formulário de index.php)
   --------------------------------------------------------------------------
   1. Busca o usuário pelo e-mail
   2. Confere a senha com password_verify()
   3. Cria a sessão e redireciona para o painel
   ========================================================================== */

require_once __DIR__ . '/includes/sessao.php';

// Esta página só aceita o envio do formulário (POST)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirecionar('index.php');
}

csrfValidar('index.php');

$email = post('email');
$senha = post('senha', false); // a senha não passa pelo trim(): espaços fazem parte dela

/** Volta para a tela de login exibindo um erro. */
function falharLogin(string $mensagem, string $email): void
{
    $_SESSION['login_email'] = $email;
    definirMensagem($mensagem, 'danger');
    redirecionar('index.php');
}

if ($email === '' || $senha === '') {
    falharLogin('Informe o e-mail e a senha.', $email);
}

$sql = $pdo->prepare('SELECT id, nome, senha, perfil, status FROM usuarios WHERE email = ?');
$sql->execute([$email]);
$usuario = $sql->fetch();

// A mensagem é a mesma para "e-mail não existe" e "senha errada":
// assim ninguém consegue descobrir quais e-mails estão cadastrados.
if (!$usuario || !password_verify($senha, $usuario['senha'])) {
    falharLogin('E-mail ou senha incorretos.', $email);
}

if ((int) $usuario['status'] !== 1) {
    falharLogin('Sua conta está inativa. Procure um administrador.', $email);
}

// Se o PHP passou a usar um algoritmo de hash mais forte, atualiza a senha gravada
if (password_needs_rehash($usuario['senha'], PASSWORD_DEFAULT)) {
    $sql = $pdo->prepare('UPDATE usuarios SET senha = ?, atualizado_em = atualizado_em WHERE id = ?');
    $sql->execute([password_hash($senha, PASSWORD_DEFAULT), $usuario['id']]);
}

registrarLogin($pdo, $usuario);

// Aproveita o login para apagar os tokens vencidos do "Manter conectado"
$pdo->exec('DELETE FROM usuarios_tokens WHERE expira_em < NOW()');

if (post('lembrar') === '1') {
    criarTokenLembrar($pdo, (int) $usuario['id']);
}

definirMensagem('Bem-vindo(a) de volta, ' . primeiroNome($usuario['nome']) . '!', 'info');
redirecionar('pages/dashboard.php');
