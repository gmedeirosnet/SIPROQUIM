<?php
// relatorios/relatorio_movimentos.php
require_once __DIR__ . '/../config/db.php';

// Set page title for header
$pageTitle = "Histórico de Movimentações";

// Initialize filters
$data_inicio = isset($_GET['data_inicio']) ? $_GET['data_inicio'] : date('Y-m-d', strtotime('-30 days'));
$data_fim = isset($_GET['data_fim']) ? $_GET['data_fim'] : date('Y-m-d');
$tipo = isset($_GET['tipo']) ? $_GET['tipo'] : '';

// Build query
$where_conditions = [];
$params = [];

$where_conditions[] = "m.data BETWEEN ? AND ?";
$params[] = $data_inicio . ' 00:00:00';
$params[] = $data_fim . ' 23:59:59';

if (!empty($tipo)) {
    $where_conditions[] = "m.tipo = ?";
    $params[] = $tipo;
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Query for movements
$sql = "SELECT
            m.id,
            m.data,
            m.tipo,
            m.quantidade,
            m.observacao,
            p.nome as produto_nome,
            p.codigo as produto_codigo,
            l.nome as lugar_nome,
            pe.nome as pessoa_nome
        FROM
            movimentos m
        LEFT JOIN
            produtos p ON m.id_produto = p.id
        LEFT JOIN
            lugares l ON m.id_lugar = l.id
        LEFT JOIN
            pessoas pe ON m.id_pessoa = pe.id
        $where_clause
        ORDER BY
            m.data DESC
        LIMIT 500";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $movimentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate totals for summary
    $stmt = $pdo->prepare("
        SELECT
            COUNT(*) as total_movimentos,
            SUM(CASE WHEN tipo = 'entrada' THEN 1 ELSE 0 END) as total_entradas,
            SUM(CASE WHEN tipo = 'saida' THEN 1 ELSE 0 END) as total_saidas,
            SUM(CASE WHEN tipo = 'entrada' THEN quantidade ELSE 0 END) as qtd_entradas,
            SUM(CASE WHEN tipo = 'saida' THEN quantidade ELSE 0 END) as qtd_saidas
        FROM
            movimentos m
        $where_clause
    ");
    $stmt->execute($params);
    $summary = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Erro ao gerar relatório: " . $e->getMessage();
    exit;
}

// Include the header
include_once __DIR__ . '/../includes/header.php';
?>

<div class="content">
    <div class="widget">
        <div class="widget-header">
            <h3 class="widget-title">Histórico de Movimentações</h3>
        </div>

        <!-- Filter Form -->
        <div class="filtro mb-4">
            <form method="get" action="" class="filter-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="data_inicio">Data Início:</label>
                        <input type="date" id="data_inicio" name="data_inicio" value="<?= $data_inicio ?>" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="data_fim">Data Fim:</label>
                        <input type="date" id="data_fim" name="data_fim" value="<?= $data_fim ?>" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="tipo">Tipo:</label>
                        <select id="tipo" name="tipo" class="form-control">
                            <option value="">Todos</option>
                            <option value="entrada" <?= $tipo == 'entrada' ? 'selected' : '' ?>>Entradas</option>
                            <option value="saida" <?= $tipo == 'saida' ? 'selected' : '' ?>>Saídas</option>
                        </select>
                    </div>
                    <div class="form-group align-self-end">
                        <button type="submit" class="btn btn-primary">Filtrar</button>
                        <a href="?data_inicio=<?= date('Y-m-d', strtotime('-30 days')) ?>&data_fim=<?= date('Y-m-d') ?>" class="btn btn-outline-secondary">Limpar</a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Summary -->
        <div class="summary mb-4">
            <div class="summary-item">
                <div>Total de Movimentações</div>
                <div class="summary-number"><?= $summary['total_movimentos'] ?></div>
            </div>
            <div class="summary-item">
                <div>Entradas</div>
                <div class="summary-number entrada"><?= $summary['total_entradas'] ?></div>
                <div class="summary-detail"><?= number_format($summary['qtd_entradas'], 0, ',', '.') ?> unidades</div>
            </div>
            <div class="summary-item">
                <div>Saídas</div>
                <div class="summary-number saida"><?= $summary['total_saidas'] ?></div>
                <div class="summary-detail"><?= number_format($summary['qtd_saidas'], 0, ',', '.') ?> unidades</div>
            </div>
        </div>

        <!-- Results Table -->
        <?php if (count($movimentos) > 0): ?>
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Tipo</th>
                            <th>Produto</th>
                            <th>Qtd</th>
                            <th>Local</th>
                            <th>Pessoa</th>
                            <th>Observação</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($movimentos as $movimento): ?>
                            <tr>
                                <td><?= date('d/m/Y H:i', strtotime($movimento['data'])) ?></td>
                                <td>
                                    <span class="badge badge-<?= $movimento['tipo'] == 'entrada' ? 'success' : 'danger' ?>">
                                        <?= $movimento['tipo'] == 'entrada' ? 'Entrada' : 'Saída' ?>
                                    </span>
                                </td>
                                <td>
                                    <?= htmlspecialchars($movimento['produto_nome']) ?>
                                    <?php if (!empty($movimento['produto_codigo'])): ?>
                                        <small class="text-muted"><?= htmlspecialchars($movimento['produto_codigo']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td class="text-right"><?= $movimento['quantidade'] ?></td>
                                <td><?= htmlspecialchars($movimento['lugar_nome'] ?? '') ?></td>
                                <td><?= htmlspecialchars($movimento['pessoa_nome'] ?? '') ?></td>
                                <td><?= htmlspecialchars($movimento['observacao'] ?? '') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info">Nenhum movimento encontrado para o período selecionado.</div>
        <?php endif; ?>

        <div class="btn-group mt-4">
            <a href="../index.php" class="btn btn-secondary">Voltar para a Página Inicial</a>
            <a href="relatorio_estoque.php" class="btn btn-outline-primary">Ver Estoque</a>
            <a href="produtos_por_local.php" class="btn btn-outline-primary">Produtos por Almoxarifado</a>
        </div>
    </div>
</div>

<style>
/* Minimal additional custom styles */
.entrada { color: var(--success); }
.saida { color: var(--danger); }

.filter-form {
    background-color: var(--light-gray);
    padding: 15px;
    border-radius: var(--radius);
}

.summary {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 15px;
    margin-bottom: 20px;
}

.summary-item {
    padding: 15px;
    background-color: var(--white);
    border-radius: var(--radius);
    box-shadow: var(--shadow);
}

.summary-number {
    font-size: 24px;
    font-weight: 600;
    margin: 5px 0;
}

.summary-detail {
    font-size: 0.85rem;
    color: var(--text-secondary);
}

.form-row {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
}

.form-group {
    flex: 1;
    min-width: 200px;
}

.align-self-end {
    align-self: flex-end;
}
</style>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
