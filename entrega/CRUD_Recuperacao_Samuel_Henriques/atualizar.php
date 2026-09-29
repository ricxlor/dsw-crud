<?php
require 'conexao.php';

// Só aceita envio pelo formulário (POST)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$id    = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$nome  = trim($_POST['nome'] ?? '');
$email = trim($_POST['email'] ?? '');
$curso = trim($_POST['curso'] ?? '');

if (!$id) {
    header('Location: index.php?tipo=erro&msg=' . urlencode('Registro não encontrado.'));
    exit;
}

// Em caso de erro de validação, volta para o formulário de edição
$voltar = 'editar.php?id=' . $id . '&tipo=erro&msg=';

if ($nome === '' || $email === '' || $curso === '') {
    header('Location: ' . $voltar . urlencode('Preencha todos os campos.'));
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: ' . $voltar . urlencode('E-mail inválido.'));
    exit;
}

// 1) Verifica se o registro existe antes de atualizar
$stmt = $conn->prepare('SELECT id FROM alunos WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$existe = $stmt->get_result()->num_rows > 0;
$stmt->close();

if (!$existe) {
    $conn->close();
    header('Location: index.php?tipo=erro&msg=' . urlencode('Registro não encontrado.'));
    exit;
}

// 2) UPDATE com Prepared Statement (o WHERE garante que só este aluno é alterado)
$stmt = $conn->prepare('UPDATE alunos SET nome = ?, email = ?, curso = ? WHERE id = ?');
$stmt->bind_param('sssi', $nome, $email, $curso, $id); // 3 strings + 1 inteiro

if (!$stmt->execute()) {
    error_log('Erro no UPDATE: ' . $stmt->error);
    $tipo = 'erro';
    $msg  = 'Erro ao atualizar o aluno.';
} elseif ($stmt->affected_rows === 0) {
    // O registro existe, mas os dados enviados eram iguais aos já salvos
    $tipo = 'sucesso';
    $msg  = 'Nenhuma alteração foi feita.';
} else {
    $tipo = 'sucesso';
    $msg  = 'Aluno atualizado com sucesso!';
}

$stmt->close();
$conn->close();

header('Location: index.php?tipo=' . $tipo . '&msg=' . urlencode($msg));
exit;
