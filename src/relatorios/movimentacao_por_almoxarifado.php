<?php
// relatorios/movimentacao_por_almoxarifado.php
require_once __DIR__ . '/../config/db.php';

// Verificação de permissões
require_once __DIR__ . '/../auth/auth_check.php';
requirePermission(PERMISSION_READ, $current_user_grupo);

// Set page title for the header
// $pageTitle = 'Movimentação por Almoxarifado';

// Initialize variables
$selected_lugar = null;
$movimentos = [];
$search_term = '';
$lugares = [];
$total_entrada = 0;
$total_saida = 0;
$total_movements = 0;
$produtos_movimentados = [];

// Process search query if submitted
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_term = $_GET['search'];

    // Search for lugares matching the term
    $stmt = $pdo->prepare("
        SELECT id, nome, descricao
        FROM lugares
        WHERE nome LIKE :search
        ORDER BY nome
    ");
    $stmt->execute(['search' => "%{$search_term}%"]);
    $lugares = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// If a lugar is selected, get all its movements
if (isset($_GET['lugar_id']) && !empty($_GET['lugar_id'])) {
    $lugar_id = (int) $_GET['lugar_id'];

    // Get selected lugar details
    $stmt = $pdo->prepare("
        SELECT id, nome, descricao
        FROM lugares
        WHERE id = :id
    ");
    $stmt->execute(['id' => $lugar_id]);
    $selected_lugar = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($selected_lugar) {
        // Get all movements for this lugar
        $stmt = $pdo->prepare("
            SELECT m.id, m.tipo, m.quantidade, m.data_movimento, m.observacao,
                   p.nome AS produto, p.tipo AS produto_tipo,
                   p.volume AS produto_volume, p.unidade_medida,
                   pe.nome AS pessoa
            FROM movimentos m
            JOIN produtos p ON m.id_produto = p.id
            JOIN pessoas pe ON m.id_pessoa = pe.id
            WHERE m.id_lugar = :id_lugar
            ORDER BY m.data_movimento DESC
        ");

        $stmt->execute(['id_lugar' => $lugar_id]);
        $movimentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $total_movements = count($movimentos);

        // Calculate totals and track unique products
        foreach ($movimentos as $movimento) {
            if ($movimento['tipo'] == 'entrada') {
                $total_entrada += $movimento['quantidade'];
            } else {
                $total_saida += $movimento['quantidade'];
            }

            // Track unique products movimentados
            if (!isset($produtos_movimentados[$movimento['produto']])) {
                $produtos_movimentados[$movimento['produto']] = [
                    'nome' => $movimento['produto'],
                    'entradas' => 0,
                    'saidas' => 0
                ];
            }

            if ($movimento['tipo'] == 'entrada') {
                $produtos_movimentados[$movimento['produto']]['entradas'] += $movimento['quantidade'];
            } else {
                $produtos_movimentados[$movimento['produto']]['saidas'] += $movimento['quantidade'];
            }
        }
    }
}

// Get all lugares for direct selection if no search or selection
if (empty($lugares) && empty($selected_lugar)) {
    $stmt = $pdo->query("SELECT id, nome, descricao FROM lugares ORDER BY nome LIMIT 100");
    $lugares = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Include header
include_once __DIR__ . '/../includes/header.php';
?>

<div class="content">
    <h2 class="section-title">Movimentação por Almoxarifado</h2>

    <div class="card mb-4">
        <div class="card-body">
            <form method="get" action="" class="mb-0">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Buscar almoxarifado por nome..." value="<?= htmlspecialchars($search_term) ?>">
                    <button type="submit" class="btn btn-primary">Buscar</button>
                </div>
            </form>

            <br>
            <?php if (!empty($lugares) && empty($selected_lugar)): ?>
                <div class="table-responsive mt-3">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Almoxarifado</th>
                                <th>Descrição</th>
                                <th>Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($lugares as $lugar): ?>
                            <tr>
                                <td><?= htmlspecialchars($lugar['nome']) ?></td>
                                <td><?= htmlspecialchars($lugar['descricao'] ?? '-') ?></td>
                                <td>
                                    <a href="?lugar_id=<?= $lugar['id'] ?>&search=<?= urlencode($search_term) ?>" class="btn btn-sm btn-primary">
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
    </div>

    <?php if ($selected_lugar): ?>
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h3 class="h5 mb-0"><?= htmlspecialchars($selected_lugar['nome']) ?></h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Descrição:</strong> <?= htmlspecialchars($selected_lugar['descricao'] ?? 'Não informado') ?></p>
                    </div>
                </div>
                <p><a href="?search=<?= urlencode($search_term) ?>" class="btn btn-sm btn-outline-primary">« Selecionar outro almoxarifado</a></p>
            </div>
        </div>

        <br>
        <div class="dashboard-cards">
            <div class="dashboard-card">
                <div>Total de Movimentações: <strong><?= $total_movements ?></strong></div>
            </div>
            <div class="dashboard-card">
                <div>Total de Entradas: <strong><?= $total_entrada ?></strong></div>
            </div>
            <div class="dashboard-card">
                <div>Total de Saídas: <strong><?= $total_saida ?></strong></div>
            </div>
            <div class="dashboard-card">
                <div>Produtos Movimentados: <strong><?= count($produtos_movimentados) ?></strong></div>
            </div>
        </div>

        <?php if (!empty($produtos_movimentados)): ?>
            <div class="card mt-4">
                <div class="card-header">
                    <h3 class="h5 mb-0">Resumo por Produto</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Produto</th>
                                    <th class="text-center">Entradas</th>
                                    <th class="text-center">Saídas</th>
                                    <th class="text-center">Saldo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($produtos_movimentados as $produto): ?>
                                <tr>
                                    <td><?= htmlspecialchars($produto['nome']) ?></td>
                                    <td class="text-center text-success"><?= $produto['entradas'] ?></td>
                                    <td class="text-center text-danger"><?= $produto['saidas'] ?></td>
                                    <td class="text-center <?= ($produto['entradas'] - $produto['saidas'] >= 0) ? 'text-success' : 'text-danger' ?>">
                                        <strong><?= $produto['entradas'] - $produto['saidas'] ?></strong>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <br>
        <?php if (!empty($movimentos)): ?>
            <h3 class="mt-4">Histórico de Movimentações</h3>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Tipo</th>
                            <th>Produto</th>
                            <th>Quantidade</th>
                            <th>Pessoa</th>
                            <th>Observação</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($movimentos as $movimento): ?>
                            <tr>
                                <td><?= date('d/m/Y H:i', strtotime($movimento['data_movimento'])) ?></td>
                                <td>
                                    <?php if ($movimento['tipo'] == 'entrada'): ?>
                                        <span class="text-success"><strong>Entrada</strong></span>
                                    <?php else: ?>
                                        <span class="text-danger"><strong>Saída</strong></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($movimento['produto']) ?>
                                    <?php if (!empty($movimento['produto_tipo']) || !empty($movimento['produto_volume'])): ?>
                                        <small class="text-muted">
                                            (<?= htmlspecialchars($movimento['produto_tipo'] ?? '') ?>
                                            <?php if (!empty($movimento['produto_volume'])): ?>
                                                <?= htmlspecialchars($movimento['produto_volume']) ?>
                                                <?= htmlspecialchars($movimento['unidade_medida'] ?? '') ?>
                                            <?php endif; ?>)
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td class="text-right"><?= $movimento['quantidade'] ?></td>
                                <td><?= htmlspecialchars($movimento['pessoa']) ?></td>
                                <td><?= htmlspecialchars($movimento['observacao'] ?: '-') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info mt-4">
                <p>Não há movimentações registradas para este almoxarifado.</p>
            </div>
        <?php endif; ?>
    <?php elseif (isset($_GET['lugar_id'])): ?>
        <div class="alert alert-danger mt-4">
            <p>Almoxarifado não encontrado.</p>
        </div>
    <?php endif; ?>

    <div class="btn-group mt-4">
        <a href="relatorio_estoque.php" class="btn btn-outline-primary">Ver Estoque</a>
        <a href="relatorio_movimentos.php" class="btn btn-outline-primary">Ver Movimentações</a>
        <a href="produtos_por_local.php" class="btn btn-outline-primary">Produtos por Almoxarifado</a>
        <a href="../index.php" class="btn btn-secondary">Voltar para a Página Inicial</a>
    </div>
</div>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>