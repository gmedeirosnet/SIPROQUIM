<?php
// relatorios/movimentacao_produtos.php
require_once __DIR__ . '/../config/db.php';

// Initialize variables
$selected_product = null;
$movimentos = [];
$search_term = '';
$produtos = [];
$total_entrada = 0;
$total_saida = 0;
$total_movements = 0;
$saldo_atual = 0;

// Process search query if submitted
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_term = $_GET['search'];

    // Search for products matching the term
    $stmt = $pdo->prepare("
        SELECT id, nome, tipo, volume, unidade_medida
        FROM produtos
        WHERE nome LIKE :search
        ORDER BY nome
    ");
    $stmt->execute(['search' => "%{$search_term}%"]);
    $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// If a product is selected, get all its movements
if (isset($_GET['produto_id']) && !empty($_GET['produto_id'])) {
    $produto_id = (int) $_GET['produto_id'];

    // Get selected product details
    $stmt = $pdo->prepare("
        SELECT p.id, p.nome, p.tipo, p.volume, p.unidade_medida, g.nome AS grupo, f.nome AS fabricante
        FROM produtos p
        LEFT JOIN grupos g ON p.id_grupo = g.id
        LEFT JOIN fabricantes f ON p.id_fabricante = f.id
        WHERE p.id = :id
    ");
    $stmt->execute(['id' => $produto_id]);
    $selected_product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($selected_product) {
        // Get all movements for this product
        $stmt = $pdo->prepare("
            SELECT m.id, m.tipo, m.quantidade, m.data_movimento, m.observacao,
                   pe.nome AS pessoa, l.nome AS lugar
            FROM movimentos m
            JOIN pessoas pe ON m.id_pessoa = pe.id
            JOIN lugares l ON m.id_lugar = l.id
            WHERE m.id_produto = :id_produto
            ORDER BY m.data_movimento DESC
        ");

        $stmt->execute(['id_produto' => $produto_id]);
        $movimentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $total_movements = count($movimentos);

        // Calculate totals
        foreach ($movimentos as $movimento) {
            if ($movimento['tipo'] == 'entrada') {
                $total_entrada += $movimento['quantidade'];
            } else {
                $total_saida += $movimento['quantidade'];
            }
        }

        $saldo_atual = $total_entrada - $total_saida;
    }
}

// Get all products for direct selection (limit to reasonable number)
if (empty($produtos) && empty($selected_product)) {
    $stmt = $pdo->query("SELECT id, nome FROM produtos ORDER BY nome LIMIT 100");
    $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Movimentação de Produtos</title>
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
        .product-search {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f5f5f5;
            border-radius: 5px;
        }
        .product-search form {
            display: flex;
            gap: 10px;
        }
        .product-search input[type="text"] {
            flex-grow: 1;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .product-search button {
            padding: 8px 15px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .product-search button:hover {
            background-color: #0056b3;
        }
        .product-list {
            margin-top: 15px;
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid #ddd;
            border-radius: 4px;
            background-color: white;
        }
        .product-list table {
            width: 100%;
            border-collapse: collapse;
        }
        .product-list th, .product-list td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }
        .product-list th {
            background-color: #f2f2f2;
        }
        .product-list tr:hover {
            background-color: #f5f5f5;
        }
        .product-list a {
            color: #007bff;
            text-decoration: none;
        }
        .product-list a:hover {
            text-decoration: underline;
        }
        .product-details {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f0f7ff;
            border-radius: 5px;
            border-left: 4px solid #007bff;
        }
        .product-details h2 {
            margin-top: 0;
            color: #007bff;
        }
        .product-details p {
            margin: 5px 0;
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
        .saldo {
            color: <?php echo $saldo_atual >= 0 ? '#28a745' : '#dc3545'; ?>;
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
        <h1>Movimentação de Produtos</h1>

        <div class="product-search">
            <form method="get" action="">
                <input type="text" name="search" placeholder="Buscar produto por nome..." value="<?= htmlspecialchars($search_term) ?>">
                <button type="submit">Buscar</button>
            </form>

            <?php if (!empty($produtos) && empty($selected_product)): ?>
                <div class="product-list">
                    <table>
                        <thead>
                            <tr>
                                <th>Produto</th>
                                <th>Detalhes</th>
                                <th>Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($produtos as $produto): ?>
                            <tr>
                                <td><?= htmlspecialchars($produto['nome']) ?></td>
                                <td>
                                    <?php if (!empty($produto['tipo']) || !empty($produto['volume'])): ?>
                                        <?= htmlspecialchars($produto['tipo'] ?? '') ?>
                                        <?php if (!empty($produto['volume'])): ?>
                                            <?= htmlspecialchars($produto['volume']) ?>
                                            <?= htmlspecialchars($produto['unidade_medida'] ?? '') ?>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="?produto_id=<?= $produto['id'] ?>&search=<?= urlencode($search_term) ?>">
                                        Selecionar
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($selected_product): ?>
            <div class="product-details">
                <h2><?= htmlspecialchars($selected_product['nome']) ?></h2>
                <p><strong>Grupo:</strong> <?= htmlspecialchars($selected_product['grupo'] ?? 'Sem grupo') ?></p>
                <p><strong>Fabricante:</strong> <?= htmlspecialchars($selected_product['fabricante'] ?? 'Não especificado') ?></p>
                <?php if (!empty($selected_product['tipo']) || !empty($selected_product['volume'])): ?>
                    <p>
                        <strong>Detalhes:</strong>
                        <?= htmlspecialchars($selected_product['tipo'] ?? '') ?>
                        <?php if (!empty($selected_product['volume'])): ?>
                            <?= htmlspecialchars($selected_product['volume']) ?>
                            <?= htmlspecialchars($selected_product['unidade_medida'] ?? '') ?>
                        <?php endif; ?>
                    </p>
                <?php endif; ?>
                <p><a href="?search=<?= urlencode($search_term) ?>">« Selecionar outro produto</a></p>
            </div>

            <div class="summary">
                <div class="summary-item">
                    <div>Total de Movimentações</div>
                    <div class="summary-number"><?= $total_movements ?></div>
                </div>
                <div class="summary-item">
                    <div>Total de Entradas</div>
                    <div class="summary-number entrada"><?= $total_entrada ?></div>
                </div>
                <div class="summary-item">
                    <div>Total de Saídas</div>
                    <div class="summary-number saida"><?= $total_saida ?></div>
                </div>
                <div class="summary-item">
                    <div>Saldo Atual</div>
                    <div class="summary-number saldo"><?= $saldo_atual ?></div>
                </div>
            </div>

            <?php if (!empty($movimentos)): ?>
                <h2>Movimentações do Produto</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Tipo</th>
                            <th>Quantidade</th>
                            <th>Pessoa</th>
                            <th>Lugar</th>
                            <th>Observação</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($movimentos as $movimento): ?>
                            <tr>
                                <td><?= date('d/m/Y H:i', strtotime($movimento['data_movimento'])) ?></td>
                                <td>
                                    <?php if ($movimento['tipo'] == 'entrada'): ?>
                                        <span class="entrada"><strong>Entrada</strong></span>
                                    <?php else: ?>
                                        <span class="saida"><strong>Saída</strong></span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-right"><?= $movimento['quantidade'] ?></td>
                                <td><?= htmlspecialchars($movimento['pessoa']) ?></td>
                                <td><?= htmlspecialchars($movimento['lugar']) ?></td>
                                <td><?= htmlspecialchars($movimento['observacao'] ?: '-') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-data">
                    <p>Não há movimentações registradas para este produto.</p>
                </div>
            <?php endif; ?>
        <?php elseif (isset($_GET['produto_id'])): ?>
            <div class="no-data">
                <p>Produto não encontrado.</p>
            </div>
        <?php endif; ?>

        <div class="links">
            <a href="../index.php">Voltar para a Página Inicial</a>
            <a href="relatorio_estoque.php">Ver Relatório de Estoque</a>
            <a href="relatorio_movimentos.php">Ver Relatório de Movimentações</a>
            <a href="produtos_por_local.php">Ver Produtos por Local</a>
        </div>
    </div>
</body>
</html>