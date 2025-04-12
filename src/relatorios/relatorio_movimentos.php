<?php
// relatorios/relatorio_movimentos.php
require_once __DIR__ . '/../config/db.php';

// Consulta que junta movimentos com produtos e pessoas
$sql = "SELECT m.id,
               p.nome AS produto,
               pe.nome AS pessoa,
               l.nome AS lugar,
               m.tipo,
               m.quantidade,
               m.observacao,
               m.data_movimento
        FROM movimentos m
        JOIN produtos p ON p.id = m.id_produto
        JOIN pessoas pe ON pe.id = m.id_pessoa
        LEFT JOIN lugares l ON l.id = m.id_lugar
        ORDER BY m.data_movimento DESC";

try {
    $stmt = $pdo->query($sql);
    $movimentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Erro ao gerar relatório: " . $e->getMessage();
    exit;
}

// Calcular totais e estatísticas
$total_movimentos = count($movimentos);
$total_entradas = 0;
$total_saidas = 0;
$quantidade_entrada = 0;
$quantidade_saida = 0;

foreach ($movimentos as $mov) {
    if ($mov['tipo'] == 'entrada') {
        $total_entradas++;
        $quantidade_entrada += $mov['quantidade'];
    } else {
        $total_saidas++;
        $quantidade_saida += $mov['quantidade'];
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Movimentações</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        h1, h2 {
            color: #333;
        }
        .summary {
            background-color: #f5f5f5;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 10px;
        }
        .summary-item {
            padding: 10px;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .summary-number {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
        }
        .entrada {
            color: #28a745;
        }
        .saida {
            color: #dc3545;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .links {
            margin-top: 20px;
        }
        .links a {
            display: inline-block;
            margin-right: 10px;
            padding: 8px 15px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 3px;
        }
        .links a:hover {
            background-color: #0056b3;
        }
        .filtro {
            margin-bottom: 20px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Relatório de Movimentações</h1>

        <div class="summary">
            <div class="summary-item">
                <div>Total de Movimentações</div>
                <div class="summary-number"><?= $total_movimentos ?></div>
            </div>
            <div class="summary-item">
                <div>Entradas</div>
                <div class="summary-number entrada"><?= $total_entradas ?></div>
                <div><strong>Quantidade:</strong> <?= $quantidade_entrada ?> itens</div>
            </div>
            <div class="summary-item">
                <div>Saídas</div>
                <div class="summary-number saida"><?= $total_saidas ?></div>
                <div><strong>Quantidade:</strong> <?= $quantidade_saida ?> itens</div>
            </div>
        </div>

        <h2>Lista de Movimentações</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Produto</th>
                    <th>Pessoa</th>
                    <th>Local</th>
                    <th>Tipo</th>
                    <th>Quantidade</th>
                    <th>Data do Movimento</th>
                    <th>Observação</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($movimentos as $mov): ?>
                <tr>
                    <td><?= $mov['id'] ?></td>
                    <td><?= htmlspecialchars($mov['produto']) ?></td>
                    <td><?= htmlspecialchars($mov['pessoa']) ?></td>
                    <td><?= htmlspecialchars($mov['lugar'] ?: 'Não especificado') ?></td>
                    <td>
                        <?php if ($mov['tipo'] == 'entrada'): ?>
                            <span class="entrada"><strong>Entrada</strong></span>
                        <?php else: ?>
                            <span class="saida"><strong>Saída</strong></span>
                        <?php endif; ?>
                    </td>
                    <td class="text-right"><?= $mov['quantidade'] ?></td>
                    <td><?= date("d/m/Y H:i", strtotime($mov['data_movimento'])) ?></td>
                    <td><?= htmlspecialchars($mov['observacao'] ?: '') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="links">
            <a href="../index.php">Voltar para a Página Inicial</a>
            <a href="relatorio_estoque.php">Ver Relatório de Estoque</a>
        </div>
    </div>
</body>
</html>
