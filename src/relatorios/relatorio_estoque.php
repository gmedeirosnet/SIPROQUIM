<?php
// relatorios/relatorio_estoque.php
require_once __DIR__ . '/../config/db.php';

// Verificação de permissões (todos têm permissão de leitura)
require_once __DIR__ . '/../auth/auth_check.php';
requirePermission(PERMISSION_READ, $current_user_grupo);

// Set page title for the header
// $pageTitle = 'Relatório de Estoque';

// Inicializar variável de pesquisa
$search_produto = isset($_GET['search_produto']) ? trim($_GET['search_produto']) : '';

// Verificar se existe um filtro específico por ID do produto
$produto_id_filter = isset($_GET['produto_id']) ? intval($_GET['produto_id']) : null;

// Adicionar filtros de visualização
$view_type = isset($_GET['view_type']) ? $_GET['view_type'] : 'detalhado';

// Construir a consulta SQL baseada no tipo de visualização
if ($view_type === 'produto') {
    // Saldo por Produto (agrupado por produto)
    $sql_base = "SELECT
                p.id as produto_id,
                p.nome as produto,
                g.nome as grupo,
                COALESCE(SUM(CASE
                    WHEN m.tipo = 'entrada' THEN m.quantidade
                    WHEN m.tipo = 'saida' THEN -m.quantidade
                    ELSE 0
                END), 0) as saldo
            FROM produtos p
            LEFT JOIN grupos g ON p.id_grupo = g.id
            LEFT JOIN movimentos m ON p.id = m.id_produto";
} elseif ($view_type === 'almoxarifado') {
    // Saldo por Almoxarifado (agrupado por local)
    $sql_base = "SELECT
                l.id as lugar_id,
                l.nome as lugar,
                COALESCE(SUM(CASE
                    WHEN m.tipo = 'entrada' THEN m.quantidade
                    WHEN m.tipo = 'saida' THEN -m.quantidade
                    ELSE 0
                END), 0) as saldo,
                COUNT(DISTINCT p.id) as total_produtos
            FROM lugares l
            LEFT JOIN movimentos m ON l.id = m.id_lugar
            LEFT JOIN produtos p ON m.id_produto = p.id";
} else {
    // Visualização detalhada (padrão)
    $sql_base = "SELECT
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
            LEFT JOIN lugares l ON m.id_lugar = l.id";
}

// Adicionar cláusula WHERE para filtrar por nome do produto ou ID do produto
$where_clause = "";
$params = [];

if (!empty($search_produto)) {
    $where_clause = " WHERE p.nome LIKE :search_produto";
    $params[':search_produto'] = "%{$search_produto}%";
} elseif ($produto_id_filter) {
    $where_clause = " WHERE p.id = :produto_id";
    $params[':produto_id'] = $produto_id_filter;
}

// Completar a consulta SQL baseada no tipo de visualização
if ($view_type === 'produto') {
    $sql = $sql_base . $where_clause . " GROUP BY p.id, p.nome, g.nome ORDER BY p.nome";
} elseif ($view_type === 'almoxarifado') {
    $sql = $sql_base . $where_clause . " GROUP BY l.id, l.nome ORDER BY l.nome";
} else {
    $sql = $sql_base . $where_clause . " GROUP BY p.id, p.nome, g.nome, l.nome ORDER BY p.nome, l.nome";
}

try {
    $stmt = $pdo->prepare($sql);

    // Vincular parâmetros, se houver
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }

    $stmt->execute();
    $estoques = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Erro ao gerar relatório: " . $e->getMessage();
    exit;
}

// Se estamos filtrando um produto específico, vamos buscar o nome dele para exibir
$produto_nome_filtrado = '';
if ($produto_id_filter && count($estoques) > 0) {
    $produto_nome_filtrado = $estoques[0]['produto'];
}

// Calcular totais baseado no tipo de visualização
$total_produtos = 0;
$total_itens = 0;
$produtos_por_grupo = [];
$produtos_computados = [];

if ($view_type === 'produto') {
    // Para visualização por produto
    $total_produtos = count($estoques);
    foreach ($estoques as $estoque) {
        $total_itens += $estoque['saldo'];
        if (!empty($estoque['grupo'])) {
            if (!isset($produtos_por_grupo[$estoque['grupo']])) {
                $produtos_por_grupo[$estoque['grupo']] = 0;
            }
            $produtos_por_grupo[$estoque['grupo']]++;
        }
    }
} elseif ($view_type === 'almoxarifado') {
    // Para visualização por almoxarifado
    $total_almoxarifados = count($estoques);
    foreach ($estoques as $estoque) {
        $total_itens += $estoque['saldo'];
        $total_produtos += $estoque['total_produtos'];
    }
} else {
    // Para visualização detalhada (padrão)
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
            if (!isset($produtos_computados[$estoque['produto_id'] . '_grupo'])) {
                $produtos_por_grupo[$estoque['grupo']]++;
                $produtos_computados[$estoque['produto_id'] . '_grupo'] = true;
            }
        }
    }
}

// Include header
include_once __DIR__ . '/../includes/header.php';
?>

<div class="content">
    <h2 class="section-title">Relatório de Estoque</h2>

    <!-- Formulário de pesquisa de produtos -->
    <div class="card mb-4">
        <div class="card-header">
            <h5>Pesquisar Produtos</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="" class="form-inline">
                <div class="input-group mb-2">
                    <input type="text" name="search_produto" class="form-control"
                           placeholder="Digite o nome do produto..."
                           value="<?= htmlspecialchars($search_produto) ?>">
                    <input type="hidden" name="view_type" value="<?= htmlspecialchars($view_type) ?>">
                    <div class="input-group-append">
                        <button type="submit" class="btn btn-primary">Pesquisar</button>
                    </div>
                </div>
                <?php if (!empty($search_produto) || $produto_id_filter): ?>
                    <a href="relatorio_estoque.php?view_type=<?= htmlspecialchars($view_type) ?>" class="btn btn-secondary btn-sm">Limpar Pesquisa</a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Filtros de Visualização -->
    <div class="card mb-4">
        <div class="card-header">
            <h5>Tipo de Visualização</h5>
        </div>
        <div class="card-body">
            <div class="btn-group" role="group">
                <a href="?view_type=detalhado<?= !empty($search_produto) ? '&search_produto=' . urlencode($search_produto) : '' ?><?= $produto_id_filter ? '&produto_id=' . $produto_id_filter : '' ?>"
                   class="btn <?= $view_type === 'detalhado' ? 'btn-primary' : 'btn-outline-primary' ?>">
                    Saldo Detalhado
                </a>
                <a href="?view_type=produto<?= !empty($search_produto) ? '&search_produto=' . urlencode($search_produto) : '' ?><?= $produto_id_filter ? '&produto_id=' . $produto_id_filter : '' ?>"
                   class="btn <?= $view_type === 'produto' ? 'btn-primary' : 'btn-outline-primary' ?>">
                    Saldo por Produto
                </a>
                <a href="?view_type=almoxarifado<?= !empty($search_produto) ? '&search_produto=' . urlencode($search_produto) : '' ?><?= $produto_id_filter ? '&produto_id=' . $produto_id_filter : '' ?>"
                   class="btn <?= $view_type === 'almoxarifado' ? 'btn-primary' : 'btn-outline-primary' ?>">
                    Saldo por Almoxarifado
                </a>
            </div>
        </div>
    </div>

    <?php if (!empty($search_produto)): ?>
        <div class="alert alert-info">
            <strong>Filtrando por:</strong> produtos contendo "<?= htmlspecialchars($search_produto) ?>"
            (<?= count($estoques) ?> resultados encontrados)
        </div>
    <?php elseif ($produto_id_filter): ?>
        <div class="alert alert-info">
            <strong>Filtrando pelo produto:</strong> <?= htmlspecialchars($produto_nome_filtrado) ?>
        </div>
    <?php endif; ?>

    <br>
    <div class="dashboard-cards">
        <?php if ($view_type === 'almoxarifado'): ?>
            <div class="dashboard-card">
                <div>Total de Almoxarifados: <strong><?= count($estoques) ?></strong></div>
            </div>
            <div class="dashboard-card">
                <div>Total de Produtos: <strong><?= $total_produtos ?></strong></div>
            </div>
        <?php else: ?>
            <div class="dashboard-card">
                <div>Total de Produtos: <strong><?= $total_produtos ?></strong></div>
            </div>
        <?php endif; ?>
        <div class="dashboard-card">
            <div>Total de Itens em Estoque: <strong><?= $total_itens ?></strong></div>
        </div>
    </div>

    <br>
    <?php if (count($produtos_por_grupo) > 0): ?>
    <h3 class="mt-4">Produtos por Grupo</h3>
    <div class="table-responsive">
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

    <h3 class="mt-4">
        <?php if ($view_type === 'produto'): ?>
            Saldo Total por Produto
        <?php elseif ($view_type === 'almoxarifado'): ?>
            Saldo Total por Almoxarifado
        <?php else: ?>
            Saldo por Produto e Almoxarifado
        <?php endif; ?>
    </h3>
    <?php if (count($estoques) > 0): ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <?php if ($view_type === 'produto'): ?>
                            <th>Produto</th>
                            <th>Grupo</th>
                            <th class="text-right">Saldo Total</th>
                        <?php elseif ($view_type === 'almoxarifado'): ?>
                            <th>Almoxarifado</th>
                            <th class="text-center">Total de Produtos</th>
                            <th class="text-right">Saldo Total</th>
                        <?php else: ?>
                            <th>Produto</th>
                            <th>Grupo</th>
                            <th>Almoxarifado</th>
                            <th class="text-right">Saldo</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($estoques as $estoque): ?>
                    <tr<?= $estoque['saldo'] < 5 ? ' class="table-danger"' : '' ?>>
                        <?php if ($view_type === 'produto'): ?>
                            <td>
                                <a href="movimentacao_produtos.php?produto_id=<?= $estoque['produto_id'] ?>" class="produto-link">
                                    <?= $estoque['produto'] ?>
                                </a>
                            </td>
                            <td><?= $estoque['grupo'] ?: 'Sem grupo' ?></td>
                            <td class="text-right"><?= $estoque['saldo'] ?></td>
                        <?php elseif ($view_type === 'almoxarifado'): ?>
                            <td>
                                <a href="produtos_por_local.php?lugar_id=<?= $estoque['lugar_id'] ?>" class="produto-link">
                                    <?= $estoque['lugar'] ?: 'Não especificado' ?>
                                </a>
                            </td>
                            <td class="text-center"><?= $estoque['total_produtos'] ?: 0 ?></td>
                            <td class="text-right"><?= $estoque['saldo'] ?></td>
                        <?php else: ?>
                            <td>
                                <a href="movimentacao_produtos.php?produto_id=<?= $estoque['produto_id'] ?>" class="produto-link">
                                    <?= $estoque['produto'] ?>
                                </a>
                            </td>
                            <td><?= $estoque['grupo'] ?: 'Sem grupo' ?></td>
                            <td><?= $estoque['lugar'] ?: 'Não especificado' ?></td>
                            <td class="text-right"><?= $estoque['saldo'] ?></td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-warning">
            Nenhum produto encontrado com os critérios de pesquisa.
        </div>
    <?php endif; ?>

    <div class="btn-group mt-4">
        <a href="relatorio_movimentos.php" class="btn btn-outline-primary">Ver Movimentações</a>
        <a href="produtos_por_local.php" class="btn btn-outline-primary">Produtos por Almoxarifado</a>
        <a href="../index.php" class="btn btn-secondary">Voltar para a Página Inicial</a>
    </div>
</div>

<style>
    .produto-link {
        color: #007bff;
        text-decoration: none;
    }
    .produto-link:hover {
        text-decoration: underline;
        color: #0056b3;
    }
</style>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
