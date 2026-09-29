<?php
// Dados de acesso ao banco
if (getenv('DB_HOST') !== false) {
    // Definidos por variáveis de ambiente: docker-compose.yml ou scripts rodar.sh / rodar.bat
    $host    = getenv('DB_HOST');
    $usuario = getenv('DB_USER') ?: 'root';
    $senha   = getenv('DB_PASS') ?: ''; // sem DB_PASS = banco sem senha
} else {
    // Padrão: MariaDB instalado na máquina (usuário criado conforme o README)
    $host    = 'localhost';
    $usuario = 'crud';
    $senha   = 'root';
}
$banco = 'crud_recuperacao';

// Desde o PHP 8.1 o mysqli lança exceções por padrão.
// Desligamos isso para tratar os erros manualmente com connect_errno.
mysqli_report(MYSQLI_REPORT_OFF);

// Conexão orientada a objetos
$conn = new mysqli($host, $usuario, $senha, $banco);

// Trata falha de conexão (connect_errno é diferente de 0 quando dá erro)
if ($conn->connect_errno) {
    // Não mostramos o erro técnico ao usuário, apenas registramos no log
    error_log('Erro de conexão: ' . $conn->connect_error);
    die('Não foi possível conectar ao banco de dados. Tente novamente mais tarde.');
}

// Garante que acentos sejam gravados e lidos corretamente
$conn->set_charset('utf8mb4');
