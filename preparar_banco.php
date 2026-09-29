<?php
// Prepara o banco de dados para rodar o sistema sem Docker.
// Chamado pelos scripts rodar.sh / rodar.bat, que definem DB_HOST, DB_USER e DB_PASS.
// Se a tabela alunos já existir, os dados são mantidos. Para apagar e recriar:
//   php preparar_banco.php --recriar

// Só pode ser executado pelo terminal, nunca pelo navegador
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Acesso negado.');
}

$host    = getenv('DB_HOST') ?: 'localhost';
$usuario = getenv('DB_USER') ?: 'root';
$senha   = getenv('DB_PASS') ?: '';

mysqli_report(MYSQLI_REPORT_OFF);

// Conecta sem escolher o banco, pois ele pode ainda não existir
// (@ evita o aviso do PHP; a mensagem de erro é exibida logo abaixo)
$conn = @new mysqli($host, $usuario, $senha);
if ($conn->connect_errno) {
    echo "ERRO: não foi possível conectar ao MySQL em '$host' com o usuário '$usuario'.\n";
    echo 'Detalhe: ' . $conn->connect_error . "\n";
    exit(1);
}
$conn->set_charset('utf8mb4');

$resultado = $conn->query(
    "SELECT COUNT(*) FROM information_schema.tables
     WHERE table_schema = 'crud_recuperacao' AND table_name = 'alunos'"
);
$tabelaExiste = $resultado->fetch_row()[0] > 0;

if ($tabelaExiste && !in_array('--recriar', $argv, true)) {
    echo "Banco crud_recuperacao já existe. Mantendo os dados.\n";
    exit(0);
}

// Executa o banco.sql inteiro (vários comandos de uma vez)
$sql = file_get_contents(__DIR__ . '/banco.sql');
if (!$conn->multi_query($sql)) {
    echo 'ERRO ao executar o banco.sql: ' . $conn->error . "\n";
    exit(1);
}
// É preciso percorrer todos os resultados para o MySQL executar cada comando
do {
    if ($r = $conn->store_result()) {
        $r->free();
    }
} while ($conn->more_results() && $conn->next_result());

if ($conn->errno) {
    echo 'ERRO ao executar o banco.sql: ' . $conn->error . "\n";
    exit(1);
}

echo "Banco crud_recuperacao criado com os alunos de exemplo.\n";
