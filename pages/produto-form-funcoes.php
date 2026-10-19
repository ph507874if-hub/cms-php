<?php
/* Funções locais do módulo de produtos.
   Imagens aceitas: JPEG, PNG e WebP; máximo de 3 MB. */
function salvarImagemProduto(array $arquivo, ?string &$erro): ?string {
    $erro = null;
    if (($arquivo['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return null;
    if (($arquivo['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        $erro = 'Não foi possível enviar a imagem.';
        return null;
    }
    if (($arquivo['size'] ?? 0) > 3 * 1024 * 1024) {
        $erro = 'A imagem deve ter no máximo 3 MB.';
        return null;
    }
    $info = @getimagesize($arquivo['tmp_name']);
    if (!$info || !isset($info['mime'])) {
        $erro = 'O arquivo enviado não é uma imagem válida.';
        return null;
    }
    $extensoes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    if (!isset($extensoes[$info['mime']])) {
        $erro = 'Use uma imagem JPG, PNG ou WebP.';
        return null;
    }
    $pasta = __DIR__ . '/../uploads/produtos';
    if (!is_dir($pasta) && !mkdir($pasta, 0755, true) && !is_dir($pasta)) {
        $erro = 'Não foi possível criar a pasta de imagens.';
        return null;
    }
    $nome = bin2hex(random_bytes(16)) . '.' . $extensoes[$info['mime']];
    if (!move_uploaded_file($arquivo['tmp_name'], $pasta . '/' . $nome)) {
        $erro = 'Não foi possível salvar a imagem.';
        return null;
    }
    return $nome;
}

function apagarImagemProduto(?string $nome): void {
    if (!$nome) return;
    $nomeSeguro = basename($nome);
    $caminho = __DIR__ . '/../uploads/produtos/' . $nomeSeguro;
    if (is_file($caminho)) @unlink($caminho);
}
