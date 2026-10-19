/* CMS — formulário de cadastro/edição de usuário.
   Requer jQuery; Summernote é opcional e só inicializa se estiver disponível. */
(function ($) {
  'use strict';

  var regexEmail = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;
  var regexUsuario = /^[a-z0-9._]{3,30}$/;
  var TAMANHO_MAX_FOTO = 2 * 1024 * 1024;

  function marcarErro($campo, mensagem) {
    $campo.addClass('is-invalid');
    $campo.closest('.form-group').addClass('has-error')
      .find('.invalid-feedback').text(mensagem);
  }
  function limparErro($campo) {
    $campo.removeClass('is-invalid');
    $campo.closest('.form-group').removeClass('has-error');
  }
  function sugerirUsuario(nome) {
    var ignorar = ['da', 'de', 'do', 'das', 'dos', 'e'];
    var partes = String(nome || '').toLowerCase()
      .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
      .replace(/[^a-z\s]/g, '').trim().split(/\s+/)
      .filter(function (p) { return p && ignorar.indexOf(p) === -1; });
    if (!partes.length) return '';
    return partes.length === 1 ? partes[0] : partes[0] + '.' + partes[partes.length - 1];
  }
  function forcaSenha(senha) {
    if (!senha) return 0;
    var pontos = 0;
    if (senha.length >= 8) pontos++;
    if (/[a-z]/.test(senha) && /[A-Z]/.test(senha)) pontos++;
    if (/\d/.test(senha)) pontos++;
    if (/[^A-Za-z0-9]/.test(senha)) pontos++;
    return Math.max(1, pontos);
  }

  $(function () {
    var $form = $('#formUsuario');
    if (!$form.length) return;

    var modo = $form.data('modo');
    var $nome = $('#nome'), $email = $('#email'), $usuario = $('#usuario');
    var $senha = $('#senha'), $confirma = $('#confirmarSenha'), $bio = $('#biografia');
    var usuarioEditadoManualmente = modo === 'editar' || !!$usuario.val();

    if ($.fn.summernote && $bio.length) {
      $bio.summernote({
        lang: 'pt-BR',
        height: 260,
        placeholder: 'Escreva aqui...',
        styleTags: ['p', 'h2', 'h3', 'h4', 'blockquote', 'pre'],
        toolbar: [
          ['style', ['style']],
          ['font', ['bold', 'italic', 'underline', 'strikethrough', 'clear']],
          ['color', ['forecolor']],
          ['para', ['ul', 'ol', 'paragraph']],
          ['insert', ['link', 'picture', 'table', 'hr']],
          ['view', ['undo', 'redo', 'codeview', 'fullscreen']]
        ],
        callbacks: {
          onChange: function (conteudo) {
            $bio.val(conteudo);
            if (!$bio.summernote('isEmpty')) limparErro($bio);
          }
        }
      });
    }

    $usuario.on('input', function () { usuarioEditadoManualmente = true; });
    $nome.on('input', function () {
      if (!usuarioEditadoManualmente) {
        $usuario.val(sugerirUsuario($(this).val()));
        limparErro($usuario);
      }
    });

    var textos = ['', 'Fraca', 'Razoável', 'Boa', 'Forte'];
    $senha.on('input', function () {
      var nivel = forcaSenha($(this).val());
      $('#medidorSenha').attr('data-level', nivel);
      $('#textoForca').text(nivel ? 'Força da senha: ' + textos[nivel] :
        'Use 8+ caracteres com letras maiúsculas, números e símbolos.');
    });

    var fotoPadrao = $('#previewFoto').data('padrao');
    $('#btnEscolherFoto').on('click', function () { $('#foto').trigger('click'); });
    $('#foto').on('change', function () {
      var arquivo = this.files && this.files[0];
      if (!arquivo) return;
      if (!/^image\/(jpeg|png|webp|gif)$/.test(arquivo.type)) {
        CMS.toast('Escolha uma imagem JPG, PNG, WEBP ou GIF.', 'danger');
        this.value = ''; return;
      }
      if (arquivo.size > TAMANHO_MAX_FOTO) {
        CMS.toast('A imagem deve ter no máximo 2 MB.', 'danger');
        this.value = ''; return;
      }
      var leitor = new FileReader();
      leitor.onload = function (e) { $('#previewFoto').attr('src', e.target.result); };
      leitor.readAsDataURL(arquivo);
      $('#removerFoto').val('0');
      $('#btnRemoverFoto').removeClass('hidden');
    });
    $('#btnRemoverFoto').on('click', function () {
      $('#foto').val('');
      $('#previewFoto').attr('src', fotoPadrao || '');
      $('#removerFoto').val('1');
      $(this).addClass('hidden');
    });

    $('#status').on('change', function () {
      $('#statusTexto').text(this.checked ?
        'Ativo — pode acessar o painel' : 'Inativo — acesso bloqueado');
    }).trigger('change');

    $form.on('input change', '.form-control, .form-select, input[type=radio]', function () {
      limparErro($(this));
    });

    $form.on('submit', function (e) {
      var valido = true;
      var nome = $.trim($nome.val() || '');
      var email = $.trim($email.val() || '');
      var usuario = $.trim($usuario.val() || '');
      var senha = $senha.val() || '';

      if (nome.length < 3) { marcarErro($nome, 'Informe o nome completo (mínimo 3 caracteres).'); valido = false; }
      if (!email) { marcarErro($email, 'Informe o e-mail.'); valido = false; }
      else if (!regexEmail.test(email)) { marcarErro($email, 'Digite um e-mail válido.'); valido = false; }
      if (!regexUsuario.test(usuario)) { marcarErro($usuario, 'Use de 3 a 30 caracteres: letras minúsculas, números, ponto ou _.'); valido = false; }

      var validarSenha = modo === 'cadastrar' || senha !== '';
      if (validarSenha) {
        if (senha.length < 8) { marcarErro($senha, 'A senha deve ter pelo menos 8 caracteres.'); valido = false; }
        if (($confirma.val() || '') !== senha) { marcarErro($confirma, 'As senhas não conferem.'); valido = false; }
      }

      var $perfil = $('input[name="perfil"]');
      if ($perfil.length && !$perfil.filter(':checked').length) {
        $perfil.closest('.form-group').addClass('has-error'); valido = false;
      }

      if ($bio.prop('required') && $.fn.summernote && $bio.summernote('isEmpty')) {
        marcarErro($bio, 'Escreva uma breve apresentação.'); valido = false;
      }

      if (!valido) {
        e.preventDefault();
        var $primeiro = $form.find('.has-error, .is-invalid').first();
        if ($primeiro.length) {
          $('html, body').animate({ scrollTop: $primeiro.offset().top - 110 }, 250);
          $primeiro.find('.form-control').first().trigger('focus');
        }
        if (window.CMS) CMS.toast('Verifique os campos destacados.', 'danger');
        return;
      }
      $('#btnSalvar').addClass('is-loading').prop('disabled', true)
        .html('<span class="spinner"></span> Salvando...');
    });
  });
})(jQuery);
