<?php
// relatorios/movimentacao_produtos.php
require_once __DIR__ . '/../config/db.php';

// Set page title
$pageTitle = "Movimentação por Produto";

// Get search term from GET parameter
$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';
$selected_product = isset($_GET['produto_id']) ? (int)$_GET['produto_id'] : null;

// Get product list or details for a specific product
if (!empty($selected_product)) {
    // Get product details
    $stmt = $pdo->prepare("SELECT p.id, p.nome, p.descricao, g.nome AS grupo_nome,
                          f.nome AS fabricante_nome, p.codigo, p.unidade
                          FROM produtos p
                          LEFT JOIN grupos g ON p.id_grupo = g.id
                          LEFT JOIN fabricantes f ON p.id_fabricante = f.id
                          WHERE p.id = ?");
    $stmt->execute([$selected_product]);
    $produto = $stmt->fetch(PDO::FETCH_ASSOC);

    // Get movement history
    $stmt = $pdo->prepare("SELECT m.id, m.data, m.tipo, m.quantidade, m.observacao,
                          l.nome AS lugar_nome, pe.nome AS pessoa_nome
                          FROM movimentos m
                          LEFT JOIN lugares l ON m.id_lugar = l.id
                          LEFT JOIN pessoas pe ON m.id_pessoa = pe.id
                          WHERE m.id_produto = ?
                          ORDER BY m.data DESC");
    $stmt->execute([$selected_product]);
    $movimentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate current stock
    $stmt = $pdo->prepare("SELECT
                          COALESCE(SUM(CASE WHEN tipo = 'entrada' THEN quantidade ELSE 0 END), 0) AS total_entradas,
                          COALESCE(SUM(CASE WHEN tipo = 'saida' THEN quantidade ELSE 0 END), 0) AS total_saidas
                          FROM movimentos
                          WHERE id_produto = ?");
    $stmt->execute([$selected_product]);
    $totais = $stmt->fetch(PDO::FETCH_ASSOC);

    $total_entradas = $totais['total_entradas'];
    $total_saidas = $totais['total_saidas'];
    $saldo_atual = $total_entradas - $total_saidas;
} else {
    // Search for products
    $search_sql = '';
    $search_params = [];

    if (!empty($search_term)) {
        $search_sql = "WHERE p.nome LIKE ? OR p.descricao LIKE ? OR p.codigo LIKE ?";
        $search_params = ["%$search_term%", "%$search_term%", "%$search_term%"];
    }

    $sql = "SELECT p.id, p.nome, p.descricao, g.nome AS grupo_nome
            FROM produtos p
            LEFT JOIN grupos g ON p.id_grupo = g.id
            $search_sql
            ORDER BY p.nome
            LIMIT 50";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($search_params);
    $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Include header
include_once __DIR__ . '/../includes/header.php';
?>

<div class="content">
    <div class="widget">
        <?php if (empty($selected_product)): ?>
            <div class="widget-header">
                <h3 class="widget-title">Produtos</h3>
            </div>

            <div class="search-form mb-4">
                <form method="get" action="" class="d-flex">
                    <input type="text" name="search" class="form-control" placeholder="Buscar produto por nome..." value="<?= htmlspecialchars($search_term) ?>">
                    <button type="submit" class="btn btn-primary">Buscar</button>
                </form>
            </div>

            <?php if (!empty($produtos)): ?>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Produto</th>
                                <th>Grupo</th>
                                <th>Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($produtos as $produto): ?>
                            <tr>
                                <td><?= htmlspecialchars($produto['nome']) ?></td>
                                <td><?= htmlspecialchars($produto['grupo_nome'] ?? 'Não definido') ?></td>
                                <td>
                                    <a href="?produto_id=<?= $produto['id'] ?>" class="btn btn-sm btn-primary">Ver Movimentações</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <p>Nenhum produto encontrado.</p>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <!-- Product Detail View -->
            <div class="widget-header d-flex justify-content-between align-items-center">
                <h3 class="widget-title">Movimentação do Produto</h3>
                <a href="?" class="btn btn-sm btn-outline-secondary">Voltar para Lista</a>
            </div>

            <div class="product-details mb-4">
                <h3><?= htmlspecialchars($produto['nome']) ?></h3>
                <div class="details-grid">
                    <div class="detail-item">
                        <span class="detail-label">Código:</span>
                        <span class="detail-value"><?= htmlspecialchars($produto['codigo'] ?? 'N/A') ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Grupo:</span>
                        <span class="detail-value"><?= htmlspecialchars($produto['grupo_nome'] ?? 'Não definido') ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Fabricante:</span>
                        <span class="detail-value"><?= htmlspecialchars($produto['fabricante_nome'] ?? 'Não definido') ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Unidade:</span>
                        <span class="detail-value"><?= htmlspecialchars($produto['unidade'] ?? 'UN') ?></span>
                    </div>
                </div>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-label">Total Entradas</div>
                    <div class="stat-value entrada"><?= $total_entradas ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Total Saídas</div>
                    <div class="stat-value saida"><?= $total_saidas ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Saldo Atual</div>
                    <div class="stat-value saldo"><?= $saldo_atual ?></div>
                </div>
            </div>

            <div class="widget-header">
                <h3 class="widget-title">Histórico de Movimentações</h3>
            </div>

            <?php if (!empty($movimentos)): ?>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Tipo</th>
                                <th>Quantidade</th>
                                <th>Almoxarifado</th>
                                <th>Responsável</th>
                                <th>Observação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($movimentos as $movimento): ?>
                                <tr>
                                    <td><?= date('d/m/Y H:i', strtotime($movimento['data'])) ?></td>
                                    <td><span class="badge badge-<?= $movimento['tipo'] == 'entrada' ? 'success' : 'danger' ?>">
                                        <?= $movimento['tipo'] == 'entrada' ? 'Entrada' : 'Saída' ?>
                                    </span></td>
                                    <td class="text-right"><?= $movimento['quantidade'] ?></td>
                                    <td><?= htmlspecialchars($movimento['lugar_nome'] ?? 'Não definido') ?></td>
                                    <td><?= htmlspecialchars($movimento['pessoa_nome'] ?? 'Não definido') ?></td>
                                    <td><?= htmlspecialchars($movimento['observacao'] ?? '') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <p>Nenhuma movimentação registrada para este produto.</p>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <div class="btn-group mt-4">
            <a href="../index.php" class="btn btn-secondary">Voltar para a Página Inicial</a>
            <a href="../cadastros/movimento.php" class="btn btn-primary">Registrar Movimentação</a>
            <a href="relatorio_estoque.php" class="btn btn-outline-primary">Ver Estoque</a>
        </div>
    </div>
</div>

<style>
/* Minimal additional custom styles */
.entrada { color: var(--success); }
.saida { color: var(--danger); }
.saldo { color: <?= isset($saldo_atual) && $saldo_atual >= 0 ? 'var(--success)' : 'var(--danger)' ?>; }

.details-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 15px;
    margin-top: 15px;
}
.detail-item {
    margin-bottom: 5px;
}
.detail-label {
    font-weight: 600;
    color: var(--text-secondary);
}
</style>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>