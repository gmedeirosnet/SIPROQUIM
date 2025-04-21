<?php
// relatorios/movimentacao_por_pessoa.php
require_once __DIR__ . '/../config/db.php';

// Verificação de permissões
require_once __DIR__ . '/../auth/auth_check.php';
requirePermission(PERMISSION_READ, $current_user_grupo);

// Set page title for the header
// $pageTitle = 'Movimentação por Pessoa';

// Initialize variables
$selected_person = null;
$movimentos = [];
$search_term = '';
$pessoas = [];
$total_entrada = 0;
$total_saida = 0;
$total_movements = 0;
$produtos_movimentados = [];

// Process search query if submitted
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_term = $_GET['search'];

    // Search for pessoas matching the term (removendo referência à coluna documento)
    $stmt = $pdo->prepare("
        SELECT id, nome, email
        FROM pessoas
        WHERE nome LIKE :search OR email LIKE :search
        ORDER BY nome
    ");
    $stmt->execute(['search' => "%{$search_term}%"]);
    $pessoas = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// If a person is selected, get all their movements
if (isset($_GET['pessoa_id']) && !empty($_GET['pessoa_id'])) {
    $pessoa_id = (int) $_GET['pessoa_id'];

    // Get selected person details (removendo referência à coluna documento e tipo)
    $stmt = $pdo->prepare("
        SELECT p.id, p.nome, p.email, gp.nome AS grupo
        FROM pessoas p
        LEFT JOIN grupos_pessoas gp ON p.id_grupo_pessoa = gp.id
        WHERE p.id = :id
    ");
    $stmt->execute(['id' => $pessoa_id]);
    $selected_person = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($selected_person) {
        // Get all movements for this person
        $stmt = $pdo->prepare("
            SELECT m.id, m.tipo, m.quantidade, m.data_movimento, m.observacao,
                   p.nome AS produto, p.tipo AS produto_tipo,
                   p.volume AS produto_volume, p.unidade_medida,
                   l.nome AS lugar
            FROM movimentos m
            JOIN produtos p ON m.id_produto = p.id
            JOIN lugares l ON m.id_lugar = l.id
            WHERE m.id_pessoa = :id_pessoa
            ORDER BY m.data_movimento DESC
        ");

        $stmt->execute(['id_pessoa' => $pessoa_id]);
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

// Get all pessoas for direct selection if no search or selection
if (empty($pessoas) && empty($selected_person)) {
    $stmt = $pdo->query("SELECT id, nome FROM pessoas ORDER BY nome LIMIT 100");
    $pessoas = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Include header
include_once __DIR__ . '/../includes/header.php';
?>

<div class="content">
    <h2 class="section-title">Movimentação por Pessoa</h2>

    <div class="card mb-4">
        <div class="card-body">
            <form method="get" action="" class="mb-0">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Buscar pessoa por nome ou email..." value="<?= htmlspecialchars($search_term) ?>">
                    <button type="submit" class="btn btn-primary">Buscar</button>
                </div>
            </form>

            <br>
            <?php if (!empty($pessoas) && empty($selected_person)): ?>
                <div class="table-responsive mt-3">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Email</th>
                                <th>Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pessoas as $pessoa): ?>
                            <tr>
                                <td><?= htmlspecialchars($pessoa['nome']) ?></td>
                                <td><?= htmlspecialchars($pessoa['email'] ?? '-') ?></td>
                                <td>
                                    <a href="?pessoa_id=<?= $pessoa['id'] ?>&search=<?= urlencode($search_term) ?>" class="btn btn-sm btn-primary">
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

    <?php if ($selected_person): ?>
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h3 class="h5 mb-0"><?= htmlspecialchars($selected_person['nome']) ?></h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Email:</strong> <?= htmlspecialchars($selected_person['email'] ?? 'Não informado') ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Grupo:</strong> <?= htmlspecialchars($selected_person['grupo'] ?? 'Não especificado') ?></p>
                    </div>
                </div>
                <p><a href="?search=<?= urlencode($search_term) ?>" class="btn btn-sm btn-outline-primary">« Selecionar outra pessoa</a></p>
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
                            <th>Local</th>
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
                                <td><?= htmlspecialchars($movimento['lugar']) ?></td>
                                <td><?= htmlspecialchars($movimento['observacao'] ?: '-') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info mt-4">
                <p>Não há movimentações registradas para esta pessoa.</p>
            </div>
        <?php endif; ?>
    <?php elseif (isset($_GET['pessoa_id'])): ?>
        <div class="alert alert-danger mt-4">
            <p>Pessoa não encontrada.</p>
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