<?php
// cadastros/list_produtos.php
require_once __DIR__ . '/../config/db.php';

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

// Get produtos with pagination and search
$sql = "SELECT p.*, g.nome AS grupo_nome, f.nome AS fabricante_nome, f.cnpj AS fabricante_cnpj
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
        $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM movimentos WHERE id_produto = :id");
        $stmt_check->execute([':id' => $id]);
        $movimentos_count = $stmt_check->fetchColumn();

        if ($movimentos_count > 0) {
            $error = "Não é possível excluir este produto pois existem movimentações associadas a ele.";
        } else {
            $stmt = $pdo->prepare("DELETE FROM produtos WHERE id = :id");
            $stmt->execute([':id' => $id]);

            // Redirect to avoid resubmission
            header("Location: list_produtos.php?deleted=1");
            exit;
        }
    } catch (PDOException $e) {
        $error = "Não foi possível excluir este produto. Erro: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Produtos</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #333;
            text-align: center;
        }
        .message {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #007bff;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        tr:hover {
            background-color: #e9f3ff;
        }
        .actions {
            display: flex;
            gap: 5px;
        }
        .btn {
            padding: 8px 12px;
            cursor: pointer;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
        }
        .btn-primary {
            background-color: #007bff;
            color: white;
        }
        .btn-warning {
            background-color: #ffc107;
            color: #212529;
        }
        .btn-danger {
            background-color: #dc3545;
            color: white;
        }
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }
        .pagination a, .pagination span {
            padding: 8px 16px;
            margin: 0 5px;
            border: 1px solid #ddd;
            text-decoration: none;
            color: #007bff;
        }
        .pagination a:hover {
            background-color: #007bff;
            color: white;
        }
        .pagination .active {
            background-color: #007bff;
            color: white;
        }
        .pagination .disabled {
            color: #6c757d;
            pointer-events: none;
        }
        .search-form {
            margin-bottom: 20px;
            display: flex;
        }
        .search-form input[type="text"] {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px 0 0 4px;
        }
        .search-form button {
            padding: 10px 15px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 0 4px 4px 0;
            cursor: pointer;
        }
        .header-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .filter-controls {
            display: flex;
            gap: 10px;
            align-items: center;
            margin-bottom: 20px;
        }
        .filter-controls label {
            margin-right: 5px;
        }
        .filter-controls select {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .filter-row {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }
        .filter-item {
            display: flex;
            align-items: center;
            gap: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Lista de Produtos</h1>

        <?php if (isset($_GET['deleted'])): ?>
            <div class="message success">Produto excluído com sucesso!</div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="message error"><?= $error ?></div>
        <?php endif; ?>

        <div class="header-actions">
            <a href="produto.php" class="btn btn-primary">Cadastrar Novo Produto</a>

            <form class="search-form" method="get">
                <input type="text" name="search" placeholder="Buscar por nome, fabricante ou tipo" value="<?= htmlspecialchars($search) ?>">
                <?php if ($filter_grupo > 0): ?>
                    <input type="hidden" name="grupo" value="<?= $filter_grupo ?>">
                <?php endif; ?>
                <?php if ($filter_fabricante > 0): ?>
                    <input type="hidden" name="fabricante" value="<?= $filter_fabricante ?>">
                <?php endif; ?>
                <button type="submit">Buscar</button>
            </form>
        </div>

        <div class="filter-row">
            <div class="filter-item">
                <label for="filter_grupo">Filtrar por Grupo:</label>
                <select id="filter_grupo" onchange="applyFilters()">
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
                <select id="filter_fabricante" onchange="applyFilters()">
                    <option value="0">Todos os Fabricantes</option>
                    <?php foreach ($fabricantes as $fab): ?>
                    <option value="<?= $fab['id'] ?>" <?= $filter_fabricante == $fab['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($fab['nome']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <?php if ($filter_grupo > 0 || $filter_fabricante > 0 || !empty($search)): ?>
                <a href="list_produtos.php" class="btn">Limpar Filtros</a>
            <?php endif; ?>
        </div>

        <?php if (count($produtos) > 0): ?>
            <table>
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
                                    <br><small><?= htmlspecialchars($produto['fabricante_cnpj'] ?? '') ?></small>
                                <?php elseif (!empty($produto['fabricante'])): ?>
                                    <?= htmlspecialchars($produto['fabricante']) ?> <small>(legado)</small>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($produto['grupo_nome'] ?? '-') ?></td>
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
                                    R$ <?= number_format((float)$produto['preco'], 2, ',', '.') ?>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td class="actions">
                                <a href="produto.php?id=<?= $produto['id'] ?>" class="btn btn-warning">Editar</a>
                                <form method="post" onsubmit="return confirm('Tem certeza que deseja excluir este produto?');" style="display: inline;">
                                    <input type="hidden" name="id" value="<?= $produto['id'] ?>">
                                    <button type="submit" name="delete" class="btn btn-danger">Excluir</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=1<?= $filter_grupo ? '&grupo=' . $filter_grupo : '' ?><?= $filter_fabricante ? '&fabricante=' . $filter_fabricante : '' ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">Primeira</a>
                        <a href="?page=<?= ($page - 1) ?><?= $filter_grupo ? '&grupo=' . $filter_grupo : '' ?><?= $filter_fabricante ? '&fabricante=' . $filter_fabricante : '' ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">Anterior</a>
                    <?php else: ?>
                        <span class="disabled">Primeira</span>
                        <span class="disabled">Anterior</span>
                    <?php endif; ?>

                    <?php
                    $start_page = max(1, $page - 2);
                    $end_page = min($start_page + 4, $total_pages);
                    for ($i = $start_page; $i <= $end_page; $i++): ?>
                        <?php if ($i == $page): ?>
                            <span class="active"><?= $i ?></span>
                        <?php else: ?>
                            <a href="?page=<?= $i ?><?= $filter_grupo ? '&grupo=' . $filter_grupo : '' ?><?= $filter_fabricante ? '&fabricante=' . $filter_fabricante : '' ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>"><?= $i ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?= ($page + 1) ?><?= $filter_grupo ? '&grupo=' . $filter_grupo : '' ?><?= $filter_fabricante ? '&fabricante=' . $filter_fabricante : '' ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">Próxima</a>
                        <a href="?page=<?= $total_pages ?><?= $filter_grupo ? '&grupo=' . $filter_grupo : '' ?><?= $filter_fabricante ? '&fabricante=' . $filter_fabricante : '' ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">Última</a>
                    <?php else: ?>
                        <span class="disabled">Próxima</span>
                        <span class="disabled">Última</span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <p>Nenhum produto encontrado.</p>
        <?php endif; ?>

        <p><a href="../index.php" class="btn">Voltar para a Página Inicial</a></p>
    </div>

    <script>
        function applyFilters() {
            const grupoValue = document.getElementById('filter_grupo').value;
            const fabricanteValue = document.getElementById('filter_fabricante').value;
            const searchValue = '<?= urlencode($search) ?>';

            let url = 'list_produtos.php?';
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
</body>
</html>