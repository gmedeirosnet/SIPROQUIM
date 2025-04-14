<?php
// relatorios/relatorio_estoque.php
require_once __DIR__ . '/../config/db.php';

// Set page title for the header
$pageTitle = "Estoque Atual";

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

// Include the header
include_once __DIR__ . '/../includes/header.php';
?>

<div class="content">
    <div class="widget">
        <div class="widget-header">
            <h3 class="widget-title">Resumo do Estoque</h3>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?= $total_produtos ?></div>
                <div class="stat-label">Total de Produtos</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= $total_itens ?></div>
                <div class="stat-label">Total de Itens em Estoque</div>
            </div>
        </div>

        <?php if (count($produtos_por_grupo) > 0): ?>
        <div class="widget-header">
            <h3 class="widget-title">Produtos por Grupo</h3>
        </div>
        <div class="table-container">
            <table class="table">
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
        </div>
        <?php endif; ?>

        <div class="widget-header">
            <h3 class="widget-title">Saldo por Produto e Almoxarifado</h3>
        </div>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Produto</th>
                        <th>Grupo</th>
                        <th>Almoxarifado</th>
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
        </div>

        <div class="btn-group mt-4">
            <a href="../index.php" class="btn btn-secondary">Voltar para a Página Inicial</a>
            <a href="relatorio_movimentos.php" class="btn btn-outline-primary">Movimentações</a>
            <a href="produtos_por_local.php" class="btn btn-outline-primary">Produtos por Almoxarifado</a>
        </div>
    </div>
</div>

<style>
.low-stock {
    background-color: rgba(231, 76, 60, 0.1);
}
</style>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
