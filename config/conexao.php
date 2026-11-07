<?php
/* ==========================================================================
   Conexão com o banco de dados (PDO)
   --------------------------------------------------------------------------
   Inclua este arquivo em qualquer página que precise do banco:
       require_once '../config/conexao.php';
   Depois disso, a variável $pdo estará disponível.
   ========================================================================== */

// Ajuste estes dados conforme o seu ambiente (no XAMPP o padrão é root sem senha)
define('DB_HOST',    'localhost');
define('DB_NOME',    'loja_pedro');
define('DB_USUARIO', 'root');
define('DB_SENHA',   '');

// Fuso horário usado pelo PHP nas funções de data (date, strtotime...)
date_default_timezone_set('America/Sao_Paulo');

try {
    $pdo = new PDO(
        "mysql:host=127.0.0.1;port=3307;dbname=loja_pedro;charset=utf8mb4",
        'root',
        '',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );

    // Deixa o MySQL no mesmo fuso do PHP (assim NOW() e date() marcam a mesma hora)
    $pdo->exec("SET time_zone = '" . date('P') . "'");

} catch (PDOException $erro) {
    // Nunca mostre $erro->getMessage() ao visitante: a mensagem pode revelar
    // dados do servidor. Em desenvolvimento, consulte o log de erros do PHP.
    error_log('Erro de conexão com o banco: ' . $erro->getMessage());
    http_response_code(500);
    exit('Não foi possível conectar ao banco de dados. Confira os dados em config/conexao.php '
       . 'e se o arquivo sql/banco.sql já foi importado.');
}
