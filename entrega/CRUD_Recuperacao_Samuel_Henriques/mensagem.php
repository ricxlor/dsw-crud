<?php
// Exibe a mensagem de feedback recebida pela URL (?tipo=sucesso&msg=...)
// Aceitamos apenas os tipos conhecidos, para não usar qualquer texto como classe CSS
$tipo = $_GET['tipo'] ?? '';
$msg  = $_GET['msg'] ?? '';

if ($msg !== '' && ($tipo === 'sucesso' || $tipo === 'erro')): ?>
    <div class="mensagem <?= htmlspecialchars($tipo, ENT_QUOTES, 'UTF-8') ?>">
        <?= htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') ?>
    </div>
<?php endif; ?>
