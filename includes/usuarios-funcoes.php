<?php
/* ==========================================================================
   Funções do CRUD de usuários
   --------------------------------------------------------------------------
   Usadas por pages/usuarios*.php. Ao criar o CRUD do seu tema, crie um
   arquivo parecido (ex.: includes/noticias-funcoes.php) com a leitura e a
   validação do seu formulário.
   ========================================================================== */

/** Etiqueta colorida do perfil (Administrador / Editor). */
function badgePerfil(string $perfil): string
{
    return $perfil === 'admin'
        ? '<span class="badge badge-primary"><i class="bi bi-shield-lock"></i> Administrador</span>'
        : '<span class="badge badge-info"><i class="bi bi-pencil-square"></i> Editor</span>';
}

/** Etiqueta colorida do status (Ativo / Inativo). */
function badgeStatus($status): string
{
    return (int) $status === 1
        ? '<span class="badge badge-success badge-dot">Ativo</span>'
        : '<span class="badge badge-gray badge-dot">Inativo</span>';
}


/**
 * Lê os campos do formulário de usuário enviados por POST.
 * (A senha e a foto são tratadas à parte, em cada página.)
 */
function lerFormularioUsuario(): array
{
    return [
        'nome'      => post('nome'),
        'email'     => mb_strtolower(post('email')),
        'telefone'  => post('telefone'),
        'usuario'   => mb_strtolower(post('usuario')),
        'biografia' => limparHtml(post('biografia')),     // HTML do editor, já sem código perigoso
        'perfil'    => post('perfil'),
        'status'    => isset($_POST['status']) ? 1 : 0,   // checkbox desmarcado não é enviado
    ];
}


/**
 * Valida os dados do usuário NO SERVIDOR (as mesmas regras do usuario-form.js).
 * A validação do JavaScript pode ser burlada; a do PHP, não.
 *
 *   $id → informe na edição, para o registro não ser comparado com ele mesmo
 *         na verificação de e-mail/usuário duplicado.
 *
 * Retorna um array [campo => mensagem]. Array vazio = tudo certo.
 */
function validarUsuario(PDO $pdo, array $dados, string $senha, string $confirmarSenha, ?int $id = null): array
{
    $erros = [];

    // Nome
    if (mb_strlen($dados['nome']) < 3) {
        $erros['nome'] = 'Informe o nome completo (mínimo 3 caracteres).';
    } elseif (mb_strlen($dados['nome']) > 100) {
        $erros['nome'] = 'O nome deve ter no máximo 100 caracteres.';
    }

    // E-mail
    if ($dados['email'] === '') {
        $erros['email'] = 'Informe o e-mail.';
    } elseif (!filter_var($dados['email'], FILTER_VALIDATE_EMAIL) || mb_strlen($dados['email']) > 150) {
        $erros['email'] = 'Digite um e-mail válido.';
    } else {
        // "id <> ?" ignora o próprio registro durante a edição
        $sql = $pdo->prepare('SELECT id FROM usuarios WHERE email = ? AND id <> ?');
        $sql->execute([$dados['email'], (int) $id]);
        if ($sql->fetch()) {
            $erros['email'] = 'Este e-mail já está cadastrado.';
        }
    }

    // Telefone (opcional)
    if ($dados['telefone'] !== '' && !preg_match('/^[0-9()+\-\s]{8,20}$/', $dados['telefone'])) {
        $erros['telefone'] = 'Digite um telefone válido. Ex.: (77) 99999-9999';
    }

    // Nome de usuário
    if (!preg_match('/^[a-z0-9._]{3,30}$/', $dados['usuario'])) {
        $erros['usuario'] = 'Use de 3 a 30 caracteres: letras minúsculas, números, ponto ou _.';
    } else {
        $sql = $pdo->prepare('SELECT id FROM usuarios WHERE usuario = ? AND id <> ?');
        $sql->execute([$dados['usuario'], (int) $id]);
        if ($sql->fetch()) {
            $erros['usuario'] = 'Este nome de usuário já está em uso.';
        }
    }

    // Senha: obrigatória no cadastro; na edição, só valida se foi preenchida
    if ($id === null || $senha !== '') {
        if (strlen($senha) < 8) {
            $erros['senha'] = 'A senha deve ter pelo menos 8 caracteres.';
        } elseif (strlen($senha) > 72) {
            $erros['senha'] = 'A senha deve ter no máximo 72 caracteres.'; // limite do algoritmo bcrypt
        }
        if ($confirmarSenha !== $senha) {
            $erros['confirmar_senha'] = 'As senhas não conferem.';
        }
    }

    // Biografia (editor rich text)
    if (htmlVazio($dados['biografia'])) {
        $erros['biografia'] = 'Escreva uma breve apresentação.';
    }

    // Perfil
    if (!in_array($dados['perfil'], ['admin', 'editor'], true)) {
        $erros['perfil'] = 'Selecione um perfil de acesso.';
    }

    return $erros;
}


/**
 * Existe algum OUTRO administrador ativo além do usuário informado?
 * Usada para impedir que o painel fique sem nenhum administrador.
 */
function existeOutroAdminAtivo(PDO $pdo, int $id): bool
{
    $sql = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE perfil = 'admin' AND status = 1 AND id <> ?");
    $sql->execute([$id]);
    return $sql->fetchColumn() > 0;
}
