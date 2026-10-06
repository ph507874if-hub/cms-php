<?php
/* ==========================================================================
   Usuários — cadastro (o "C" do CRUD: Create)
   --------------------------------------------------------------------------
   A mesma página exibe o formulário (GET) e recebe o envio (POST):
     GET  → mostra o formulário vazio
     POST → valida; se estiver tudo certo, faz o INSERT e redireciona;
            se houver erro, mostra o formulário de novo com os dados digitados
   ========================================================================== */

require_once '../includes/auth.php';
require_once '../config/conexao.php';
require_once '../includes/usuarios-funcoes.php';

exigirAdmin();

// Valores iniciais do formulário
$dados = [
    'nome' => '', 'email' => '', 'telefone' => '', 'usuario' => '',
    'biografia' => '', 'perfil' => '', 'status' => 1,
];
$erros = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfValidar();

    $dados          = lerFormularioUsuario();
    $senha          = post('senha', false);
    $confirmarSenha = post('confirmar_senha', false);

    // 1) Valida os campos
    $erros = validarUsuario($pdo, $dados, $senha, $confirmarSenha);

    // 2) Só salva a foto se o restante estiver correto (evita arquivos "órfãos")
    $foto = null;
    if (!$erros && isset($_FILES['foto'])) {
        $foto = salvarImagem($_FILES['foto'], 'usuarios', $erroFoto);
        if ($erroFoto) {
            $erros['foto'] = $erroFoto;
        }
    }

    // 3) Grava no banco
    if (!$erros) {
        $sql = $pdo->prepare(
            'INSERT INTO usuarios (nome, email, usuario, senha, telefone, foto, biografia, perfil, status)
             VALUES (:nome, :email, :usuario, :senha, :telefone, :foto, :biografia, :perfil, :status)'
        );
        $sql->execute([
            ':nome'      => $dados['nome'],
            ':email'     => $dados['email'],
            ':usuario'   => $dados['usuario'],
            ':senha'     => password_hash($senha, PASSWORD_DEFAULT), // nunca grave a senha "pura"
            ':telefone'  => $dados['telefone'] !== '' ? $dados['telefone'] : null,
            ':foto'      => $foto,
            ':biografia' => $dados['biografia'],
            ':perfil'    => $dados['perfil'],
            ':status'    => $dados['status'],
        ]);

        // Redirecionar depois do POST evita cadastrar de novo ao apertar F5
        definirMensagem('Usuário cadastrado com sucesso!');
        redirecionar('usuarios.php');
    }
}

// Variáveis usadas pelo formulário (includes/usuario-form.php)
$modo              = 'cadastrar';
$acao              = 'usuarios-cadastrar.php';
$voltarPara        = 'usuarios.php';
$mostrarPermissoes = true;

$titulo    = 'Novo usuário';
$menuAtivo = 'usuarios';
$usaEditor = true;
require_once '../includes/header.php';
?>

      <main class="content">

        <div class="page-header">
          <div>
            <ol class="breadcrumb">
              <li><a href="dashboard.php">Dashboard</a></li>
              <li><a href="usuarios.php">Usuários</a></li>
              <li>Cadastrar</li>
            </ol>
            <h1>Novo usuário</h1>
            <p>Cadastre um novo administrador ou editor do painel.</p>
          </div>
          <div class="page-header__actions">
            <a href="usuarios.php" class="btn btn-light"><i class="bi bi-arrow-left"></i> Voltar para a lista</a>
          </div>
        </div>

        <?php exibirMensagem(); ?>

<?php require '../includes/usuario-form.php'; ?>

      </main>

<?php
$scripts = ['usuario-form.js'];
require_once '../includes/footer.php';
?>
