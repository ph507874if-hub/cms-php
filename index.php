<?php
// A entrada principal do sistema é a loja pública.
// O acesso administrativo fica disponível em admin.php.
header('Location: loja.php', true, 302);
exit;
