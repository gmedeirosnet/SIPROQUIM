<?php
// relatorios/produtos_por_local.php
require_once __DIR__ . '/../config/db.php';

// Get inventory by location
$stmt = $pdo->query("
    SELECT
        l.id as lugar_id,
        l.nome as lugar,
        p.id as produto_id,
        p.nome as produto,
        g.nome as grupo,
        f.nome as fabricante,
        COALESCE(SUM(CASE WHEN m.tipo = 'entrada' THEN m.quantidade ELSE -m.quantidade END), 0) as saldo
    FROM lugares l
    LEFT JOIN movimentos m ON l.id = m.id_lugar
    LEFT JOIN produtos p ON m.id_produto = p.id
    LEFT JOIN grupos g ON p.id_grupo = g.id
    LEFT JOIN fabricantes f ON p.id_fabricante = f.id
    GROUP BY l.id, l.nome, p.id, p.nome, g.nome, f.nome
    HAVING COALESCE(SUM(CASE WHEN m.tipo = 'entrada' THEN m.quantidade ELSE -m.quantidade END), 0) > 0
    ORDER BY l.nome, p.nome
");

$produtos_por_lugar = [];
$total_lugares = 0;
$total_produtos = 0;
$total_itens = 0;
$produtos_unicos = [];

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $lugar_id = $row['lugar_id'];
    if (!isset($produtos_por_lugar[$lugar_id])) {
        $produtos_por_lugar[$lugar_id] = [
            'nome' => $row['lugar'],
            'produtos' => []
        ];
        $total_lugares++;
    }

    $produtos_por_lugar[$lugar_id]['produtos'][] = $row;
    $total_itens += $row['saldo'];

    if (!isset($produtos_unicos[$row['produto_id']])) {
        $produtos_unicos[$row['produto_id']] = true;
        $total_produtos++;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produtos Disponíveis por Local</title>
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
        .accordion-item {
            border: 1px solid #ddd;
            margin-bottom: 15px;
            border-radius: 4px;
            overflow: hidden;
        }
        .accordion-header {
            background-color: #f8f9fa;
            padding: 12px 15px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .accordion-header h3 {
            margin: 0;
            font-size: 18px;
        }
        .accordion-content {
            display: none;
            padding: 15px;
            border-top: 1px solid #ddd;
        }
        .accordion-item.active .accordion-content {
            display: block;
        }
        .badge {
            background-color: #007bff;
            color: white;
            padding: 3px 8px;
            border-radius: 10px;
            font-size: 14px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
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
        .no-data {
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 5px;
            text-align: center;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Produtos Disponíveis por Local</h1>

        <div class="summary">
            <div class="summary-item">
                <div>Total de Locais</div>
                <div class="summary-number"><?= $total_lugares ?></div>
            </div>
            <div class="summary-item">
                <div>Total de Produtos</div>
                <div class="summary-number"><?= $total_produtos ?></div>
            </div>
            <div class="summary-item">
                <div>Total de Itens em Estoque</div>
                <div class="summary-number"><?= $total_itens ?></div>
            </div>
        </div>

        <?php if (!empty($produtos_por_lugar)): ?>
            <div class="accordion">
                <?php foreach ($produtos_por_lugar as $lugar): ?>
                <div class="accordion-item">
                    <div class="accordion-header">
                        <h3><?= htmlspecialchars($lugar['nome']) ?></h3>
                        <span class="badge"><?= count($lugar['produtos']) ?></span>
                    </div>
                    <div class="accordion-content">
                        <table>
                            <thead>
                                <tr>
                                    <th>Produto</th>
                                    <th>Grupo</th>
                                    <th>Fabricante</th>
                                    <th class="text-right">Quantidade</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($lugar['produtos'] as $item): ?>
                                <tr>
                                    <td><?= htmlspecialchars($item['produto']) ?></td>
                                    <td><?= htmlspecialchars($item['grupo'] ?? 'Sem grupo') ?></td>
                                    <td><?= htmlspecialchars($item['fabricante'] ?? 'Não especificado') ?></td>
                                    <td class="text-right"><?= $item['saldo'] ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-data">
                <p>Não há produtos em estoque no momento.</p>
            </div>
        <?php endif; ?>

        <div class="links">
            <a href="../index.php">Voltar para a Página Inicial</a>
            <a href="relatorio_estoque.php">Ver Relatório de Estoque</a>
            <a href="relatorio_movimentos.php">Ver Relatório de Movimentações</a>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle accordion functionality
            const accordionHeaders = document.querySelectorAll('.accordion-header');

            accordionHeaders.forEach(header => {
                header.addEventListener('click', function() {
                    // Get the parent accordion item
                    const accordionItem = this.parentNode;

                    // Toggle active class
                    const wasActive = accordionItem.classList.contains('active');

                    // Close all accordion items
                    document.querySelectorAll('.accordion-item').forEach(item => {
                        item.classList.remove('active');
                    });

                    // If it wasn't active before, make it active now
                    if (!wasActive) {
                        accordionItem.classList.add('active');
                    }
                });
            });

            // Open the first accordion item by default
            const firstAccordionItem = document.querySelector('.accordion-item');
            if (firstAccordionItem) {
                firstAccordionItem.classList.add('active');
            }
        });
    </script>
</body>
</html>