<?php
// cadastros/list_produtos.php
require_once __DIR__ . '/../config/db.php';

// Verificação de permissões
require_once __DIR__ . '/../auth/auth_check.php';
requirePermission(PERMISSION_READ, $current_user_grupo);

// Set page title
// $pageTitle = 'Lista de Produtos';

// Pagination setup
$per_page = 25;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $per_page;

// Get total count for pagination
$stmt_count = $pdo->query("SELECT COUNT(*) FROM produtos");
$total_records = $stmt_count->fetchColumn();
$total_pages = ceil($total_records / $per_page);

// Search functionality
$search = isset($_GET['search']) ? $_GET['search'] : '';
$where_clause = '';
$params = [];

if (!empty($search)) {
    $where_clause = "WHERE p.nome LIKE :search OR f.nome LIKE :search OR p.tipo LIKE :search";
    $params[':search'] = "%{$search}%";
}

// Filter by group
$filter_grupo = isset($_GET['grupo']) ? (int)$_GET['grupo'] : 0;
if ($filter_grupo > 0) {
    if (empty($where_clause)) {
        $where_clause = "WHERE p.id_grupo = :grupo";
    } else {
        $where_clause .= " AND p.id_grupo = :grupo";
    }
    $params[':grupo'] = $filter_grupo;
}

// Filter by fabricante
$filter_fabricante = isset($_GET['fabricante']) ? (int)$_GET['fabricante'] : 0;
if ($filter_fabricante > 0) {
    if (empty($where_clause)) {
        $where_clause = "WHERE p.id_fabricante = :fabricante";
    } else {
        $where_clause .= " AND p.id_fabricante = :fabricante";
    }
    $params[':fabricante'] = $filter_fabricante;
}

// Filter by tipo
$filter_tipo = isset($_GET['tipo']) ? trim($_GET['tipo']) : '';
if (!empty($filter_tipo)) {
    if (empty($where_clause)) {
        $where_clause = "WHERE p.tipo = :tipo";
    } else {
        $where_clause .= " AND p.tipo = :tipo";
    }
    $params[':tipo'] = $filter_tipo;
}

// Get produtos with pagination, search and filters
$sql = "SELECT p.*,
        g.nome as grupo_nome,
        f.nome as fabricante_nome
        FROM produtos p
        LEFT JOIN grupos g ON p.id_grupo = g.id
        LEFT JOIN fabricantes f ON p.id_fabricante = f.id
        {$where_clause}
        ORDER BY p.nome ASC
        LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get product groups for filter
$stmt_grupos = $pdo->query("SELECT id, nome FROM grupos ORDER BY nome");
$grupos = $stmt_grupos->fetchAll(PDO::FETCH_ASSOC);

// Get fabricantes for filter
$stmt_fabricantes = $pdo->query("SELECT id, nome FROM fabricantes ORDER BY nome");
$fabricantes = $stmt_fabricantes->fetchAll(PDO::FETCH_ASSOC);

// Get distinct tipos for filter
$stmt_tipos = $pdo->query("SELECT DISTINCT tipo FROM produtos WHERE tipo IS NOT NULL AND tipo != '' ORDER BY tipo");
$tipos = $stmt_tipos->fetchAll(PDO::FETCH_ASSOC);

// Handle delete action
if (isset($_POST['delete']) && isset($_POST['id'])) {
    // Verificar se o usuário tem permissão para excluir
    if (!$current_user_permissions['delete']) {
        header('Location: /auth/access_denied.php');
        exit;
    }

    $id = (int)$_POST['id'];

    try {
        // Check if there are any movements using this product
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM movimentos WHERE id_produto = :id");
        $stmt->execute([':id' => $id]);
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            $error = "Não é possível excluir este produto pois existem {$count} movimentações associadas a ele.";
        } else {
            $stmt = $pdo->prepare("DELETE FROM produtos WHERE id = :id");
            $stmt->execute([':id' => $id]);

            // Redirect to avoid resubmission
            header("Location: list_produtos.php?deleted=1" .
                ($filter_grupo ? '&grupo=' . $filter_grupo : '') .
                ($filter_fabricante ? '&fabricante=' . $filter_fabricante : '') .
                (!empty($filter_tipo) ? '&tipo=' . urlencode($filter_tipo) : '') .
                (!empty($search) ? '&search=' . urlencode($search) : ''));
            exit;
        }
    } catch (PDOException $e) {
        $error = "Não foi possível excluir este produto. Erro: " . $e->getMessage();
    }
}

// Include header
include_once __DIR__ . '/../includes/header.php';
?>

<div class="content">
    <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-success">Produto excluído com sucesso!</div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <br>
    <div class="header-actions">
        <div>
            <h2>Lista de Produtos</h2>
        </div>

        <form class="search-form" method="get">
            <div class="form-row">
                <div class="form-col">
                    <input type="text" name="search" placeholder="Buscar por nome, fabricante ou tipo" class="form-control" value="<?= htmlspecialchars($search) ?>">
                    <?php if ($filter_grupo > 0): ?>
                        <input type="hidden" name="grupo" value="<?= $filter_grupo ?>">
                    <?php endif; ?>
                    <?php if ($filter_fabricante > 0): ?>
                        <input type="hidden" name="fabricante" value="<?= $filter_fabricante ?>">
                    <?php endif; ?>
                    <?php if (!empty($filter_tipo)): ?>
                        <input type="hidden" name="tipo" value="<?= htmlspecialchars($filter_tipo) ?>">
                    <?php endif; ?>
                </div>
                <div>
                    <button type="submit" class="btn btn-primary">Buscar</button>
                    <?php if (!empty($search)): ?>
                        <?php
                        $clear_params = [];
                        if ($filter_grupo) {
                            $clear_params[] = 'grupo=' . $filter_grupo;
                        }
                        if ($filter_fabricante) {
                            $clear_params[] = 'fabricante=' . $filter_fabricante;
                        }
                        if (!empty($filter_tipo)) {
                            $clear_params[] = 'tipo=' . urlencode($filter_tipo);
                        }
                        $clear_url = '?' . implode('&', $clear_params);
                        ?>
                        <a href="<?= $clear_url ?>" class="btn btn-outline-secondary">Limpar Busca</a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>

    <br>
    <div class="filter-row">
        <div class="filter-item">
            <label for="filter_grupo">Filtrar por Grupo:</label>
            <select id="filter_grupo" class="form-select" onchange="applyFilters()">
                <option value="0">Todos os Grupos</option>
                <?php foreach ($grupos as $grupo): ?>
                <option value="<?= $grupo['id'] ?>" <?= $filter_grupo == $grupo['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($grupo['nome']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="filter-item">
            <label for="filter_fabricante">Filtrar por Fabricante:</label>
            <select id="filter_fabricante" class="form-select" onchange="applyFilters()">
                <option value="0">Todos os Fabricantes</option>
                <?php foreach ($fabricantes as $fab): ?>
                <option value="<?= $fab['id'] ?>" <?= $filter_fabricante == $fab['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($fab['nome']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="filter-item">
            <label for="filter_tipo">Filtrar por Tipo:</label>
            <select id="filter_tipo" class="form-select" onchange="applyFilters()">
                <option value="">Todos os Tipos</option>
                <?php foreach ($tipos as $tipo): ?>
                <option value="<?= htmlspecialchars($tipo['tipo']) ?>" <?= $filter_tipo == $tipo['tipo'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($tipo['tipo']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <br>

        <?php if ($filter_grupo > 0 || $filter_fabricante > 0 || !empty($filter_tipo) || !empty($search)): ?>
            <a href="list_produtos.php" class="btn btn-outline-secondary">Limpar Filtros</a>
        <?php endif; ?>
    </div>

    <?php if (count($produtos) > 0): ?>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Fabricante</th>
                        <th>Grupo</th>
                        <th>Tipo</th>
                        <th>Volume</th>
                        <th>Preço</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($produtos as $produto): ?>
                        <tr>
                            <td>
                                <a href="../relatorios/movimentacao_produtos.php?produto_id=<?= $produto['id'] ?>" aria-label="Ver movimentações de <?= htmlspecialchars($produto['nome']) ?>"><?= htmlspecialchars($produto['nome']) ?></a>
                            </td>
                            <td>
                                <?php if (!empty($produto['fabricante_nome'])): ?>
                                    <?= htmlspecialchars($produto['fabricante_nome']) ?>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($produto['grupo_nome'])): ?>
                                    <?= htmlspecialchars($produto['grupo_nome']) ?>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($produto['tipo'] ?? '-') ?></td>
                            <td>
                                <?php if (!empty($produto['volume'])): ?>
                                    <?= htmlspecialchars($produto['volume']) ?>
                                    <?= htmlspecialchars($produto['unidade_medida'] ?? '') ?>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($produto['preco'])): ?>
                                    R$ <?= number_format($produto['preco'], 2, ',', '.') ?>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td class="actions">
                                <?php if ($current_user_permissions['update'] && $current_user_grupo != GROUP_AUDITORES): ?>
                                <a href="produto.php?id=<?= $produto['id'] ?>" class="btn btn-sm btn-warning">Editar</a>
                                <?php endif; ?>

                                <?php if ($current_user_permissions['delete']): ?>
                                <form method="post" onsubmit="return confirm('Tem certeza que deseja excluir este produto?');" style="display: inline;">
                                    <input type="hidden" name="id" value="<?= $produto['id'] ?>">
                                    <button type="submit" name="delete" class="btn btn-sm btn-danger">Excluir</button>
                                </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php
        $pagination_params = [];
        if ($filter_grupo > 0) { $pagination_params[] = 'grupo=' . $filter_grupo; }
        if ($filter_fabricante > 0) { $pagination_params[] = 'fabricante=' . $filter_fabricante; }
        if (!empty($filter_tipo)) { $pagination_params[] = 'tipo=' . urlencode($filter_tipo); }
        if (!empty($search)) { $pagination_params[] = 'search=' . urlencode($search); }
        $pagination_extra = !empty($pagination_params) ? '&' . implode('&', $pagination_params) : '';
        include_once __DIR__ . '/../includes/pagination.php';
        ?>
    <?php else: ?>
        <div class="alert alert-info">
            <?php if (!empty($search) || $filter_grupo > 0 || $filter_fabricante > 0 || !empty($filter_tipo)): ?>
                Nenhum produto encontrado com os filtros selecionados.
                <p><a href="list_produtos.php" class="btn btn-outline-primary mt-2">Limpar filtros</a></p>
            <?php else: ?>
                Nenhum produto cadastrado.
                <p><a href="produto.php" class="btn btn-primary mt-2">Cadastrar Produto</a></p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<script>
    function applyFilters() {
        let url = 'list_produtos.php?';
        let grupoValue = document.getElementById('filter_grupo').value;
        let fabricanteValue = document.getElementById('filter_fabricante').value;
        let tipoValue = document.getElementById('filter_tipo').value;
        let searchValue = "<?= urlencode($search) ?>";

        let params = [];

        if (grupoValue !== '0') {
            params.push('grupo=' + grupoValue);
        }

        if (fabricanteValue !== '0') {
            params.push('fabricante=' + fabricanteValue);
        }

        if (tipoValue !== '') {
            params.push('tipo=' + encodeURIComponent(tipoValue));
        }

        if (searchValue) {
            params.push('search=' + searchValue);
        }

        url += params.join('&');
        window.location.href = url;
    }
</script>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
