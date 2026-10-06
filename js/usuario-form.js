/* ==========================================================================
   CMS — Formulário de usuário (cadastrar e editar)
   Páginas: pages/usuarios-cadastrar.php e pages/usuarios-editar.php
   Requer: jQuery, Summernote Lite (+ idioma pt-BR), main.js
   --------------------------------------------------------------------------
   O <form> informa o modo em data-modo="cadastrar" ou data-modo="editar".
   No modo "editar" a senha é opcional (em branco = manter a senha atual).

   LEMBRETE: estas validações só melhoram a experiência de quem usa o painel.
   TODAS são repetidas no servidor, em validarUsuario()
   (includes/usuarios-funcoes.php), que também confere e-mail/usuário
   duplicado no banco. A senha é gravada com password_hash().
   ========================================================================== */

(function ($) {
  'use strict';

  var regexEmail   = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;
  var regexUsuario = /^[a-z0-9._]{3,30}$/;
  var TAMANHO_MAX_FOTO = 2 * 1024 * 1024; // 2 MB

  /* Ajudantes de validação ------------------------------------------ */
  function marcarErro($campo, mensagem) {
    $campo.addClass('is-invalid');
    $campo.closest('.form-group').addClass('has-error').find('.invalid-feedback').text(mensagem);
  }

  function limparErro($campo) {
    $campo.removeClass('is-invalid');
    $campo.closest('.form-group').removeClass('has-error');
  }

  /* Gera um nome de usuário a partir do nome completo
     Ex.: "João da Silva" -> "joao.silva" */
  function sugerirUsuario(nome) {
    var ignorar = ['da', 'de', 'do', 'das', 'dos', 'e'];
    var partes = nome.toLowerCase()
      .normalize('NFD').replace(/[̀-ͯ]/g, '') // remove acentos
      .replace(/[^a-z\s]/g, '')
      .split(/\s+/)
      .filter(function (p) { return p && ignorar.indexOf(p) === -1; });

    if (!partes.length) { return ''; }
    return partes.length === 1 ? partes[0] : partes[0] + '.' + partes[partes.length - 1];
  }

  /* Força da senha: retorna de 0 a 4 */
  function forcaSenha(senha) {
    if (!senha) { return 0; }
    var pontos = 0;
    if (senha.length >= 8) { pontos++; }
    if (/[a-z]/.test(senha) && /[A-Z]/.test(senha)) { pontos++; }
    if (/\d/.test(senha)) { pontos++; }
    if (/[^A-Za-z0-9]/.test(senha)) { pontos++; }
    return Math.max(1, pontos);
  }

  $(function () {
    var $form     = $('#formUsuario');
    var modo      = $form.data('modo'); // "cadastrar" ou "editar"
    var $nome     = $('#nome');
    var $email    = $('#email');
    var $usuario  = $('#usuario');
    var $senha    = $('#senha');
    var $confirma = $('#confirmarSenha');
    var $bio      = $('#biografia');
    // Não sugere o usuário na edição nem quando o campo já voltou preenchido do PHP
    var usuarioEditadoManualmente = modo === 'editar' || $usuario.val() !== '';

    /* ---------------------------------------------------------------
       Editor rich text (Summernote Lite)
       Documentação: https://summernote.org/deep-dive/
       O conteúdo HTML é enviado no campo <textarea name="biografia">.
       --------------------------------------------------------------- */
    $bio.summernote({
      lang: 'pt-BR',
      height: 260,
      placeholder: 'Escreva aqui... Use a barra de ferramentas para formatar títulos, listas, links e imagens.',
      styleTags: ['p', 'h2', 'h3', 'h4', 'blockquote', 'pre'],
      toolbar: [
        ['style',  ['style']],
        ['font',   ['bold', 'italic', 'underline', 'strikethrough', 'clear']],
        ['color',  ['forecolor']],
        ['para',   ['ul', 'ol', 'paragraph']],
        ['insert', ['link', 'picture', 'video', 'table', 'hr']],
        ['view',   ['undo', 'redo', 'codeview', 'fullscreen']]
      ],
      callbacks: {
        onChange: function (conteudo) {
          // Mantém o <textarea> sempre sincronizado com o editor
          $bio.val(conteudo);
          if (!$bio.summernote('isEmpty')) { limparErro($bio); }
        }
        /* Dica: por padrão as imagens inseridas no editor ficam em Base64
           dentro do HTML (por isso a coluna é MEDIUMTEXT). Para salvar como
           arquivo no servidor, use o callback onImageUpload e envie a imagem
           via $.ajax para um script PHP de upload. */
      }
    });

    /* Sugestão automática do nome de usuário ------------------------ */
    $usuario.on('input', function () { usuarioEditadoManualmente = true; });
    $nome.on('input', function () {
      if (!usuarioEditadoManualmente) {
        $usuario.val(sugerirUsuario($(this).val()));
        limparErro($usuario);
      }
    });

    /* Medidor de força da senha ------------------------------------- */
    var textos = ['', 'Fraca', 'Razoável', 'Boa', 'Forte'];
    $senha.on('input', function () {
      var nivel = forcaSenha($(this).val());
      $('#medidorSenha').attr('data-level', nivel);
      $('#textoForca').text(nivel ? 'Força da senha: ' + textos[nivel] : 'Use 8+ caracteres com letras maiúsculas, números e símbolos.');
    });

    /* Foto de perfil: pré-visualização ------------------------------ */
    var fotoPadrao = $('#previewFoto').data('padrao');

    $('#btnEscolherFoto').on('click', function () { $('#foto').trigger('click'); });

    $('#foto').on('change', function () {
      var arquivo = this.files[0];
      if (!arquivo) { return; }

      if (!/^image\/(jpeg|png|webp|gif)$/.test(arquivo.type)) {
        CMS.toast('Escolha uma imagem JPG, PNG, WEBP ou GIF.', 'danger');
        this.value = '';
        return;
      }
      if (arquivo.size > TAMANHO_MAX_FOTO) {
        CMS.toast('A imagem deve ter no máximo 2 MB.', 'danger');
        this.value = '';
        return;
      }

      var leitor = new FileReader();
      leitor.onload = function (e) { $('#previewFoto').attr('src', e.target.result); };
      leitor.readAsDataURL(arquivo);
      $('#removerFoto').val('0');
      $('#btnRemoverFoto').removeClass('hidden');
    });

    $('#btnRemoverFoto').on('click', function () {
      $('#foto').val('');
      $('#previewFoto').attr('src', fotoPadrao);
      $('#removerFoto').val('1'); // avisa o PHP que a foto atual deve ser apagada
      $(this).addClass('hidden');
    });

    /* Status: atualiza o texto do switch ---------------------------- */
    $('#status').on('change', function () {
      $('#statusTexto').text(this.checked ? 'Ativo — pode acessar o painel' : 'Inativo — acesso bloqueado');
    }).trigger('change');

    /* Limpa o erro quando o usuário corrige o campo ----------------- */
    $form.on('input change', '.form-control, .form-select, input[type=radio]', function () {
      limparErro($(this));
    });

    /* ---------------------------------------------------------------
       Validação ao enviar
       --------------------------------------------------------------- */
    $form.on('submit', function (e) {
      var valido = true;
      var nome    = $.trim($nome.val());
      var email   = $.trim($email.val());
      var usuario = $.trim($usuario.val());
      var senha   = $senha.val();

      if (nome.length < 3) {
        marcarErro($nome, 'Informe o nome completo (mínimo 3 caracteres).'); valido = false;
      }

      if (email === '') {
        marcarErro($email, 'Informe o e-mail.'); valido = false;
      } else if (!regexEmail.test(email)) {
        marcarErro($email, 'Digite um e-mail válido.'); valido = false;
      }

      if (!regexUsuario.test(usuario)) {
        marcarErro($usuario, 'Use de 3 a 30 caracteres: letras minúsculas, números, ponto ou _.'); valido = false;
      }

      // Senha: obrigatória no cadastro; opcional na edição
      var validarSenha = modo === 'cadastrar' || senha !== '';
      if (validarSenha) {
        if (senha.length < 8) {
          marcarErro($senha, 'A senha deve ter pelo menos 8 caracteres.'); valido = false;
        }
        if ($confirma.val() !== senha) {
          marcarErro($confirma, 'As senhas não conferem.'); valido = false;
        }
      }

      // O perfil só aparece no formulário para administradores
      var $perfil = $('input[name="perfil"]');
      if ($perfil.length && !$perfil.filter(':checked').length) {
        $perfil.closest('.form-group').addClass('has-error'); valido = false;
      }

      // Editor rich text obrigatório (remova se o campo for opcional)
      if ($bio.prop('required') && $bio.summernote('isEmpty')) {
        marcarErro($bio, 'Escreva uma breve apresentação.'); valido = false;
      }

      if (!valido) {
        e.preventDefault();
        var $primeiro = $form.find('.has-error').first();
        $('html, body').animate({ scrollTop: $primeiro.offset().top - 110 }, 250);
        $primeiro.find('.form-control').first().trigger('focus');
        CMS.toast('Verifique os campos destacados.', 'danger');
        return;
      }

      // Tudo certo: o formulário segue para o PHP
      $('#btnSalvar').addClass('is-loading').html('<span class="spinner"></span> Salvando...');
    });
  });

})(jQuery);
