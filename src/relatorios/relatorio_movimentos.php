<?php
// relatorios/relatorio_movimentos.php
require_once __DIR__ . '/../config/db.php';

// Set page title for the header
// $pageTitle = 'Relatório de Movimentações';

// Initialize filter variables
$filter_type = $_GET['filter_type'] ?? 'todos';
$date_start = null;
$date_end = null;
$date_range_start = $_GET['date_start'] ?? '';
$date_range_end = $_GET['date_end'] ?? '';

// Define date filters based on selected filter type
if ($filter_type === 'diario') {
    // Daily filter (today)
    $date_start = date('Y-m-d 00:00:00');
    $date_end = date('Y-m-d 23:59:59');
} elseif ($filter_type === 'semanal') {
    // Weekly filter (last 7 days)
    $date_start = date('Y-m-d 00:00:00', strtotime('-6 days'));
    $date_end = date('Y-m-d 23:59:59');
} elseif ($filter_type === 'mensal') {
    // Monthly filter (current month)
    $date_start = date('Y-m-01 00:00:00');
    $date_end = date('Y-m-t 23:59:59');
} elseif ($filter_type === 'personalizado' && !empty($date_range_start) && !empty($date_range_end)) {
    // Custom date range filter
    $date_start = date('Y-m-d 00:00:00', strtotime($date_range_start));
    $date_end = date('Y-m-d 23:59:59', strtotime($date_range_end));
}

// Base SQL query
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
        LEFT JOIN lugares l ON l.id = m.id_lugar";

// Add date filter if applicable
if ($date_start && $date_end) {
    $sql .= " WHERE m.data_movimento BETWEEN :date_start AND :date_end";
}

// Add ordering
$sql .= " ORDER BY m.data_movimento DESC";

try {
    $stmt = $pdo->prepare($sql);

    // Bind date parameters if needed
    if ($date_start && $date_end) {
        $stmt->bindParam(':date_start', $date_start);
        $stmt->bindParam(':date_end', $date_end);
    }

    $stmt->execute();
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

// Include header
include_once __DIR__ . '/../includes/header.php';
?>

<div class="content">
    <h2 class="section-title">Relatório de Movimentações</h2>

    <div class="card mb-4">
        <div class="card-header">
            <h3 class="h5 mb-0">Filtros</h3>
        </div>
        <div class="card-body">
            <form method="get" action="" class="mb-0">
                <div class="form-row">
                    <div class="form-col">
                        <label class="form-label">Período:</label>
                        <div class="btn-group mb-3 filter-buttons">
                            <a href="?filter_type=todos" class="btn <?= $filter_type === 'todos' ? 'btn-primary' : 'btn-outline-primary' ?>">Todos</a>
                            <a href="?filter_type=diario" class="btn <?= $filter_type === 'diario' ? 'btn-primary' : 'btn-outline-primary' ?>">Diário</a>
                            <a href="?filter_type=semanal" class="btn <?= $filter_type === 'semanal' ? 'btn-primary' : 'btn-outline-primary' ?>">Semanal</a>
                            <a href="?filter_type=mensal" class="btn <?= $filter_type === 'mensal' ? 'btn-primary' : 'btn-outline-primary' ?>">Mensal</a>
                        </div>
                    </div>
                </div>

                <div class="form-row mt-3 <?= $filter_type === 'personalizado' ? '' : 'd-none' ?>" id="customDateFields">
                    <div class="form-col">
                        <label for="date_start" class="form-label">Data inicial:</label>
                        <input type="date" id="date_start" name="date_start" class="form-control" value="<?= $date_range_start ?>">
                    </div>
                    <div class="form-col">
                        <label for="date_end" class="form-label">Data final:</label>
                        <input type="date" id="date_end" name="date_end" class="form-control" value="<?= $date_range_end ?>">
                    </div>
                    <div class="form-col" style="align-self: flex-end;">
                        <input type="hidden" name="filter_type" value="personalizado">
                        <button type="submit" class="btn btn-primary">Filtrar</button>
                    </div>
                </div>
            </form>

            <div class="mt-3">
                <button id="toggleCustomDateFilter" class="btn btn-sm btn-outline-secondary">
                    <?= $filter_type === 'personalizado' ? 'Ocultar filtro por período' : 'Mostrar filtro por período' ?>
                </button>
            </div>
        </div>
    </div>

    <br></br>
    <div class="dashboard-cards">
        <div class="dashboard-card">
            <div><strong>Total de Movimentações: </strong><?= $total_movimentos ?></div>
        </div>
        <div class="dashboard-card">
            <div><strong>Entradas: </strong><?= $total_entradas ?></div>
            <!-- <div><strong>Quantidade: </strong> <?= $quantidade_entrada ?> itens</div> -->
        </div>
        <div class="dashboard-card">
            <div><strong>Saídas: </strong> <?= $total_saidas ?></div>
            <!-- <div><strong>Quantidade:</strong> <?= $quantidade_saida ?> itens</div> -->
        </div>
    </div>

    <br>
    <?php if ($filter_type !== 'todos'): ?>
    <div class="alert alert-info">
        <strong>Período selecionado:</strong>
        <?php if ($filter_type === 'diario'): ?>
            Hoje (<?= date('d/m/Y') ?>)
        <?php elseif ($filter_type === 'semanal'): ?>
            Últimos 7 dias (<?= date('d/m/Y', strtotime('-6 days')) ?> até <?= date('d/m/Y') ?>)
        <?php elseif ($filter_type === 'mensal'): ?>
            Mês atual (<?= date('d/m/Y', strtotime(date('Y-m-01'))) ?> até <?= date('d/m/Y', strtotime(date('Y-m-t'))) ?>)
        <?php elseif ($filter_type === 'personalizado'): ?>
            De <?= date('d/m/Y', strtotime($date_range_start)) ?> até <?= date('d/m/Y', strtotime($date_range_end)) ?>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <h3 class="mt-4">Lista de Movimentações</h3>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Data do Movimento</th>
                    <th>Produto</th>
                    <th>Pessoa</th>
                    <th>Local</th>
                    <th>Tipo</th>
                    <th>Quantidade</th>
                    <th>Observação</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($movimentos) > 0): ?>
                    <?php foreach ($movimentos as $mov): ?>
                    <tr>
                        <td><?= date("d/m/Y H:i", strtotime($mov['data_movimento'])) ?></td>
                        <td><?= htmlspecialchars($mov['produto']) ?></td>
                        <td><?= htmlspecialchars($mov['pessoa']) ?></td>
                        <td><?= htmlspecialchars($mov['lugar'] ?: 'Não especificado') ?></td>
                        <td>
                            <?php if ($mov['tipo'] == 'entrada'): ?>
                                <span class="text-success"><strong>Entrada</strong></span>
                            <?php else: ?>
                                <span class="text-danger"><strong>Saída</strong></span>
                            <?php endif; ?>
                        </td>
                        <td class="text-right"><?= $mov['quantidade'] ?></td>
                        <td><?= htmlspecialchars($mov['observacao'] ?: '') ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center">Nenhuma movimentação encontrada para o período selecionado.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="btn-group mt-4">
        <a href="relatorio_estoque.php" class="btn btn-outline-primary">Ver Estoque</a>
        <a href="produtos_por_local.php" class="btn btn-outline-primary">Produtos por Almoxarifado</a>
        <a href="../index.php" class="btn btn-secondary">Voltar para a Página Inicial</a>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggleBtn = document.getElementById('toggleCustomDateFilter');
        const customDateFields = document.getElementById('customDateFields');

        toggleBtn.addEventListener('click', function() {
            if (customDateFields.classList.contains('d-none')) {
                customDateFields.classList.remove('d-none');
                toggleBtn.textContent = 'Ocultar filtro por período';
                document.querySelector('input[name="filter_type"]').value = 'personalizado';
            } else {
                customDateFields.classList.add('d-none');
                toggleBtn.textContent = 'Mostrar filtro por período';
            }
        });
    });
</script>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
