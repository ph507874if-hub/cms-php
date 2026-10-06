<?php
/* ==========================================================================
   Funções auxiliares usadas em todo o painel
   --------------------------------------------------------------------------
   Reaproveite estas funções nos CRUDs do tema da sua dupla.
   ========================================================================== */

// Pasta raiz do projeto (a que contém index.php)
define('RAIZ', dirname(__DIR__));


/* --------------------------------------------------------------------------
   Saída e entrada de dados
   -------------------------------------------------------------------------- */

/** Escapa um texto para exibir no HTML com segurança (evita XSS). */
function e($texto): string
{
    return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
}

/** Lê um campo de $_POST como texto. Por padrão remove os espaços das pontas. */
function post(string $campo, bool $aparar = true): string
{
    $valor = $_POST[$campo] ?? '';
    if (!is_string($valor)) { return ''; }
    return $aparar ? trim($valor) : $valor;
}

/** Lê um parâmetro de $_GET (da URL) como texto, sem espaços nas pontas. */
function get(string $campo): string
{
    $valor = $_GET[$campo] ?? '';
    return is_string($valor) ? trim($valor) : '';
}

/** Redireciona para outra página e encerra o script. */
function redirecionar(string $url): void
{
    header('Location: ' . $url);
    exit;
}


/* --------------------------------------------------------------------------
   Mensagens de retorno ("flash messages")
   A mensagem é guardada na sessão, exibida UMA vez na página seguinte e
   apagada em seguida.
   -------------------------------------------------------------------------- */

/** Tipos: success | danger | warning | info */
function definirMensagem(string $texto, string $tipo = 'success'): void
{
    $_SESSION['mensagem'] = ['texto' => $texto, 'tipo' => $tipo];
}

function exibirMensagem(): void
{
    if (empty($_SESSION['mensagem'])) { return; }

    $mensagem = $_SESSION['mensagem'];
    unset($_SESSION['mensagem']);

    $icones = [
        'success' => 'bi-check-circle',
        'danger'  => 'bi-exclamation-octagon',
        'warning' => 'bi-exclamation-triangle',
        'info'    => 'bi-info-circle',
    ];
    $tipo = isset($icones[$mensagem['tipo']]) ? $mensagem['tipo'] : 'info';

    echo '<div class="alert alert-' . $tipo . '" role="alert">'
       . '<i class="bi ' . $icones[$tipo] . '"></i>'
       . '<div>' . e($mensagem['texto']) . '</div>'
       . '<button type="button" class="alert__close" aria-label="Fechar"><i class="bi bi-x-lg"></i></button>'
       . '</div>';
}


/* --------------------------------------------------------------------------
   Proteção contra CSRF
   Todo formulário enviado por POST leva um código secreto guardado na sessão.
   Assim, outro site não consegue enviar formulários "em nome" do usuário.
     No formulário:   <?= csrfCampo() ?>
     Ao processar:    csrfValidar();
   -------------------------------------------------------------------------- */

function csrfToken(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrfCampo(): string
{
    return '<input type="hidden" name="csrf" value="' . csrfToken() . '">';
}

/** Confere o código recebido. Se não bater, volta para a página anterior. */
function csrfValidar(string $voltarPara = ''): void
{
    if (hash_equals(csrfToken(), post('csrf'))) { return; }

    // Quando o envio ultrapassa o limite do servidor (post_max_size do php.ini),
    // o PHP descarta tudo e $_POST chega vazio.
    if (empty($_POST) && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
        definirMensagem('Os dados enviados ultrapassam o limite do servidor. Use imagens menores.', 'danger');
    } else {
        definirMensagem('Sua sessão expirou. Tente novamente.', 'warning');
    }
    redirecionar($voltarPara !== '' ? $voltarPara : $_SERVER['REQUEST_URI']);
}


/* --------------------------------------------------------------------------
   Erros de validação nos formulários
   $erros é um array [campo => mensagem] montado na validação do PHP.
     <div class="form-group<?= erroClasse($erros, 'nome') ?>">
       <input ...>
       <div class="invalid-feedback"><?= erroTexto($erros, 'nome') ?></div>
     </div>
   -------------------------------------------------------------------------- */

function erroClasse(array $erros, string $campo): string
{
    return isset($erros[$campo]) ? ' has-error' : '';
}

function erroTexto(array $erros, string $campo): string
{
    return e($erros[$campo] ?? '');
}


/* --------------------------------------------------------------------------
   Formatação
   -------------------------------------------------------------------------- */

/** Data do banco (AAAA-MM-DD HH:MM:SS) → 31/12/2026 ou 31/12/2026 às 14:30 */
function formatarData(?string $data, bool $comHora = false): string
{
    if (empty($data)) { return '—'; }
    return date($comHora ? 'd/m/Y \à\s H:i' : 'd/m/Y', strtotime($data));
}

function nomePerfil(string $perfil): string
{
    return $perfil === 'admin' ? 'Administrador' : 'Editor';
}

/** "Maria da Silva" → "Maria" */
function primeiroNome(string $nome): string
{
    return explode(' ', trim($nome))[0];
}

/** "Maria da Silva" → "MS" (primeira letra do primeiro e do último nome) */
function iniciais(string $nome): string
{
    $partes = preg_split('/\s+/', trim($nome));
    $primeira = mb_substr($partes[0], 0, 1);
    $ultima   = count($partes) > 1 ? mb_substr(end($partes), 0, 1) : '';
    return mb_strtoupper($primeira . $ultima);
}

/**
 * Monta o avatar de um usuário: a foto, se existir, ou um círculo com as iniciais.
 * $usuario precisa ter as chaves id, nome e foto.
 */
function avatar(array $usuario, string $classeExtra = ''): string
{
    $classe = trim('avatar ' . $classeExtra);

    if (!empty($usuario['foto'])) {
        return '<img src="../' . e($usuario['foto']) . '" alt="" class="' . $classe . '">';
    }

    $cor = 'avatar--c' . (($usuario['id'] % 5) + 1); // 5 cores definidas no style.css
    return '<span class="' . $classe . ' ' . $cor . '">' . e(iniciais($usuario['nome'])) . '</span>';
}


/* --------------------------------------------------------------------------
   Upload de imagens
   -------------------------------------------------------------------------- */

/**
 * Valida e salva uma imagem enviada por <input type="file">.
 *
 *   $arquivo → o item de $_FILES (ex.: $_FILES['foto'])
 *   $pasta   → subpasta dentro de "uploads" (ex.: 'usuarios')
 *   $erro    → recebe a mensagem de erro, se houver
 *
 * Retorna o caminho para gravar no banco (ex.: uploads/usuarios/a1b2c3.jpg)
 * ou null se nenhum arquivo foi enviado ou se houve erro.
 */
function salvarImagem(array $arquivo, string $pasta, ?string &$erro = null, int $tamanhoMaximo = 2097152): ?string
{
    $erro = null;

    if ($arquivo['error'] === UPLOAD_ERR_NO_FILE) {
        return null; // campo deixado em branco: não é erro
    }
    if ($arquivo['error'] === UPLOAD_ERR_INI_SIZE || $arquivo['error'] === UPLOAD_ERR_FORM_SIZE || $arquivo['size'] > $tamanhoMaximo) {
        $erro = 'A imagem deve ter no máximo ' . round($tamanhoMaximo / 1048576) . ' MB.';
        return null;
    }
    if ($arquivo['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($arquivo['tmp_name'])) {
        $erro = 'Não foi possível enviar a imagem. Tente novamente.';
        return null;
    }

    // Descobre o tipo REAL do arquivo pelo conteúdo. Nunca confie na extensão
    // nem em $arquivo['type']: os dois são informados pelo navegador.
    $extensoes = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif',
    ];
    $tipo = (new finfo(FILEINFO_MIME_TYPE))->file($arquivo['tmp_name']);

    if (!isset($extensoes[$tipo])) {
        $erro = 'Escolha uma imagem JPG, PNG, WEBP ou GIF.';
        return null;
    }

    $destino = RAIZ . '/uploads/' . $pasta;
    if (!is_dir($destino)) {
        mkdir($destino, 0775, true);
    }

    // Nome aleatório: evita arquivos com o mesmo nome e nomes maliciosos
    $nome = bin2hex(random_bytes(16)) . '.' . $extensoes[$tipo];

    if (!move_uploaded_file($arquivo['tmp_name'], $destino . '/' . $nome)) {
        $erro = 'Não foi possível salvar a imagem no servidor (verifique a permissão da pasta uploads).';
        return null;
    }

    return 'uploads/' . $pasta . '/' . $nome;
}

/** Apaga do disco um arquivo salvo por salvarImagem(). */
function apagarImagem(?string $caminho): void
{
    // Só apaga o que estiver dentro de "uploads/"
    if (empty($caminho) || strpos($caminho, 'uploads/') !== 0 || strpos($caminho, '..') !== false) {
        return;
    }
    $arquivo = RAIZ . '/' . $caminho;
    if (is_file($arquivo)) {
        unlink($arquivo);
    }
}


/* --------------------------------------------------------------------------
   Limpeza do HTML vindo do editor rich text (Summernote)
   --------------------------------------------------------------------------
   O conteúdo do editor é HTML e será exibido SEM htmlspecialchars() (senão a
   formatação apareceria como texto). Por isso ele precisa ser "limpo" antes
   de ir para o banco: mantemos só as tags e os atributos de formatação e
   removemos o que pode executar código (<script>, onclick, javascript:...).
   -------------------------------------------------------------------------- */
function limparHtml(string $html): string
{
    $html = trim($html);
    if ($html === '') { return ''; }

    // Tags permitidas => atributos permitidos em cada uma
    $permitidas = [
        'p' => [], 'br' => [], 'hr' => [], 'div' => [], 'span' => [],
        'b' => [], 'strong' => [], 'i' => [], 'em' => [], 'u' => [], 's' => [], 'strike' => [],
        'sub' => [], 'sup' => [], 'font' => ['color'],
        'h1' => [], 'h2' => [], 'h3' => [], 'h4' => [], 'h5' => [], 'h6' => [],
        'blockquote' => [], 'pre' => [], 'code' => [],
        'ul' => [], 'ol' => [], 'li' => [],
        'a'      => ['href', 'target', 'rel'],
        'img'    => ['src', 'alt', 'width', 'height'],
        'iframe' => ['src', 'width', 'height', 'frameborder', 'allowfullscreen'],
        'table' => [], 'thead' => [], 'tbody' => [], 'tr' => [],
        'th' => ['colspan', 'rowspan'], 'td' => ['colspan', 'rowspan'],
    ];
    $atributosGerais = ['style', 'class'];

    // Estas tags são removidas junto com TODO o seu conteúdo
    $perigosas = ['script', 'style', 'object', 'embed', 'form', 'input', 'button',
                  'select', 'textarea', 'link', 'meta', 'base', 'svg', 'math'];

    // Endereços aceitos em cada atributo
    $enderecos = [
        'href'       => '~^(https?://|mailto:|tel:|/|#)~i',
        'img.src'    => '~^(https?://|/|\.\./|uploads/|data:image/(png|jpe?g|gif|webp);base64,)~i',
        'iframe.src' => '~^(https?:)?//(www\.)?(youtube\.com/embed/|youtube-nocookie\.com/embed/|player\.vimeo\.com/video/)~i',
    ];

    $documento = new DOMDocument();
    libxml_use_internal_errors(true); // o HTML do editor nem sempre é "perfeito"
    $documento->loadHTML(
        '<!DOCTYPE html><html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"></head>'
        . '<body><div>' . $html . '</div></body></html>'
    );
    libxml_clear_errors();

    $raiz = $documento->getElementsByTagName('div')->item(0);

    // Percorre todos os elementos (de trás para frente, pois a lista muda ao remover)
    $elementos = iterator_to_array($raiz->getElementsByTagName('*'));
    foreach (array_reverse($elementos) as $elemento) {
        $tag = strtolower($elemento->nodeName);

        if (in_array($tag, $perigosas, true)) {
            $elemento->parentNode->removeChild($elemento);
            continue;
        }

        // Tag desconhecida: remove a tag, mas mantém o texto que estava dentro
        if (!isset($permitidas[$tag])) {
            while ($elemento->firstChild) {
                $elemento->parentNode->insertBefore($elemento->firstChild, $elemento);
            }
            $elemento->parentNode->removeChild($elemento);
            continue;
        }

        foreach (iterator_to_array($elemento->attributes) as $atributo) {
            $nome  = strtolower($atributo->nodeName);
            $valor = trim($atributo->nodeValue);

            $permitido = in_array($nome, $permitidas[$tag], true) || in_array($nome, $atributosGerais, true);

            if ($permitido && $nome === 'style' && preg_match('~url\s*\(|expression|javascript:|@import|behavior~i', $valor)) {
                $permitido = false;
            }
            if ($permitido && ($nome === 'href' || $nome === 'src')) {
                $regra = $enderecos[$tag . '.' . $nome] ?? $enderecos[$nome] ?? null;
                $permitido = $regra !== null && preg_match($regra, $valor) === 1;
            }

            if (!$permitido) {
                $elemento->removeAttribute($atributo->nodeName);
            }
        }

        // Vídeo ou imagem que ficou sem endereço válido não serve para nada
        if (($tag === 'iframe' || $tag === 'img') && !$elemento->hasAttribute('src')) {
            $elemento->parentNode->removeChild($elemento);
            continue;
        }

        // Link que abre em nova aba: impede que a outra página controle a nossa
        if ($tag === 'a' && $elemento->getAttribute('target') === '_blank') {
            $elemento->setAttribute('rel', 'noopener noreferrer');
        }
    }

    $limpo = '';
    foreach ($raiz->childNodes as $filho) {
        $limpo .= $documento->saveHTML($filho);
    }
    return trim($limpo);
}

/** Diz se o HTML do editor está "vazio" (o Summernote envia <p><br></p> quando não há texto). */
function htmlVazio(string $html): bool
{
    $texto = trim(str_replace("\u{00A0}", ' ', html_entity_decode(strip_tags($html), ENT_QUOTES, 'UTF-8')));
    return $texto === '' && stripos($html, '<img') === false && stripos($html, '<iframe') === false;
}
