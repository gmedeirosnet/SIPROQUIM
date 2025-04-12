<?php
// relatorios/relatorio_estoque.php
require_once __DIR__ . '/../config/db.php';

// Consulta SQL para calcular o saldo atual em estoque por produto e lugar
$sql = "SELECT
            p.id as produto_id,
            p.nome as produto,
            g.nome as grupo,
            l.nome as lugar,
            COALESCE(SUM(CASE
                WHEN m.tipo = 'entrada' THEN m.quantidade
                WHEN m.tipo = 'saida' THEN -m.quantidade
                ELSE 0
            END), 0) as saldo
        FROM produtos p
        LEFT JOIN grupos g ON p.id_grupo = g.id
        LEFT JOIN movimentos m ON p.id = m.id_produto
        LEFT JOIN lugares l ON m.id_lugar = l.id
        GROUP BY p.id, p.nome, g.nome, l.nome
        ORDER BY p.nome, l.nome";

try {
    $stmt = $pdo->query($sql);
    $estoques = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Erro ao gerar relatório: " . $e->getMessage();
    exit;
}

// Calcular totais
$total_produtos = 0;
$total_itens = 0;
$produtos_por_grupo = [];
$produtos_computados = [];

foreach ($estoques as $estoque) {
    if (!isset($produtos_computados[$estoque['produto_id']])) {
        $total_produtos++;
        $produtos_computados[$estoque['produto_id']] = true;
    }

    $total_itens += $estoque['saldo'];

    if (!empty($estoque['grupo'])) {
        if (!isset($produtos_por_grupo[$estoque['grupo']])) {
            $produtos_por_grupo[$estoque['grupo']] = 0;
        }
        $produtos_por_grupo[$estoque['grupo']]++;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Estoque</title>
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
        .low-stock {
            background-color: #ffe6e6;
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
    </style>
</head>
<body>
    <div class="container">
        <h1>Relatório de Estoque</h1>

        <div class="summary">
            <div class="summary-item">
                <div>Total de Produtos</div>
                <div class="summary-number"><?= $total_produtos ?></div>
            </div>
            <div class="summary-item">
                <div>Total de Itens em Estoque</div>
                <div class="summary-number"><?= $total_itens ?></div>
            </div>
        </div>

        <?php if (count($produtos_por_grupo) > 0): ?>
        <h2>Produtos por Grupo</h2>
        <table>
            <thead>
                <tr>
                    <th>Grupo</th>
                    <th class="text-center">Quantidade de Produtos</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($produtos_por_grupo as $grupo => $quantidade): ?>
                <tr>
                    <td><?= $grupo ?></td>
                    <td class="text-center"><?= $quantidade ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>

        <h2>Saldo por Produto e Local</h2>
        <table>
            <thead>
                <tr>
                    <th>Produto</th>
                    <th>Grupo</th>
                    <th>Local</th>
                    <th class="text-right">Saldo</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($estoques as $estoque): ?>
                <tr<?= $estoque['saldo'] < 5 ? ' class="low-stock"' : '' ?>>
                    <td><?= $estoque['produto'] ?></td>
                    <td><?= $estoque['grupo'] ?: 'Sem grupo' ?></td>
                    <td><?= $estoque['lugar'] ?: 'Não especificado' ?></td>
                    <td class="text-right"><?= $estoque['saldo'] ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="links">
            <a href="../index.php">Voltar para a Página Inicial</a>
            <a href="relatorio_movimentos.php">Ver Relatório de Movimentações</a>
        </div>
    </div>
</body>
</html>
