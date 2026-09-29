<?php
require 'conexao.php';

// Converte o id da URL para número inteiro (retorna false/null se for inválido)
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

$aluno = null;

if ($id) {
    // SELECT com Prepared Statement para buscar o aluno pelo id
    $stmt = $conn->prepare('SELECT id, nome, email, curso FROM alunos WHERE id = ?');
    $stmt->bind_param('i', $id); // i = inteiro
    $stmt->execute();
    $resultado = $stmt->get_result();
    $aluno = $resultado->fetch_assoc(); // null se não encontrou
    $stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Aluno</title>
    <link rel="stylesheet" href="estilo.css">
</head>
<body>
    <h1>Editar Aluno</h1>

    <?php require 'mensagem.php'; ?>

    <section>
        <?php if (!$aluno): ?>
            <!-- ID inválido ou inexistente -->
            <div class="mensagem erro">Registro não encontrado.</div>
            <a href="index.php" class="botao botao-secundario">Voltar</a>
        <?php else: ?>
            <!-- Formulário pré-preenchido: os dados do banco vão no atributo value -->
            <form action="atualizar.php" method="post">
                <!-- O id vai escondido para o atualizar.php saber qual registro alterar -->
                <input type="hidden" name="id"
                       value="<?= htmlspecialchars($aluno['id'], ENT_QUOTES, 'UTF-8') ?>">

                <label for="nome">Nome</label>
                <input type="text" id="nome" name="nome" maxlength="100" required
                       value="<?= htmlspecialchars($aluno['nome'], ENT_QUOTES, 'UTF-8') ?>">

                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" maxlength="100" required
                       value="<?= htmlspecialchars($aluno['email'], ENT_QUOTES, 'UTF-8') ?>">

                <label for="curso">Curso</label>
                <input type="text" id="curso" name="curso" maxlength="100" required
                       value="<?= htmlspecialchars($aluno['curso'], ENT_QUOTES, 'UTF-8') ?>">

                <button type="submit">Salvar alterações</button>
                <a href="index.php" class="botao botao-secundario">Cancelar</a>
            </form>
        <?php endif; ?>
    </section>
</body>
</html>
