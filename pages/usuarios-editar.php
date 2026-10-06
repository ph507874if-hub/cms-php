<?php
/* ==========================================================================
   Usuários — edição (o "U" do CRUD: Update)
   --------------------------------------------------------------------------
   Endereço: usuarios-editar.php?id=5
     GET  → busca o registro (SELECT) e preenche o formulário
     POST → valida e grava as alterações (UPDATE)

   Quem pode acessar:
     • Administrador → edita qualquer usuário
     • Editor        → edita apenas o próprio cadastro ("Meu perfil"),
                       sem poder mudar o perfil nem o status
   ========================================================================== */

require_once '../includes/auth.php';
require_once '../config/conexao.php';
require_once '../includes/usuarios-funcoes.php';

// Sem ?id= na URL, abre o cadastro de quem está logado
$id = (int) get('id');
if ($id === 0) {
    $id = (int) $usuarioLogado['id'];
}

$proprioPerfil = $id === (int) $usuarioLogado['id'];

if (!$proprioPerfil) {
    exigirAdmin();
}

$voltarPara = ehAdmin() ? 'usuarios.php' : 'dashboard.php';

/* ---- Busca o registro que será editado ---------------------------------- */
$sql = $pdo->prepare('SELECT * FROM usuarios WHERE id = ?');
$sql->execute([$id]);
$registro = $sql->fetch();

if (!$registro) {
    definirMensagem('Usuário não encontrado.', 'danger');
    redirecionar($voltarPara);
}

$dados = $registro; // na primeira exibição, o formulário mostra os dados do banco
$erros = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfValidar();

    $dados          = lerFormularioUsuario();
    $dados['foto']  = $registro['foto'];
    $senha          = post('senha', false);
    $confirmarSenha = post('confirmar_senha', false);

    // Só administradores alteram perfil e status. Para os demais, valem os
    // valores do banco — mesmo que alguém adultere o formulário pelo navegador.
    if (!ehAdmin()) {
        $dados['perfil'] = $registro['perfil'];
        $dados['status'] = $registro['status'];
    }

    // 1) Valida os campos
    $erros = validarUsuario($pdo, $dados, $senha, $confirmarSenha, $id);

    // 2) Regras de segurança
    $eraAdminAtivo      = $registro['perfil'] === 'admin' && (int) $registro['status'] === 1;
    $continuaAdminAtivo = $dados['perfil'] === 'admin' && (int) $dados['status'] === 1;

    if ($proprioPerfil && (int) $dados['status'] !== 1) {
        $erros['status'] = 'Você não pode desativar a própria conta.';
    } elseif ($eraAdminAtivo && !$continuaAdminAtivo && !existeOutroAdminAtivo($pdo, $id)) {
        $erros['perfil'] = 'Este é o único administrador ativo: ele precisa continuar ativo e como administrador.';
    }

    // 3) Foto: nova imagem, remoção ou nenhuma mudança
    $foto     = $registro['foto'];
    $erroFoto = null;
    if (!$erros) {
        $novaFoto = isset($_FILES['foto']) ? salvarImagem($_FILES['foto'], 'usuarios', $erroFoto) : null;

        if ($erroFoto) {
            $erros['foto'] = $erroFoto;
        } elseif ($novaFoto) {
            $foto = $novaFoto;
        } elseif (post('remover_foto') === '1') {
            $foto = null;
        }
    }

    // 4) Grava no banco
    if (!$erros) {
        $sql = $pdo->prepare(
            'UPDATE usuarios
                SET nome = :nome, email = :email, usuario = :usuario, telefone = :telefone,
                    foto = :foto, biografia = :biografia, perfil = :perfil, status = :status
              WHERE id = :id'
        );
        $sql->execute([
            ':nome'      => $dados['nome'],
            ':email'     => $dados['email'],
            ':usuario'   => $dados['usuario'],
            ':telefone'  => $dados['telefone'] !== '' ? $dados['telefone'] : null,
            ':foto'      => $foto,
            ':biografia' => $dados['biografia'],
            ':perfil'    => $dados['perfil'],
            ':status'    => $dados['status'],
            ':id'        => $id,
        ]);

        // A senha só é alterada se o campo foi preenchido
        if ($senha !== '') {
            $sql = $pdo->prepare('UPDATE usuarios SET senha = ? WHERE id = ?');
            $sql->execute([password_hash($senha, PASSWORD_DEFAULT), $id]);

            // Senha nova: os logins salvos em outros aparelhos ("Manter conectado") deixam de valer
            $sql = $pdo->prepare('DELETE FROM usuarios_tokens WHERE usuario_id = ?');
            $sql->execute([$id]);
        }

        // Se a foto mudou ou foi removida, apaga o arquivo antigo do disco
        if ($foto !== $registro['foto']) {
            apagarImagem($registro['foto']);
        }

        definirMensagem($proprioPerfil ? 'Seu perfil foi atualizado!' : 'Alterações salvas com sucesso!');
        redirecionar($voltarPara);
    }
}

// Variáveis usadas pelo formulário (includes/usuario-form.php)
$modo              = 'editar';
$acao              = 'usuarios-editar.php?id=' . $id;
$mostrarPermissoes = ehAdmin();
$podeExcluir       = ehAdmin() && !$proprioPerfil;

$titulo    = $proprioPerfil ? 'Meu perfil' : 'Editar usuário';
$menuAtivo = ehAdmin() ? 'usuarios' : '';
$usaEditor = true;
require_once '../includes/header.php';
?>

      <main class="content">

        <div class="page-header">
          <div>
            <ol class="breadcrumb">
              <li><a href="dashboard.php">Dashboard</a></li>
              <?php if (ehAdmin()): ?>
                <li><a href="usuarios.php">Usuários</a></li>
              <?php endif; ?>
              <li><?= $proprioPerfil ? 'Meu perfil' : 'Editar' ?></li>
            </ol>
            <h1><?= e($titulo) ?></h1>
            <p>Atualize os dados de acesso e as informações do usuário.</p>
          </div>
          <div class="page-header__actions">
            <a href="<?= $voltarPara ?>" class="btn btn-light"><i class="bi bi-arrow-left"></i> <?= ehAdmin() ? 'Voltar para a lista' : 'Voltar ao dashboard' ?></a>
          </div>
        </div>

        <?php exibirMensagem(); ?>

<?php require '../includes/usuario-form.php'; ?>

      </main>

      <?php if ($podeExcluir): ?>
      <!-- Modal de exclusão: fica FORA do formulário principal (não se coloca um <form> dentro de outro) -->
      <div class="modal" id="modalExcluir" role="dialog" aria-modal="true" aria-labelledby="modalExcluirTitulo" aria-hidden="true">
        <div class="modal__dialog">
          <form action="usuarios-excluir.php" method="post">
            <?= csrfCampo() ?>
            <input type="hidden" name="id" value="<?= $id ?>">
            <div class="modal__body">
              <div class="modal__icon modal__icon--danger"><i class="bi bi-trash3"></i></div>
              <h3 class="modal__title" id="modalExcluirTitulo">Excluir usuário?</h3>
              <p class="modal__text">Você está prestes a excluir <strong><?= e($registro['nome']) ?></strong>. Esta ação não poderá ser desfeita.</p>
            </div>
            <div class="modal__footer">
              <button type="button" class="btn btn-light" data-modal-close>Cancelar</button>
              <button type="submit" class="btn btn-danger"><i class="bi bi-trash3"></i> Sim, excluir</button>
            </div>
          </form>
        </div>
      </div>
      <?php endif; ?>

<?php
$scripts = ['usuario-form.js'];
require_once '../includes/footer.php';
?>
