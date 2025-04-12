<?php
// cadastros/list_grupos_pessoas.php
require_once __DIR__ . '/../config/db.php';

// Pagination setup
$per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $per_page;

// Get total count for pagination
$stmt_count = $pdo->query("SELECT COUNT(*) FROM grupos_pessoas");
$total_records = $stmt_count->fetchColumn();
$total_pages = ceil($total_records / $per_page);

// Search functionality
$search = isset($_GET['search']) ? $_GET['search'] : '';
$where_clause = '';
$params = [];

if (!empty($search)) {
    $where_clause = "WHERE nome LIKE :search OR descricao LIKE :search";
    $params[':search'] = "%{$search}%";
}

// Get person groups with pagination and search
$sql = "SELECT gp.*,
        (SELECT COUNT(*) FROM pessoas WHERE id_grupo_pessoa = gp.id) AS total_pessoas
        FROM grupos_pessoas gp
        {$where_clause}
        ORDER BY gp.nome ASC
        LIMIT :limit OFFSET :offset";

$stmt = $pdo->prepare($sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$grupos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle delete action
if (isset($_POST['delete']) && isset($_POST['id'])) {
    $id = (int)$_POST['id'];

    try {
        // Check if there are any persons using this group
        $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM pessoas WHERE id_grupo_pessoa = :id");
        $stmt_check->execute([':id' => $id]);
        $pessoas_count = $stmt_check->fetchColumn();

        if ($pessoas_count > 0) {
            $error = "Não é possível excluir este grupo pois existem pessoas associadas a ele.";
        } else {
            $stmt = $pdo->prepare("DELETE FROM grupos_pessoas WHERE id = :id");
            $stmt->execute([':id' => $id]);

            // Redirect to avoid resubmission
            header("Location: list_grupos_pessoas.php?deleted=1");
            exit;
        }
    } catch (PDOException $e) {
        $error = "Não foi possível excluir este grupo. Erro: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Grupos de Pessoas</title>
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
        .badge {
            display: inline-block;
            padding: 3px 7px;
            font-size: 12px;
            font-weight: bold;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: 10px;
            background-color: #6c757d;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Lista de Grupos de Pessoas</h1>

        <?php if (isset($_GET['deleted'])): ?>
            <div class="message success">Grupo excluído com sucesso!</div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="message error"><?= $error ?></div>
        <?php endif; ?>

        <div class="header-actions">
            <a href="grupo_pessoa.php" class="btn btn-primary">Cadastrar Novo Grupo</a>

            <form class="search-form" method="get">
                <input type="text" name="search" placeholder="Buscar por nome ou descrição" value="<?= htmlspecialchars($search) ?>">
                <button type="submit">Buscar</button>
            </form>
        </div>

        <?php if (count($grupos) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Descrição</th>
                        <th>Pessoas</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($grupos as $grupo): ?>
                        <tr>
                            <td><?= $grupo['id'] ?></td>
                            <td><?= htmlspecialchars($grupo['nome']) ?></td>
                            <td><?= htmlspecialchars($grupo['descricao'] ?? '-') ?></td>
                            <td>
                                <span class="badge"><?= $grupo['total_pessoas'] ?></span>
                                <?php if ($grupo['total_pessoas'] > 0): ?>
                                    <a href="list_pessoas.php?grupo=<?= $grupo['id'] ?>" class="btn-link">Ver pessoas</a>
                                <?php endif; ?>
                            </td>
                            <td class="actions">
                                <a href="grupo_pessoa.php?id=<?= $grupo['id'] ?>" class="btn btn-warning">Editar</a>
                                <form method="post" onsubmit="return confirm('Tem certeza que deseja excluir este grupo?');" style="display: inline;">
                                    <input type="hidden" name="id" value="<?= $grupo['id'] ?>">
                                    <button type="submit" name="delete" class="btn btn-danger" <?= $grupo['total_pessoas'] > 0 ? 'disabled title="Não é possível excluir um grupo que está sendo usado"' : '' ?>>Excluir</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=1<?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">Primeira</a>
                        <a href="?page=<?= ($page - 1) . (!empty($search) ? '&search=' . urlencode($search) : '') ?>">Anterior</a>
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
                            <a href="?page=<?= $i . (!empty($search) ? '&search=' . urlencode($search) : '') ?>"><?= $i ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?= ($page + 1) . (!empty($search) ? '&search=' . urlencode($search) : '') ?>">Próxima</a>
                        <a href="?page=<?= $total_pages . (!empty($search) ? '&search=' . urlencode($search) : '') ?>">Última</a>
                    <?php else: ?>
                        <span class="disabled">Próxima</span>
                        <span class="disabled">Última</span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <p>Nenhum grupo de pessoas encontrado.</p>
        <?php endif; ?>

        <p><a href="../index.php" class="btn">Voltar para a Página Inicial</a></p>
    </div>
</body>
</html>