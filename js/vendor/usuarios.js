/* CMS — listagem de usuários. Filtros e paginação são processados pelo PHP. */
(function ($) {
  'use strict';
  $(function () {
    $('#filtroPerfil, #filtroStatus').on('change', function () {
      $('#formFiltros').trigger('submit');
    });

    $('#tabelaUsuarios').on('click', '.btn-excluir', function () {
      $('#excluirNome').text($(this).data('nome') || 'este usuário');
      $('#excluirId').val($(this).data('id') || '');
      if (window.CMS) CMS.abrirModal('#modalExcluir');
    });

    $('#formFiltros').on('submit', function () {
      var $btn = $(this).find('[type="submit"]');
      $btn.addClass('is-loading');
    });
  });
})(jQuery);
