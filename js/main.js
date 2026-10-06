/* ==========================================================================
   CMS — Scripts gerais (usado em todas as páginas)
   Requer: jQuery
   ========================================================================== */

// Namespace global para funções reutilizáveis do CMS
window.CMS = window.CMS || {};

(function ($) {
  'use strict';

  /* ------------------------------------------------------------------
     Toast (mensagem flutuante)
     Uso: CMS.toast('Usuário salvo com sucesso!', 'success');
     Tipos: success | danger | info
     ------------------------------------------------------------------ */
  CMS.toast = function (mensagem, tipo) {
    tipo = tipo || 'success';
    var icones = { success: 'bi-check-circle-fill', danger: 'bi-x-circle-fill', info: 'bi-info-circle-fill' };

    var $container = $('.toast-container');
    if (!$container.length) {
      $container = $('<div class="toast-container" role="status" aria-live="polite"></div>').appendTo('body');
    }

    var $toast = $('<div class="toast toast--' + tipo + '"><i class="bi ' + icones[tipo] + '"></i><span></span></div>');
    $toast.find('span').text(mensagem); // .text() evita injeção de HTML
    $container.append($toast);

    setTimeout(function () {
      $toast.addClass('is-leaving');
      setTimeout(function () { $toast.remove(); }, 200);
    }, 3500);
  };

  /* ------------------------------------------------------------------
     Modal
     Abrir:  <button data-modal-open="#idDoModal">
     Fechar: <button data-modal-close>  (ou clicar fora / tecla ESC)
     ------------------------------------------------------------------ */
  CMS.abrirModal = function (seletor) {
    $(seletor).addClass('is-open').attr('aria-hidden', 'false');
    $('body').addClass('modal-open');
    setTimeout(function () { $(seletor).find('[data-modal-close]').last().trigger('focus'); }, 50);
  };

  CMS.fecharModal = function (seletor) {
    $(seletor || '.modal.is-open').removeClass('is-open').attr('aria-hidden', 'true');
    $('body').removeClass('modal-open');
  };

  $(function () {

    /* Menu lateral (celular/tablet) ---------------------------------- */
    $('[data-sidebar-toggle]').on('click', function () {
      $('body').toggleClass('sidebar-open');
    });
    $('.sidebar-backdrop').on('click', function () {
      $('body').removeClass('sidebar-open');
    });

    /* Dropdown do usuário -------------------------------------------- */
    $('[data-dropdown-toggle]').on('click', function (e) {
      e.stopPropagation();
      var $dropdown = $(this).closest('.dropdown');
      $('.dropdown').not($dropdown).removeClass('is-open');
      $dropdown.toggleClass('is-open');
      $(this).attr('aria-expanded', $dropdown.hasClass('is-open'));
    });
    $(document).on('click', function () {
      $('.dropdown').removeClass('is-open');
      $('[data-dropdown-toggle]').attr('aria-expanded', 'false');
    });

    /* Modais --------------------------------------------------------- */
    $(document).on('click', '[data-modal-open]', function (e) {
      e.preventDefault();
      CMS.abrirModal($(this).data('modal-open'));
    });
    $(document).on('click', '[data-modal-close]', function () {
      CMS.fecharModal($(this).closest('.modal'));
    });
    $(document).on('click', '.modal', function (e) {
      if (e.target === this) { CMS.fecharModal(this); }
    });

    /* Tecla ESC fecha modal, dropdown e menu ------------------------- */
    $(document).on('keydown', function (e) {
      if (e.key === 'Escape') {
        CMS.fecharModal();
        $('.dropdown').removeClass('is-open');
        $('body').removeClass('sidebar-open');
      }
    });

    /* Fechar alertas ------------------------------------------------- */
    $(document).on('click', '.alert__close', function () {
      $(this).closest('.alert').slideUp(180, function () { $(this).remove(); });
    });

    /* Mostrar/ocultar senha ------------------------------------------ */
    $(document).on('click', '.toggle-password', function () {
      var $input = $(this).siblings('input');
      var visivel = $input.attr('type') === 'text';
      $input.attr('type', visivel ? 'password' : 'text');
      $(this).find('i').toggleClass('bi-eye bi-eye-slash');
      $(this).attr('aria-label', visivel ? 'Mostrar senha' : 'Ocultar senha');
    });
  });

})(jQuery);
