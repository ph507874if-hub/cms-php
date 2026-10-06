<?php
/* ==========================================================================
   Sessão e funções de autenticação
   --------------------------------------------------------------------------
   Este arquivo inicia a sessão e reúne as funções de login/logout.
   Ele NÃO protege a página — quem faz isso é o includes/auth.php.

   É incluído diretamente apenas nas páginas públicas (index.php, login.php e
   logout.php). Nas páginas do painel, use sempre o auth.php.
   ========================================================================== */

require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/funcoes.php';

// Opção "Manter conectado"
define('COOKIE_LEMBRAR', 'cms_lembrar'); // nome do cookie
define('DIAS_LEMBRAR', 30);              // validade em dias

if (session_status() === PHP_SESSION_NONE) {
    session_name('cms_sessao');
    session_set_cookie_params([
        'lifetime' => 0,      // o cookie da sessão some ao fechar o navegador
        'path'     => '/',
        'httponly' => true,   // o JavaScript não consegue ler o cookie
        'samesite' => 'Lax',  // o cookie não é enviado em formulários de outros sites
    ]);
    session_start();
}


/**
 * Marca o usuário como logado.
 * $usuario é a linha da tabela "usuarios" (precisa de id, nome e perfil).
 */
function registrarLogin(PDO $pdo, array $usuario): void
{
    // Troca o ID da sessão a cada login: impede o ataque de "fixação de sessão"
    session_regenerate_id(true);

    $_SESSION['usuario_id']     = (int) $usuario['id'];
    $_SESSION['usuario_nome']   = $usuario['nome'];
    $_SESSION['usuario_perfil'] = $usuario['perfil'];

    // "atualizado_em = atualizado_em" mantém a data da última EDIÇÃO do cadastro.
    // Sem isso, o ON UPDATE CURRENT_TIMESTAMP da coluna mudaria a data a cada login.
    $sql = $pdo->prepare('UPDATE usuarios SET ultimo_acesso = NOW(), atualizado_em = atualizado_em WHERE id = ?');
    $sql->execute([$usuario['id']]);
}


/** Grava ou apaga o cookie do "Manter conectado". */
function gravarCookieLembrar(string $valor, int $expira): void
{
    setcookie(COOKIE_LEMBRAR, $valor, [
        'expires'  => $expira,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}


/**
 * "Manter conectado": cria um token, guarda o HASH dele no banco e envia o
 * token para o navegador em um cookie (formato seletor:token).
 */
function criarTokenLembrar(PDO $pdo, int $usuarioId): void
{
    $seletor = bin2hex(random_bytes(12)); // 24 caracteres: localiza a linha no banco
    $token   = bin2hex(random_bytes(32)); // segredo: só o navegador conhece
    $expira  = time() + DIAS_LEMBRAR * 24 * 60 * 60;

    $sql = $pdo->prepare('INSERT INTO usuarios_tokens (usuario_id, seletor, token_hash, expira_em) VALUES (?, ?, ?, ?)');
    $sql->execute([$usuarioId, $seletor, hash('sha256', $token), date('Y-m-d H:i:s', $expira)]);

    gravarCookieLembrar($seletor . ':' . $token, $expira);
}


/** Apaga o token deste navegador (no banco e no cookie). */
function apagarTokenLembrar(PDO $pdo): void
{
    if (!isset($_COOKIE[COOKIE_LEMBRAR]) || !is_string($_COOKIE[COOKIE_LEMBRAR])) { return; }

    $seletor = explode(':', $_COOKIE[COOKIE_LEMBRAR])[0];
    $sql = $pdo->prepare('DELETE FROM usuarios_tokens WHERE seletor = ?');
    $sql->execute([$seletor]);

    gravarCookieLembrar('', time() - 3600); // data no passado = apagar o cookie
    unset($_COOKIE[COOKIE_LEMBRAR]);
}


/**
 * Tenta fazer o login usando o cookie do "Manter conectado".
 * Retorna true se o usuário foi autenticado.
 */
function entrarPeloCookie(PDO $pdo): bool
{
    if (!isset($_COOKIE[COOKIE_LEMBRAR]) || !is_string($_COOKIE[COOKIE_LEMBRAR])) { return false; }

    $partes = explode(':', $_COOKIE[COOKIE_LEMBRAR]);
    if (count($partes) !== 2) {
        apagarTokenLembrar($pdo);
        return false;
    }
    list($seletor, $token) = $partes;

    $sql = $pdo->prepare(
        'SELECT t.token_hash, u.id, u.nome, u.perfil
           FROM usuarios_tokens t
           JOIN usuarios u ON u.id = t.usuario_id
          WHERE t.seletor = ? AND t.expira_em > NOW() AND u.status = 1'
    );
    $sql->execute([$seletor]);
    $linha = $sql->fetch();

    // hash_equals() compara os dois textos sem "vazar" informação pelo tempo de resposta
    if (!$linha || !hash_equals($linha['token_hash'], hash('sha256', $token))) {
        apagarTokenLembrar($pdo);
        return false;
    }

    // Deu certo: entra e troca o token por um novo (cada token só vale uma vez)
    $sql = $pdo->prepare('DELETE FROM usuarios_tokens WHERE seletor = ?');
    $sql->execute([$seletor]);

    registrarLogin($pdo, $linha);
    criarTokenLembrar($pdo, (int) $linha['id']);
    return true;
}


/** Encerra a sessão do usuário (logout). */
function encerrarSessao(PDO $pdo): void
{
    apagarTokenLembrar($pdo);

    $_SESSION = [];
    session_destroy();

    // Começa uma sessão nova e vazia (permite exibir a mensagem de despedida)
    session_start();
    session_regenerate_id(true);
}
