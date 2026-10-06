/* ==========================================================================
   CMS — Login (index.php)
   Requer: jQuery, main.js
   --------------------------------------------------------------------------
   IMPORTANTE:
   Esta validação no navegador serve apenas para melhorar a experiência.
   A autenticação de verdade acontece no servidor (login.php), com
   password_verify() e $_SESSION. Nunca confie só no JavaScript!
   ========================================================================== */

(function ($) {
  'use strict';

  var regexEmail = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;

  function marcarErro($campo, mensagem) {
    $campo.addClass('is-invalid');
    $campo.closest('.form-group').addClass('has-error').find('.invalid-feedback').text(mensagem);
  }

  function limparErro($campo) {
    $campo.removeClass('is-invalid');
    $campo.closest('.form-group').removeClass('has-error');
  }

  $(function () {
    var $form  = $('#formLogin');
    var $email = $('#email');
    var $senha = $('#senha');
    var $btn   = $('#btnEntrar');
    var $erro  = $('.auth__box .alert'); // mensagem vinda do PHP (ex.: senha incorreta)

    // Remove o erro assim que o usuário corrige o campo
    $form.on('input', '.form-control', function () {
      limparErro($(this));
      $erro.addClass('hidden');
    });

    $form.on('submit', function (e) {
      var valido = true;
      var email = $.trim($email.val());
      var senha = $senha.val();

      if (email === '') {
        marcarErro($email, 'Informe seu e-mail.'); valido = false;
      } else if (!regexEmail.test(email)) {
        marcarErro($email, 'Digite um e-mail válido.'); valido = false;
      }

      if (senha === '') {
        marcarErro($senha, 'Informe sua senha.'); valido = false;
      }

      if (!valido) {
        e.preventDefault();
        $form.find('.is-invalid').first().trigger('focus');
        return;
      }

      // Tudo certo: o formulário segue para o login.php
      $btn.addClass('is-loading').html('<span class="spinner"></span> Entrando...');
    });
  });

})(jQuery);
