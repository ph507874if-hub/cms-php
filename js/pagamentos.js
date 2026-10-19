/* CMS — filtros, detalhes e ações visuais de pagamentos. */
(function ($) {
  'use strict';
  $(function () {
    $('#filtroStatus, #filtroDataInicio, #filtroDataFim').on('change', function () {
      $('#formFiltrosPagamentos').trigger('submit');
    });

    $('#tabelaPagamentos').on('click', '.btn-detalhes', function () {
      var destino = $(this).attr('href');
      if (destino) window.location.href = destino;
    });

    $('#formFiltrosPagamentos').on('submit', function () {
      $(this).find('[type="submit"]').addClass('is-loading');
    });

    $('.btn-imprimir').on('click', function () {
      window.print();
    });
  });
})(jQuery);
