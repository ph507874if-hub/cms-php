<?php
/* ==========================================================================
   Proteção das páginas do painel
   --------------------------------------------------------------------------
   Inclua este arquivo na PRIMEIRA linha de toda página dentro de /pages:
       require_once '../includes/auth.php';

   Quem não estiver logado é enviado para a tela de login.
   Depois do include ficam disponíveis:
       $pdo            → conexão com o banco
       $usuarioLogado  → dados de quem está logado (id, nome, email, perfil...)
       ehAdmin()       → true se o usuário logado é administrador
       exigirAdmin()   → bloqueia a página para quem não é administrador
   ========================================================================== */

require_once __DIR__ . '/sessao.php';

// 1) Existe sessão? Se não, tenta entrar pelo cookie "Manter conectado".
if (!isset($_SESSION['usuario_id']) && !entrarPeloCookie($pdo)) {
    definirMensagem('Faça login para acessar o painel.', 'info');
    redirecionar('../index.php');
}

// 2) Busca os dados atuais no banco a cada página. Assim, se o usuário for
//    excluído ou desativado, ele perde o acesso imediatamente.
$sql = $pdo->prepare('SELECT id, nome, email, usuario, foto, perfil, status FROM usuarios WHERE id = ?');
$sql->execute([$_SESSION['usuario_id']]);
$usuarioLogado = $sql->fetch();

if (!$usuarioLogado || (int) $usuarioLogado['status'] !== 1) {
    encerrarSessao($pdo);
    definirMensagem('Seu acesso ao painel foi encerrado. Procure um administrador.', 'warning');
    redirecionar('../index.php');
}

// Mantém a sessão igual ao banco (o nome ou o perfil podem ter sido alterados)
$_SESSION['usuario_nome']   = $usuarioLogado['nome'];
$_SESSION['usuario_perfil'] = $usuarioLogado['perfil'];


/** O usuário logado é administrador? */
function ehAdmin(): bool
{
    return ($_SESSION['usuario_perfil'] ?? '') === 'admin';
}

/**
 * Use no início das páginas que só administradores podem acessar:
 *     require_once '../includes/auth.php';
 *     exigirAdmin();
 */
function exigirAdmin(): void
{
    if (!ehAdmin()) {
        definirMensagem('Você não tem permissão para acessar essa página.', 'danger');
        redirecionar('dashboard.php');
    }
}
