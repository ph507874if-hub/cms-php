<?php

$host = '127.0.0.1';
$porta = '3307';
$banco = 'loja_pedro';
$usuario = 'root';
$senha = '';

echo "<h2>Teste de conexão MySQL</h2>";

if (!extension_loaded('pdo_mysql')) {
    die("ERRO: A extensão PDO_MYSQL não está habilitada no PHP.");
}

try {
    $pdo = new PDO(
        "mysql:host=$host;port=$porta;dbname=$banco;charset=utf8mb4",
        $usuario,
        $senha,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    echo "<p style='color:green'>CONEXÃO REALIZADA COM SUCESSO!</p>";

    $resultado = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

    echo "<h3>Tabelas encontradas:</h3>";

    foreach ($resultado as $tabela) {
        echo $tabela . "<br>";
    }

} catch (PDOException $e) {
    echo "<p style='color:red'>ERRO NA CONEXÃO:</p>";
    echo htmlspecialchars($e->getMessage());
}