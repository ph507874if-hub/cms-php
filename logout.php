<?php
/* ==========================================================================
   Sair do painel (logout)
   ========================================================================== */

require_once __DIR__ . '/includes/sessao.php';

encerrarSessao($pdo); // apaga a sessão e o cookie "Manter conectado"

definirMensagem('Você saiu do painel com segurança.', 'success');
redirecionar('index.php');
