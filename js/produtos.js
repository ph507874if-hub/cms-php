/* CMS — interações da listagem e formulário de produtos.
   Espera IDs/classes descritos no README. Validação definitiva é no PHP. */
(function ($) {
  'use strict';
  $(function () {
    $('#filtroCategoria, #filtroStatus').on('change', function () {
      $('#formFiltros').trigger('submit');
    });

    $('#tabelaProdutos').on('click', '.btn-excluir', function () {
      $('#excluirProdutoNome').text($(this).data('nome') || 'este produto');
      $('#excluirProdutoId').val($(this).data('id') || '');
      if (window.CMS) CMS.abrirModal('#modalExcluirProduto');
    });

    var $foto = $('#imagem');
    var $preview = $('#previewImagem');
    var imagemPadrao = $preview.data('padrao') || $preview.attr('src');

    $('#btnEscolherImagem').on('click', function () { $foto.trigger('click'); });
    $foto.on('change', function () {
      var arquivo = this.files && this.files[0];
      if (!arquivo) return;
      if (!/^image\/(jpeg|png|webp|gif)$/.test(arquivo.type)) {
        CMS.toast('Escolha uma imagem JPG, PNG, WEBP ou GIF.', 'danger');
        this.value = ''; return;
      }
      if (arquivo.size > 5 * 1024 * 1024) {
        CMS.toast('A imagem deve ter no máximo 5 MB.', 'danger');
        this.value = ''; return;
      }
      var leitor = new FileReader();
      leitor.onload = function (e) { $preview.attr('src', e.target.result); };
      leitor.readAsDataURL(arquivo);
      $('#removerImagem').val('0');
      $('#btnRemoverImagem').removeClass('hidden');
    });

    $('#btnRemoverImagem').on('click', function () {
      $foto.val('');
      $preview.attr('src', imagemPadrao || '');
      $('#removerImagem').val('1');
      $(this).addClass('hidden');
    });

    $('#formProduto').on('submit', function (e) {
      var valido = true;
      var $nome = $('#nome');
      var $preco = $('#preco');
      var $estoque = $('#estoque');
      [$nome, $preco, $estoque].forEach(function ($campo) {
        $campo.removeClass('is-invalid').closest('.form-group').removeClass('has-error');
      });

      if (!$.trim($nome.val() || '')) {
        $nome.addClass('is-invalid').closest('.form-group').addClass('has-error')
          .find('.invalid-feedback').text('Informe o nome do produto.');
        valido = false;
      }
      if ($preco.length && ($preco.val() === '' || Number($preco.val()) < 0)) {
        $preco.addClass('is-invalid').closest('.form-group').addClass('has-error')
          .find('.invalid-feedback').text('Informe um preço válido.');
        valido = false;
      }
      if ($estoque.length && ($estoque.val() === '' || Number($estoque.val()) < 0)) {
        $estoque.addClass('is-invalid').closest('.form-group').addClass('has-error')
          .find('.invalid-feedback').text('Informe uma quantidade válida.');
        valido = false;
      }
      if (!valido) {
        e.preventDefault();
        CMS.toast('Confira os campos destacados.', 'danger');
      } else {
        $('#btnSalvarProduto').addClass('is-loading').prop('disabled', true)
          .html('<span class="spinner"></span> Salvando...');
      }
    });
  });
})(jQuery);
