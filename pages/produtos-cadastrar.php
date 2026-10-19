<?php
require_once '../includes/auth.php';
require_once '../config/conexao.php';
require_once 'produto-form-funcoes.php';
exigirAdmin();

$categorias = $pdo->query('SELECT id, nome FROM categorias ORDER BY nome')->fetchAll();
$dados = ['nome'=>'', 'descricao'=>'', 'preco'=>'', 'estoque'=>'0', 'categoria_id'=>'', 'status'=>'1'];
$erros = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfValidar();
    $dados['nome'] = trim(post('nome'));
    $dados['descricao'] = trim(post('descricao'));
    $dados['preco'] = str_replace(',', '.', trim(post('preco')));
    $dados['estoque'] = trim(post('estoque'));
    $dados['categoria_id'] = trim(post('categoria_id'));
    $dados['status'] = post('status') === '0' ? '0' : '1';

    if ($dados['nome'] === '') $erros['nome'] = 'Informe o nome do produto.';
    elseif (mb_strlen($dados['nome']) > 150) $erros['nome'] = 'Use no máximo 150 caracteres no nome.';
    if ($dados['preco'] === '' || !is_numeric($dados['preco']) || (float)$dados['preco'] < 0) $erros['preco'] = 'Informe um preço válido.';
    if (filter_var($dados['estoque'], FILTER_VALIDATE_INT) === false || (int)$dados['estoque'] < 0) $erros['estoque'] = 'Informe uma quantidade inteira igual ou maior que zero.';
    if ($dados['categoria_id'] !== '' && !ctype_digit($dados['categoria_id'])) $erros['categoria_id'] = 'Categoria inválida.';

    if (!$erros && $dados['categoria_id'] !== '') {
        $check = $pdo->prepare('SELECT COUNT(*) FROM categorias WHERE id = ?');
        $check->execute([(int)$dados['categoria_id']]);
        if (!(int)$check->fetchColumn()) $erros['categoria_id'] = 'A categoria selecionada não existe.';
    }

    $erroImagem = null;
    $imagem = null;
    if (!$erros && isset($_FILES['imagem'])) {
        $imagem = salvarImagemProduto($_FILES['imagem'], $erroImagem);
        if ($erroImagem) $erros['imagem'] = $erroImagem;
    }

    if (!$erros) {
        $sql = $pdo->prepare('INSERT INTO produtos (nome, descricao, preco, estoque, categoria_id, imagem, status) VALUES (:nome, :descricao, :preco, :estoque, :categoria_id, :imagem, :status)');
        $sql->execute([
            ':nome'=>$dados['nome'],
            ':descricao'=>$dados['descricao'] !== '' ? $dados['descricao'] : null,
            ':preco'=>(float)$dados['preco'],
            ':estoque'=>(int)$dados['estoque'],
            ':categoria_id'=>$dados['categoria_id'] !== '' ? (int)$dados['categoria_id'] : null,
            ':imagem'=>$imagem,
            ':status'=>(int)$dados['status']
        ]);
        definirMensagem('Produto cadastrado com sucesso!');
        redirecionar('produtos.php');
    }
}

$titulo = 'Novo produto'; $menuAtivo = 'produtos';
require_once '../includes/header.php';
?>
<main class="content">
  <div class="page-header"><div><ol class="breadcrumb"><li><a href="dashboard.php">Dashboard</a></li><li><a href="produtos.php">Produtos</a></li><li>Cadastrar</li></ol><h1>Novo produto</h1><p>Preencha os dados do produto.</p></div><div class="page-header__actions"><a href="produtos.php" class="btn btn-light"><i class="bi bi-arrow-left"></i> Voltar</a></div></div>
  <?php exibirMensagem(); ?>
  <section class="card"><form action="produtos-cadastrar.php" method="post" enctype="multipart/form-data">
    <?= csrfCampo() ?>
    <div class="card__body">
      <div class="form-group"><label for="nome">Nome *</label><input class="form-control" id="nome" name="nome" maxlength="150" required value="<?= e($dados['nome']) ?>"><?php if(isset($erros['nome'])): ?><p class="text-danger"><?= e($erros['nome']) ?></p><?php endif; ?></div>
      <div class="form-group"><label for="descricao">Descrição</label><textarea class="form-control" id="descricao" name="descricao" rows="4"><?= e($dados['descricao']) ?></textarea></div>
      <div class="form-group"><label for="preco">Preço (R$) *</label><input class="form-control" type="number" id="preco" name="preco" min="0" step="0.01" required value="<?= e($dados['preco']) ?>"><?php if(isset($erros['preco'])): ?><p class="text-danger"><?= e($erros['preco']) ?></p><?php endif; ?></div>
      <div class="form-group"><label for="estoque">Estoque *</label><input class="form-control" type="number" id="estoque" name="estoque" min="0" step="1" required value="<?= e($dados['estoque']) ?>"><?php if(isset($erros['estoque'])): ?><p class="text-danger"><?= e($erros['estoque']) ?></p><?php endif; ?></div>
      <div class="form-group"><label for="categoria_id">Categoria</label><select class="form-select" id="categoria_id" name="categoria_id"><option value="">Sem categoria</option><?php foreach($categorias as $cat): ?><option value="<?= (int)$cat['id'] ?>" <?= (string)$dados['categoria_id'] === (string)$cat['id'] ? 'selected' : '' ?>><?= e($cat['nome']) ?></option><?php endforeach; ?></select><?php if(isset($erros['categoria_id'])): ?><p class="text-danger"><?= e($erros['categoria_id']) ?></p><?php endif; ?></div>
      <div class="form-group"><label for="imagem">Imagem (JPG, PNG ou WebP; até 3 MB)</label><input class="form-control" type="file" id="imagem" name="imagem" accept="image/jpeg,image/png,image/webp"><?php if(isset($erros['imagem'])): ?><p class="text-danger"><?= e($erros['imagem']) ?></p><?php endif; ?></div>
      <div class="form-group"><label for="status">Status</label><select class="form-select" id="status" name="status"><option value="1" <?= $dados['status']==='1'?'selected':'' ?>>Ativo</option><option value="0" <?= $dados['status']==='0'?'selected':'' ?>>Inativo</option></select></div>
    </div>
    <div class="card__footer"><a href="produtos.php" class="btn btn-light">Cancelar</a><button class="btn btn-primary" type="submit"><i class="bi bi-check-lg"></i> Salvar produto</button></div>
  </form></section>
</main>
<?php require_once '../includes/footer.php'; ?>
