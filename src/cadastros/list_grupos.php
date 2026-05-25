<?php
// cadastros/list_grupos.php
require_once __DIR__ . '/../config/db.php';

// Verificação de permissões
require_once __DIR__ . '/../auth/auth_check.php';
requirePermission(PERMISSION_READ, $current_user_grupo);

// Set page title
// $pageTitle = 'Lista de Grupos de Produtos';

// Pagination setup
$per_page = 25;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $per_page;

// Get total count for pagination
$stmt_count = $pdo->query("SELECT COUNT(*) FROM grupos");
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

// Get product groups with pagination and search
$sql = "SELECT g.*,
        (SELECT COUNT(*) FROM produtos WHERE id_grupo = g.id) AS total_produtos
        FROM grupos g
        {$where_clause}
        ORDER BY g.nome ASC
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
    // Verificar se o usuário tem permissão para excluir
    if (!userCan(PERMISSION_DELETE)) {
        header('Location: /auth/access_denied.php');
        exit;
    }

    $id = (int)$_POST['id'];

    try {
        // Check if there are any products using this group
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM produtos WHERE id_grupo = :id");
        $stmt->execute([':id' => $id]);
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            $error = "Não é possível excluir este grupo pois existem {$count} produtos associados a ele.";
        } else {
            $stmt = $pdo->prepare("DELETE FROM grupos WHERE id = :id");
            $stmt->execute([':id' => $id]);

            // Redirect to avoid resubmission
            header("Location: list_grupos.php?deleted=1");
            exit;
        }
    } catch (PDOException $e) {
        $error = "Não foi possível excluir este grupo. Erro: " . $e->getMessage();
    }
}

// Include header
include_once __DIR__ . '/../includes/header.php';
?>

<div class="content">
    <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-success">Grupo excluído com sucesso!</div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <div class="header-actions">
        <div>
            <h2>Lista de Grupos de Produtos</h2>
        </div>

        <br>
        <form class="search-form" method="get">
            <div class="form-row">
                <div class="form-col">
                    <input type="text" name="search" placeholder="Buscar por nome ou descrição" class="form-control" value="<?= htmlspecialchars($search) ?>">
                </div>
                <div>
                    <button type="submit" class="btn btn-primary">Buscar</button>
                </div>
            </div>
        </form>
    </div>

    <br>
    <?php if (count($grupos) > 0): ?>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Descrição</th>
                        <th>Produtos</th>
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
                                <span class="badge"><?= $grupo['total_produtos'] ?></span>
                                <?php if ($grupo['total_produtos'] > 0): ?>
                                    <a href="list_produtos.php?grupo=<?= $grupo['id'] ?>" class="btn-link">Ver produtos</a>
                                <?php endif; ?>
                            </td>
                            <td class="actions">
                                <?php if ($current_user_permissions['update']): ?>
                                <a href="grupo.php?id=<?= $grupo['id'] ?>" class="btn btn-sm btn-warning">Editar</a>
                                <?php endif; ?>

                                <?php if ($current_user_permissions['delete']): ?>
                                <form method="post" onsubmit="return confirm('Tem certeza que deseja excluir este grupo?');" style="display: inline;">
                                    <input type="hidden" name="id" value="<?= $grupo['id'] ?>">
                                    <button type="submit" name="delete" class="btn btn-sm btn-danger" <?= $grupo['total_produtos'] > 0 ? 'disabled title="Não é possível excluir um grupo que está sendo usado"' : '' ?>>Excluir</button>
                                </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php
        $pagination_extra = !empty($search) ? '&search=' . urlencode($search) : '';
        include_once __DIR__ . '/../includes/pagination.php';
        ?>
    <?php else: ?>
        <div class="alert alert-info">
            <?php if (!empty($search)): ?>
                Nenhum grupo encontrado com o termo "<?= htmlspecialchars($search) ?>".
                <p><a href="list_grupos.php" class="btn btn-outline-primary mt-2">Limpar busca</a></p>
            <?php else: ?>
                Nenhum grupo cadastrado.
                <p><a href="grupo.php" class="btn btn-primary mt-2">Cadastrar Grupo</a></p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
