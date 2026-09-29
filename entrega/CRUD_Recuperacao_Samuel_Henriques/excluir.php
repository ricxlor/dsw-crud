<?php
require 'conexao.php';

// Exclusão NUNCA por GET: um link, um robô de busca ou o histórico
// do navegador poderiam apagar dados sem o usuário querer.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php?tipo=erro&msg=' . urlencode('Método não permitido para exclusão.'));
    exit;
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    header('Location: index.php?tipo=erro&msg=' . urlencode('Registro não encontrado.'));
    exit;
}

// DELETE com Prepared Statement
$stmt = $conn->prepare('DELETE FROM alunos WHERE id = ?');
$stmt->bind_param('i', $id);

if (!$stmt->execute()) {
    error_log('Erro no DELETE: ' . $stmt->error);
    $tipo = 'erro';
    $msg  = 'Erro ao excluir o aluno.';
} elseif ($stmt->affected_rows === 0) {
    // Nenhuma linha apagada: o id não existe (ou já foi excluído)
    $tipo = 'erro';
    $msg  = 'Registro não encontrado.';
} else {
    $tipo = 'sucesso';
    $msg  = 'Aluno excluído com sucesso!';
}

$stmt->close();
$conn->close();

header('Location: index.php?tipo=' . $tipo . '&msg=' . urlencode($msg));
exit;
