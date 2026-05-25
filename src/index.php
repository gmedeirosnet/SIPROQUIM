<?php
// index.php
require_once __DIR__ . '/config/db.php';

$pageTitle = 'Dashboard';

// Fetch limited number of records for each entity
function fetchLimit($pdo, $table, $limit = 5, $orderBy = 'id DESC') {
    $stmt = $pdo->query("SELECT * FROM $table ORDER BY $orderBy LIMIT $limit");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get recent data
$pessoas = fetchLimit($pdo, 'pessoas');
$grupos = fetchLimit($pdo, 'grupos');
$produtos = fetchLimit($pdo, 'produtos');
$lugares = fetchLimit($pdo, 'lugares');
$grupos_pessoas = fetchLimit($pdo, 'grupos_pessoas');
$fabricantes = fetchLimit($pdo, 'fabricantes');

// For movimentos we need to join with related tables
$stmt = $pdo->query("
    SELECT m.id, p.nome AS produto, pe.nome AS pessoa, l.nome AS lugar,
           m.tipo, m.quantidade, m.data_movimento
    FROM movimentos m
    JOIN produtos p ON m.id_produto = p.id
    JOIN pessoas pe ON m.id_pessoa = pe.id
    JOIN lugares l ON m.id_lugar = l.id
    ORDER BY m.data_movimento DESC
    LIMIT 8
");
$movimentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get inventory by location
$stmt = $pdo->query("
    SELECT
        l.id as lugar_id,
        l.nome as lugar,
        p.id as produto_id,
        p.nome as produto,
        g.nome as grupo,
        COALESCE(SUM(CASE WHEN m.tipo = 'entrada' THEN m.quantidade ELSE -m.quantidade END), 0) as saldo
    FROM lugares l
    LEFT JOIN movimentos m ON l.id = m.id_lugar
    LEFT JOIN produtos p ON m.id_produto = p.id
    LEFT JOIN grupos g ON p.id_grupo = g.id
    GROUP BY l.id, l.nome, p.id, p.nome, g.nome
    HAVING COALESCE(SUM(CASE WHEN m.tipo = 'entrada' THEN m.quantidade ELSE -m.quantidade END), 0) > 0
    ORDER BY l.nome, p.nome
");
$produtos_por_lugar = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $lugar_id = $row['lugar_id'];
    if (!isset($produtos_por_lugar[$lugar_id])) {
        $produtos_por_lugar[$lugar_id] = [
            'nome' => $row['lugar'],
            'produtos' => []
        ];
    }
    $produtos_por_lugar[$lugar_id]['produtos'][] = $row;
}

// Count total items
$total_produtos = $pdo->query("SELECT COUNT(*) FROM produtos")->fetchColumn();
$total_movimentos = $pdo->query("SELECT COUNT(*) FROM movimentos")->fetchColumn();
$total_pessoas = $pdo->query("SELECT COUNT(*) FROM pessoas")->fetchColumn();
$total_lugares = $pdo->query("SELECT COUNT(*) FROM lugares")->fetchColumn();

// Get low stock items
$stmt = $pdo->query("
    SELECT
        p.id,
        p.nome as produto,
        g.nome as grupo,
        l.nome as lugar,
        COALESCE(SUM(CASE WHEN m.tipo = 'entrada' THEN m.quantidade ELSE -m.quantidade END), 0) as saldo
    FROM produtos p
    LEFT JOIN grupos g ON p.id_grupo = g.id
    LEFT JOIN movimentos m ON p.id = m.id_produto
    LEFT JOIN lugares l ON m.id_lugar = l.id
    GROUP BY p.id, p.nome, g.nome, l.nome
    HAVING COALESCE(SUM(CASE WHEN m.tipo = 'entrada' THEN m.quantidade ELSE -m.quantidade END), 0) < 5
    AND COALESCE(SUM(CASE WHEN m.tipo = 'entrada' THEN m.quantidade ELSE -m.quantidade END), 0) > 0
    ORDER BY saldo ASC
    LIMIT 5
");
$baixo_estoque = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Include the header template
include_once __DIR__ . '/includes/header.php';
?>
<div class="content">
    <div class="welcome-message">
        <h1><i class="fas fa-home"></i> Bem-vindo ao SIPROQUIM</h1>
        <p><i class="fas fa-info-circle"></i> Sistema de controle e gerenciamento de produtos químicos.</p>
    </div>

    <!-- Summary Statistics -->
    <div class="stats-grid">
        <div class="stat-card">
            <i class="fas fa-flask" style="font-size: 2rem; color: var(--primary-light); margin-bottom: var(--space-3);"></i>
            <div class="stat-value"><?= $total_produtos ?></div>
            <div class="stat-label">Produtos Cadastrados</div>
            <div class="stat-action"><a href="cadastros/list_produtos.php" class="btn btn-sm btn-outline-primary">Ver todos</a></div>
        </div>
        <div class="stat-card">
            <i class="fas fa-users" style="font-size: 2rem; color: var(--primary-light); margin-bottom: var(--space-3);"></i>
            <div class="stat-value"><?= $total_pessoas ?></div>
            <div class="stat-label">Pessoas Registradas</div>
            <div class="stat-action"><a href="cadastros/list_pessoas.php" class="btn btn-sm btn-outline-primary">Ver todos</a></div>
        </div>
        <div class="stat-card">
            <i class="fas fa-warehouse" style="font-size: 2rem; color: var(--primary-light); margin-bottom: var(--space-3);"></i>
            <div class="stat-value"><?= $total_lugares ?></div>
            <div class="stat-label">Almoxarifados Ativos</div>
            <div class="stat-action"><a href="cadastros/list_lugares.php" class="btn btn-sm btn-outline-primary">Ver todos</a></div>
        </div>
        <div class="stat-card">
            <i class="fas fa-exchange-alt" style="font-size: 2rem; color: var(--primary-light); margin-bottom: var(--space-3);"></i>
            <div class="stat-value"><?= $total_movimentos ?></div>
            <div class="stat-label">Total de Movimentações</div>
            <div class="stat-action"><a href="relatorios/relatorio_movimentos.php" class="btn btn-sm btn-outline-primary">Ver todos</a></div>
        </div>
    </div>

    <!-- Quick Actions -->
    <section class="widget">
        <div class="widget-header">
            <h3 class="widget-title"><i class="fas fa-bolt"></i> Ações Rápidas</h3>
        </div>
        <div class="card-grid">
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-arrow-down"></i> Nova Entrada</h3>
                </div>
                <div class="card-body">
                    <p>Registrar entrada de produtos no estoque</p>
                </div>
                <div class="card-footer">
                    <a href="cadastros/movimento.php" class="btn btn-success"><i class="fas fa-plus"></i> Registrar</a>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-arrow-up"></i> Nova Saída</h3>
                </div>
                <div class="card-body">
                    <p>Registrar saída de produtos do estoque</p>
                </div>
                <div class="card-footer">
                    <a href="cadastros/movimento.php" class="btn btn-danger"><i class="fas fa-minus"></i> Registrar</a>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-flask"></i> Novo Produto</h3>
                </div>
                <div class="card-body">
                    <p>Cadastrar um novo produto no sistema</p>
                </div>
                <div class="card-footer">
                    <a href="cadastros/produto.php" class="btn btn-primary"><i class="fas fa-plus-circle"></i> Cadastrar</a>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-chart-bar"></i> Relatório</h3>
                </div>
                <div class="card-body">
                    <p>Visualizar relatório de estoque atual</p>
                </div>
                <div class="card-footer">
                    <a href="relatorios/relatorio_estoque.php" class="btn btn-primary"><i class="fas fa-eye"></i> Visualizar</a>
                </div>
            </div>
        </div>
    </section>

    <div class="row">
        <!-- Recent Movements -->
        <div class="widget" style="flex: 2;">
            <div class="widget-header">
                <h3 class="widget-title"><i class="fas fa-history"></i> Últimas Movimentações</h3>
                <a href="relatorios/relatorio_movimentos.php" class="btn btn-sm btn-outline-primary"><i class="fas fa-list"></i> Ver Todos</a>
            </div>

            <?php if (!empty($movimentos)): ?>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Produto</th>
                                <th>Tipo</th>
                                <th>Qtd</th>
                                <th>Local</th>
                                <th>Data</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($movimentos as $movimento): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($movimento['produto']) ?></strong><br>
                                    <small class="text-muted"><?= htmlspecialchars($movimento['pessoa']) ?></small>
                                </td>
                                <td>
                                    <?php if ($movimento['tipo'] == 'entrada'): ?>
                                        <span class="badge badge-success">Entrada</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Saída</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($movimento['quantidade']) ?></td>
                                <td><?= htmlspecialchars($movimento['lugar']) ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($movimento['data_movimento'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <p>Nenhuma movimentação registrada</p>
                    <a href="cadastros/movimento.php" class="btn btn-primary mt-2">Registrar Movimentação</a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Low Stock Alert -->
        <div class="widget" style="flex: 1;">
            <div class="widget-header">
                <h3 class="widget-title"><i class="fas fa-exclamation-triangle"></i> Estoque Baixo</h3>
                <a href="relatorios/relatorio_estoque.php" class="btn btn-sm btn-outline-primary"><i class="fas fa-boxes"></i> Ver Estoque</a>
            </div>

            <?php if (!empty($baixo_estoque)): ?>
                <div class="alert alert-warning mb-3">
                    <i class="fas fa-exclamation-circle"></i> <strong>Atenção!</strong> Produtos com estoque abaixo de 5 unidades.
                </div>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Produto</th>
                                <th>Local</th>
                                <th>Qtd</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($baixo_estoque as $item): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($item['produto']) ?></strong>
                                    <?php if (!empty($item['grupo'])): ?>
                                    <br><small class="text-muted"><?= htmlspecialchars($item['grupo']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($item['lugar'] ?? 'N/A') ?></td>
                                <td>
                                    <span class="badge badge-warning"><?= $item['saldo'] ?></span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-success">
                    <p>Todos os produtos estão com estoque adequado.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Recent Products -->
    <div class="widget">
        <div class="widget-header">
            <h3 class="widget-title"><i class="fas fa-flask"></i> Produtos Recentes</h3>
            <a href="cadastros/list_produtos.php" class="btn btn-sm btn-outline-primary"><i class="fas fa-list"></i> Ver Todos</a>
        </div>

        <?php if (!empty($produtos)): ?>
            <div class="card-grid">
                <?php foreach ($produtos as $produto): ?>
                <div class="card">
                    <div class="card-header">
                        <h3><?= htmlspecialchars($produto['nome']) ?></h3>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($produto['tipo'])): ?>
                            <p><strong>Tipo:</strong> <?= htmlspecialchars($produto['tipo']) ?></p>
                        <?php endif; ?>

                        <?php if (!empty($produto['preco'])): ?>
                            <p><strong>Preço:</strong> R$ <?= number_format($produto['preco'], 2, ',', '.') ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer">
                        <div class="btn-group">
                            <a href="cadastros/produto.php?id=<?= $produto['id'] ?>" class="btn btn-sm btn-outline-primary">Editar</a>
                            <a href="relatorios/movimentacao_produtos.php?produto_id=<?= $produto['id'] ?>" class="btn btn-sm btn-outline-primary">Movimentos</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info text-center">
                <p>Nenhum produto cadastrado</p>
                <a href="cadastros/produto.php" class="btn btn-primary mt-2">Cadastrar Produto</a>
            </div>
        <?php endif; ?>
    </div>

    <div class="section">
        <div class="section-header">
            <h3><i class="fas fa-shield-alt"></i> Suas Permissões</h3>
        </div>
        <div class="permission-info">
            <?php if (isset($current_user_permissions)): ?>
                <div class="permission-card <?= $current_user_permissions['create'] ? 'permission-enabled' : 'permission-disabled' ?>">
                    <i class="fas fa-<?= $current_user_permissions['create'] ? 'check-circle' : 'times-circle' ?>" style="font-size: 2.5rem;"></i>
                    <h4><i class="fas fa-plus"></i> Criar</h4>
                    <p><?= $current_user_permissions['create'] ? 'Você pode criar novos registros' : 'Você não pode criar novos registros' ?></p>
                </div>

                <div class="permission-card <?= $current_user_permissions['read'] ? 'permission-enabled' : 'permission-disabled' ?>">
                    <i class="fas fa-<?= $current_user_permissions['read'] ? 'check-circle' : 'times-circle' ?>" style="font-size: 2.5rem;"></i>
                    <h4><i class="fas fa-eye"></i> Ler</h4>
                    <p><?= $current_user_permissions['read'] ? 'Você pode visualizar registros' : 'Você não pode visualizar registros' ?></p>
                </div>

                <div class="permission-card <?= $current_user_permissions['update'] ? 'permission-enabled' : 'permission-disabled' ?>">
                    <i class="fas fa-<?= $current_user_permissions['update'] ? 'check-circle' : 'times-circle' ?>" style="font-size: 2.5rem;"></i>
                    <h4><i class="fas fa-edit"></i> Atualizar</h4>
                    <p><?= $current_user_permissions['update'] ? 'Você pode editar registros existentes' : 'Você não pode editar registros existentes' ?></p>
                </div>

                <div class="permission-card <?= $current_user_permissions['delete'] ? 'permission-enabled' : 'permission-disabled' ?>">
                    <i class="fas fa-<?= $current_user_permissions['delete'] ? 'check-circle' : 'times-circle' ?>" style="font-size: 2.5rem;"></i>
                    <h4><i class="fas fa-trash-alt"></i> Excluir</h4>
                    <p><?= $current_user_permissions['delete'] ? 'Você pode excluir registros' : 'Você não pode excluir registros' ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <style>
        .section {
            margin-bottom: var(--space-8);
        }

        .section-header h3 {
            font-size: 1.5rem;
            font-weight: var(--font-weight-bold);
            margin-bottom: var(--space-6);
            color: var(--text-primary);
        }

        .permission-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: var(--space-5);
            margin: var(--space-6) 0;
        }

        .permission-card {
            padding: var(--space-6);
            border-radius: var(--radius-lg);
            text-align: center;
            transition: var(--transition-slow);
            box-shadow: var(--shadow-sm);
        }

        .permission-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .permission-card h4 {
            margin-top: var(--space-3);
            margin-bottom: var(--space-2);
            font-size: 1.125rem;
            font-weight: var(--font-weight-semibold);
        }

        .permission-card p {
            color: var(--text-secondary);
            font-size: 0.9375rem;
            margin: 0;
        }

        .permission-enabled {
            background-color: rgba(16, 185, 129, 0.08);
            border: 2px solid rgba(16, 185, 129, 0.3);
        }

        .permission-enabled i:first-child {
            color: var(--success);
        }

        .permission-disabled {
            background-color: rgba(239, 68, 68, 0.05);
            border: 2px solid rgba(239, 68, 68, 0.2);
        }

        .permission-disabled i:first-child {
            color: var(--danger);
        }
    </style>
</div>

<style>
/* Additional page-specific styles */
.welcome-message {
    background-color: var(--light-gray);
    padding: 20px;
    border-radius: var(--radius);
    margin-bottom: 20px;
    border-left: 4px solid var(--primary);
}

.welcome-message h1 {
    margin: 0;
    color: var(--primary);
    font-size: 1.8rem;
}

.welcome-message p {
    margin-top: 5px;
    color: var(--text-secondary);
}

.row {
    display: flex;
    gap: 20px;
    margin-bottom: 20px;
}

.text-muted {
    color: var(--text-secondary);
    font-size: 0.85em;
}

.stat-action {
    margin-top: 10px;
}

@media (max-width: 768px) {
    .row {
        flex-direction: column;
    }
}
</style>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
