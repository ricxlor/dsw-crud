<?php
require 'conexao.php';

// Só aceita envio pelo formulário (POST)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// trim() remove espaços do início e do fim
$nome  = trim($_POST['nome'] ?? '');
$email = trim($_POST['email'] ?? '');
$curso = trim($_POST['curso'] ?? '');

// Validação no servidor (o "required" do HTML pode ser burlado)
if ($nome === '' || $email === '' || $curso === '') {
    header('Location: index.php?tipo=erro&msg=' . urlencode('Preencha todos os campos.'));
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: index.php?tipo=erro&msg=' . urlencode('E-mail inválido.'));
    exit;
}

// INSERT com Prepared Statement: cada ? será substituído por um valor com segurança
$stmt = $conn->prepare('INSERT INTO alunos (nome, email, curso) VALUES (?, ?, ?)');
$stmt->bind_param('sss', $nome, $email, $curso);

if ($stmt->execute()) {
    $tipo = 'sucesso';
    $msg  = 'Aluno cadastrado com sucesso!';
} else {
    error_log('Erro no INSERT: ' . $stmt->error);
    $tipo = 'erro';
    $msg  = 'Erro ao cadastrar o aluno.';
}

$stmt->close();
$conn->close();

// Volta para a listagem mostrando a mensagem
header('Location: index.php?tipo=' . $tipo . '&msg=' . urlencode($msg));
exit;
