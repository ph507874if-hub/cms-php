/* ==========================================================================
   CMS — Listagem de usuários (pages/usuarios.php)
   Requer: jQuery, main.js
   --------------------------------------------------------------------------
   A busca, os filtros e a paginação são feitos no SERVIDOR (usuarios.php),
   com SELECT ... WHERE ... LIMIT ... OFFSET, lendo os dados de $_GET.
   Aqui ficam apenas as facilidades de uso da página.
   ========================================================================== */

(function ($) {
  'use strict';

  $(function () {

    /* Filtros: ao escolher um perfil ou status, envia o formulário -------- */
    $('#filtroPerfil, #filtroStatus').on('change', function () {
      $('#formFiltros').trigger('submit');
    });

    /* Exclusão: preenche o modal com os dados da linha ---------------------
       O formulário do modal envia o id via POST para usuarios-excluir.php,
       que executa o DELETE e redireciona de volta com uma mensagem. */
    $('#tabelaUsuarios').on('click', '.btn-excluir', function () {
      $('#excluirNome').text($(this).data('nome'));
      $('#excluirId').val($(this).data('id'));
      CMS.abrirModal('#modalExcluir');
    });
  });

})(jQuery);
