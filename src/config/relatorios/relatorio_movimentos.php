<?php
// relatorios/relatorio_movimentos.php
require_once __DIR__ . '/../../config/db.php';

// Consulta que junta movimentos com produtos e pessoas
$sql = "SELECT m.id,
               p.nome AS produto,
               pe.nome AS pessoa,
               m.tipo,
               m.quantidade,
               m.data_movimento
        FROM movimentos m
        JOIN produtos p ON p.id = m.id_produto
        JOIN pessoas pe ON pe.id = m.id_pessoa
        ORDER BY m.data_movimento DESC";

$stmt = $pdo->query($sql);
$movimentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Movimentações</title>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 8px;
        }
    </style>
</head>
<body>
    <h1>Relatório de Movimentações</h1>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Produto</th>
                <th>Pessoa</th>
                <th>Tipo</th>
                <th>Quantidade</th>
                <th>Data do Movimento</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($movimentos as $mov): ?>
            <tr>
                <td><?= $mov['id'] ?></td>
                <td><?= $mov['produto'] ?></td>
                <td><?= $mov['pessoa'] ?></td>
                <td><?= ucfirst($mov['tipo']) ?></td>
                <td><?= $mov['quantidade'] ?></td>
                <td><?= date("d/m/Y H:i", strtotime($mov['data_movimento'])) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div style="margin-top: 20px;">
        <a href="../../../index.php">Voltar para a Página Inicial</a>
    </div>
</body>
</html>
