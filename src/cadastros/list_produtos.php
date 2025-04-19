<?php
// cadastros/list_produtos.php
require_once __DIR__ . '/../config/db.php';

// Set page title
// $pageTitle = 'Lista de Produtos';

// Pagination setup
$per_page = 10;
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

// Handle delete action
if (isset($_POST['delete']) && isset($_POST['id'])) {
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
                </div>
                <div>
                    <button type="submit" class="btn btn-primary">Buscar</button>
                    <?php if (!empty($search)): ?>
                        <a href="?<?= $filter_grupo ? 'grupo=' . $filter_grupo : '' ?><?= $filter_fabricante ? ($filter_grupo ? '&' : '') . 'fabricante=' . $filter_fabricante : '' ?>" class="btn btn-outline-secondary">Limpar Busca</a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>

    </br>
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
        </br>

        <?php if ($filter_grupo > 0 || $filter_fabricante > 0 || !empty($search)): ?>
            <a href="list_produtos.php" class="btn btn-outline-secondary">Limpar Filtros</a>
        <?php endif; ?>
    </div>

    <?php if (count($produtos) > 0): ?>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
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
                            <td><?= $produto['id'] ?></td>
                            <td><?= htmlspecialchars($produto['nome']) ?></td>
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
                                <a href="produto.php?id=<?= $produto['id'] ?>" class="btn btn-sm btn-warning">Editar</a>
                                <form method="post" onsubmit="return confirm('Tem certeza que deseja excluir este produto?');" style="display: inline;">
                                    <input type="hidden" name="id" value="<?= $produto['id'] ?>">
                                    <button type="submit" name="delete" class="btn btn-sm btn-danger">Excluir</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($total_pages > 1): ?>
            <ul class="pagination">
                <?php if ($page > 1): ?>
                    <li><a href="?page=1<?= $filter_grupo ? '&grupo=' . $filter_grupo : '' ?><?= $filter_fabricante ? '&fabricante=' . $filter_fabricante : '' ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">Primeira</a></li>
                    <li><a href="?page=<?= ($page - 1) ?><?= $filter_grupo ? '&grupo=' . $filter_grupo : '' ?><?= $filter_fabricante ? '&fabricante=' . $filter_fabricante : '' ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">Anterior</a></li>
                <?php else: ?>
                    <li class="disabled"><span>Primeira</span></li>
                    <li class="disabled"><span>Anterior</span></li>
                <?php endif; ?>

                <?php
                $start_page = max(1, $page - 2);
                $end_page = min($start_page + 4, $total_pages);
                for ($i = $start_page; $i <= $end_page; $i++): ?>
                    <?php if ($i == $page): ?>
                        <li class="active"><span><?= $i ?></span></li>
                    <?php else: ?>
                        <li><a href="?page=<?= $i ?><?= $filter_grupo ? '&grupo=' . $filter_grupo : '' ?><?= $filter_fabricante ? '&fabricante=' . $filter_fabricante : '' ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>"><?= $i ?></a></li>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <li><a href="?page=<?= ($page + 1) ?><?= $filter_grupo ? '&grupo=' . $filter_grupo : '' ?><?= $filter_fabricante ? '&fabricante=' . $filter_fabricante : '' ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">Próxima</a></li>
                    <li><a href="?page=<?= $total_pages ?><?= $filter_grupo ? '&grupo=' . $filter_grupo : '' ?><?= $filter_fabricante ? '&fabricante=' . $filter_fabricante : '' ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">Última</a></li>
                <?php else: ?>
                    <li class="disabled"><span>Próxima</span></li>
                    <li class="disabled"><span>Última</span></li>
                <?php endif; ?>
            </ul>
        <?php endif; ?>
    <?php else: ?>
        <div class="alert alert-info">
            <?php if (!empty($search) || $filter_grupo > 0 || $filter_fabricante > 0): ?>
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
        let searchValue = "<?= urlencode($search) ?>";

        let params = [];

        if (grupoValue !== '0') {
            params.push('grupo=' + grupoValue);
        }

        if (fabricanteValue !== '0') {
            params.push('fabricante=' + fabricanteValue);
        }

        if (searchValue) {
            params.push('search=' + searchValue);
        }

        url += params.join('&');
        window.location.href = url;
    }
</script>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>