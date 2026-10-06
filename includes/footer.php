<?php
/* ==========================================================================
   Fim do layout do painel (rodapé e scripts)
   --------------------------------------------------------------------------
   Antes de incluir este arquivo, a página pode definir:
     $scripts → lista de arquivos da pasta /js usados só nesta página
                (ex.: $scripts = ['usuarios.js'];)
   ========================================================================== */

$scripts = $scripts ?? [];
?>
      <footer class="footer">
        <span>&copy; <?= date('Y') ?> Painel CMS. Todos os direitos reservados.</span>
        <span>Projeto Prático — PHP &amp; MySQL</span>
      </footer>
    </div><!-- /.main -->
  </div><!-- /.app -->

  <!-- Scripts -->
  <script src="../js/vendor/jquery.min.js"></script>
  <script src="../js/main.js"></script>
<?php if (!empty($usaEditor)): ?>
  <script src="../js/vendor/summernote/summernote-lite.min.js"></script>
  <script src="../js/vendor/summernote/lang/summernote-pt-BR.min.js"></script>
<?php endif; ?>
<?php foreach ($scripts as $script): ?>
  <script src="../js/<?= e($script) ?>"></script>
<?php endforeach; ?>
</body>
</html>
