<?php
require 'conexao.php';

// Termo digitado na busca (vazio = listar todos)
$busca = trim($_GET['busca'] ?? '');

// SELECT com Prepared Statement.
// Os % ficam no valor, e não no SQL: '%ana%' encontra "Ana", "Mariana" etc.
// Com a busca vazia o termo vira '%%', que encontra todos os registros.
$sql = 'SELECT id, nome, email, curso FROM alunos
        WHERE nome LIKE ? OR email LIKE ? OR curso LIKE ?
        ORDER BY nome';

$stmt = $conn->prepare($sql);
$termo = '%' . $busca . '%';
$stmt->bind_param('sss', $termo, $termo, $termo); // s = string
$stmt->execute();
$resultado = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Alunos</title>
    <link rel="stylesheet" href="estilo.css">
</head>
<body>
    <h1>Cadastro de Alunos</h1>

    <?php require 'mensagem.php'; ?>

    <!-- INSERT: formulário de cadastro -->
    <section>
        <h2>Novo aluno</h2>
        <form action="cadastrar.php" method="post">
            <label for="nome">Nome</label>
            <input type="text" id="nome" name="nome" maxlength="100" required>

            <label for="email">E-mail</label>
            <input type="email" id="email" name="email" maxlength="100" required>

            <label for="curso">Curso</label>
            <input type="text" id="curso" name="curso" maxlength="100" required>

            <button type="submit">Cadastrar</button>
        </form>
    </section>

    <!-- SELECT: busca e listagem -->
    <section>
        <h2>Alunos cadastrados</h2>

        <form action="index.php" method="get">
            <label for="busca">Buscar por nome, e-mail ou curso</label>
            <input type="search" id="busca" name="busca"
                   value="<?= htmlspecialchars($busca, ENT_QUOTES, 'UTF-8') ?>">
            <button type="submit">Buscar</button>
            <a href="index.php" class="botao botao-secundario">Limpar</a>
        </form>

        <?php if ($resultado->num_rows === 0): ?>
            <p>Nenhum registro encontrado.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>E-mail</th>
                        <th>Curso</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($aluno = $resultado->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($aluno['id'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($aluno['nome'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($aluno['email'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($aluno['curso'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <!-- Editar só abre o formulário, então pode ser um link (GET) -->
                                <a href="editar.php?id=<?= htmlspecialchars($aluno['id'], ENT_QUOTES, 'UTF-8') ?>"
                                   class="botao">Editar</a>

                                <!-- Excluir altera o banco: usa POST e pede confirmação -->
                                <form action="excluir.php" method="post"
                                      onsubmit="return confirm('Tem certeza que deseja excluir este registro?');">
                                    <input type="hidden" name="id"
                                           value="<?= htmlspecialchars($aluno['id'], ENT_QUOTES, 'UTF-8') ?>">
                                    <button type="submit" class="botao-excluir">Excluir</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>
</body>
</html>
<?php
$stmt->close();
$conn->close();
