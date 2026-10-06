<?php
/* ==========================================================================
   Tela de login
   O formulário é enviado para login.php, que confere o e-mail e a senha.
   ========================================================================== */

require_once __DIR__ . '/includes/sessao.php';

// Quem já está logado (ou marcou "Manter conectado") vai direto para o painel
if (isset($_SESSION['usuario_id']) || entrarPeloCookie($pdo)) {
    redirecionar('pages/dashboard.php');
}

// E-mail digitado na tentativa anterior (para não precisar digitar de novo)
$email = $_SESSION['login_email'] ?? '';
unset($_SESSION['login_email']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Entrar | Painel CMS</title>
  <meta name="description" content="Acesso ao painel administrativo do CMS">
  <link rel="icon" type="image/svg+xml" href="img/favicon.svg">

  <!-- Ícones (Bootstrap Icons) -->
  <link rel="stylesheet" href="css/vendor/bootstrap-icons/bootstrap-icons.min.css">
  <!-- Estilos do CMS -->
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/login.css">
</head>
<body>

  <main class="auth">

    <!-- ===================== LADO DO FORMULÁRIO ===================== -->
    <section class="auth__form-side">
      <a href="index.php" class="auth__logo">
        <img src="img/logo.svg" alt="">
        Painel CMS
      </a>

      <div class="auth__box">
        <h1>Bem-vindo de volta</h1>
        <p class="auth__lead">Entre com suas credenciais para acessar o painel administrativo.</p>

        <!-- Mensagens vindas do login.php, logout.php e auth.php -->
        <?php exibirMensagem(); ?>

        <form class="auth__form" id="formLogin" action="login.php" method="post" novalidate>
          <?= csrfCampo() ?>

          <div class="form-group">
            <label class="form-label" for="email">E-mail</label>
            <div class="input-icon">
              <i class="bi bi-envelope"></i>
              <input type="email" class="form-control" id="email" name="email" placeholder="voce@exemplo.com" autocomplete="username" value="<?= e($email) ?>" <?= $email === '' ? 'autofocus' : '' ?>>
            </div>
            <div class="invalid-feedback"></div>
          </div>

          <div class="form-group">
            <label class="form-label" for="senha">Senha</label>
            <div class="input-icon has-toggle">
              <i class="bi bi-lock"></i>
              <input type="password" class="form-control" id="senha" name="senha" placeholder="Digite sua senha" autocomplete="current-password" <?= $email !== '' ? 'autofocus' : '' ?>>
              <button type="button" class="toggle-password" aria-label="Mostrar senha"><i class="bi bi-eye"></i></button>
            </div>
            <div class="invalid-feedback"></div>
          </div>

          <div class="auth__row">
            <label class="check">
              <input type="checkbox" name="lembrar" value="1"> Manter conectado
            </label>
            <!-- Recuperação de senha: não implementada (exige envio de e-mail) -->
            <a href="#">Esqueceu a senha?</a>
          </div>

          <button type="submit" class="btn btn-primary btn-lg btn-block" id="btnEntrar">
            Entrar <i class="bi bi-arrow-right"></i>
          </button>
        </form>
      </div>

      <footer class="auth__footer">
        <span>&copy; <?= date('Y') ?> Painel CMS</span>
        <span>Projeto Prático — PHP &amp; MySQL</span>
      </footer>
    </section>

    <!-- ======================== LADO VISUAL ========================= -->
    <aside class="auth__visual" aria-hidden="true">
      <span class="auth__tag"><span></span> Sistema de Gerenciamento de Conteúdo</span>

      <!-- Ilustração do painel feita só com HTML e CSS -->
      <div class="mock">
        <div class="mock__bar"><i></i><i></i><i></i></div>
        <div class="mock__body">
          <div class="mock__side"><span></span><span></span><span></span><span></span><span></span></div>
          <div class="mock__main">
            <div class="mock__row">
              <div class="mock__stat"><b></b><em></em></div>
              <div class="mock__stat"><b></b><em></em></div>
              <div class="mock__stat"><b></b><em></em></div>
            </div>
            <div class="mock__line"><i></i><b></b><s></s></div>
            <div class="mock__line"><i></i><b></b><s></s></div>
            <div class="mock__line"><i></i><b></b><s></s></div>
          </div>
        </div>
      </div>

      <div class="auth__quote">
        <h2>Todo o conteúdo do seu site, em um só lugar.</h2>
        <p>Cadastre, edite e publique informações com poucos cliques. O que você altera aqui aparece no website.</p>
        <ul class="auth__features">
          <li><i class="bi bi-shield-check"></i> Acesso seguro</li>
          <li><i class="bi bi-people"></i> Múltiplos usuários</li>
          <li><i class="bi bi-phone"></i> Responsivo</li>
        </ul>
      </div>
    </aside>

  </main>

  <!-- Scripts -->
  <script src="js/vendor/jquery.min.js"></script>
  <script src="js/main.js"></script>
  <script src="js/login.js"></script>
</body>
</html>
