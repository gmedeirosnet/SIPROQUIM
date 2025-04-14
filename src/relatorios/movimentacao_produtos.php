<?php
// relatorios/movimentacao_produtos.php
require_once __DIR__ . '/../config/db.php';

// Set page title for the header
$pageTitle = 'Movimentação de Produtos';

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

// Include header
include_once __DIR__ . '/../includes/header.php';
?>

<div class="content">
    <h2 class="section-title">Movimentação de Produtos</h2>

    <div class="card mb-4">
        <div class="card-body">
            <form method="get" action="" class="mb-0">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Buscar produto por nome..." value="<?= htmlspecialchars($search_term) ?>">
                    <button type="submit" class="btn btn-primary">Buscar</button>
                </div>
            </form>

            <?php if (!empty($produtos) && empty($selected_product)): ?>
                <div class="table-responsive mt-3">
                    <table class="table table-hover">
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
                                    <a href="?produto_id=<?= $produto['id'] ?>&search=<?= urlencode($search_term) ?>" class="btn btn-sm btn-primary">
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

    <?php if ($selected_product): ?>
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h3 class="h5 mb-0"><?= htmlspecialchars($selected_product['nome']) ?></h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <p><strong>Grupo:</strong> <?= htmlspecialchars($selected_product['grupo'] ?? 'Sem grupo') ?></p>
                    </div>
                    <div class="col-md-4">
                        <p><strong>Fabricante:</strong> <?= htmlspecialchars($selected_product['fabricante'] ?? 'Não especificado') ?></p>
                    </div>
                    <div class="col-md-4">
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
                    </div>
                </div>
                <p><a href="?search=<?= urlencode($search_term) ?>" class="btn btn-sm btn-outline-primary">« Selecionar outro produto</a></p>
            </div>
        </div>

        <div class="dashboard-cards">
            <div class="dashboard-card">
                <div>Total de Movimentações</div>
                <div class="dashboard-number"><?= $total_movements ?></div>
            </div>
            <div class="dashboard-card">
                <div>Total de Entradas</div>
                <div class="dashboard-number text-success"><?= $total_entrada ?></div>
            </div>
            <div class="dashboard-card">
                <div>Total de Saídas</div>
                <div class="dashboard-number text-danger"><?= $total_saida ?></div>
            </div>
            <div class="dashboard-card">
                <div>Saldo Atual</div>
                <div class="dashboard-number <?= $saldo_atual >= 0 ? 'text-success' : 'text-danger' ?>"><?= $saldo_atual ?></div>
            </div>
        </div>

        <?php if (!empty($movimentos)): ?>
            <h3 class="mt-4">Movimentações do Produto</h3>
            <div class="table-responsive">
                <table class="table">
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
                                        <span class="text-success"><strong>Entrada</strong></span>
                                    <?php else: ?>
                                        <span class="text-danger"><strong>Saída</strong></span>
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
            </div>
        <?php else: ?>
            <div class="alert alert-info mt-4">
                <p>Não há movimentações registradas para este produto.</p>
            </div>
        <?php endif; ?>
    <?php elseif (isset($_GET['produto_id'])): ?>
        <div class="alert alert-danger mt-4">
            <p>Produto não encontrado.</p>
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