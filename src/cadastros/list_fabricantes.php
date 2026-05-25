<?php
// cadastros/list_fabricantes.php

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../auth/auth_check.php';
requirePermission(PERMISSION_READ, $current_user_grupo);


$pageTitle = 'Lista de Fabricantes';

$per_page = 25;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $per_page;

$stmt_count = $pdo->query("SELECT COUNT(*) FROM fabricantes");
$total_records = $stmt_count->fetchColumn();
$total_pages = ceil($total_records / $per_page);

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$where_clause = '';
$params = [];

if (!empty($search)) {
    $where_clause = "WHERE nome ILIKE :search OR cnpj LIKE :search OR email ILIKE :search";
    $params[':search'] = "%{$search}%";
}

$sql = "SELECT * FROM fabricantes {$where_clause} ORDER BY nome ASC LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$fabricantes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle delete action
if (isset($_POST['delete']) && isset($_POST['id'])) {
    if (!userCan(PERMISSION_DELETE)) {
        header('Location: /auth/access_denied.php');
        exit;
    }

    $id = (int)$_POST['id'];

    try {
        $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM produtos WHERE id_fabricante = :id");
        $stmt_check->execute([':id' => $id]);
        $produtos_count = $stmt_check->fetchColumn();

        if ($produtos_count > 0) {
            $error = "Não é possível excluir este fabricante pois existem produtos associados a ele.";
        } else {
            $stmt = $pdo->prepare("DELETE FROM fabricantes WHERE id = :id");
            $stmt->execute([':id' => $id]);

            header("Location: list_fabricantes.php?deleted=1");
            exit;
        }
    } catch (PDOException $e) {
        error_log("Fabricante delete failed: " . $e->getMessage());
        $error = "Não foi possível excluir este fabricante. Tente novamente.";
    }
}

include_once __DIR__ . '/../includes/header.php';
?>

<div class="content">
    <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-success">Fabricante excluído com sucesso!</div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="header-actions">
        <div>
            <h2>Lista de Fabricantes</h2>
        </div>

        <form class="search-form" method="get">
            <div class="form-row">
                <div class="form-col">
                    <input type="text" name="search" class="form-control" placeholder="Buscar por nome, CNPJ ou email" value="<?= htmlspecialchars($search) ?>">
                </div>
                <div>
                    <button type="submit" class="btn btn-primary">Buscar</button>
                    <?php if (!empty($search)): ?>
                        <a href="list_fabricantes.php" class="btn btn-outline-secondary">Limpar</a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>

    </br>
    <?php if (count($fabricantes) > 0): ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>CNPJ</th>
                        <th>Email</th>
                        <th>Endereço</th>
                        <th>Observação</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($fabricantes as $fabricante): ?>
                        <tr>
                            <td><?= htmlspecialchars($fabricante['id']) ?></td>
                            <td><?= htmlspecialchars($fabricante['nome']) ?></td>
                            <td><?= htmlspecialchars($fabricante['cnpj']) ?></td>
                            <td><?= htmlspecialchars($fabricante['email'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($fabricante['endereco'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($fabricante['observacao'] ?? '-') ?></td>
                            <td class="actions">
                                <?php if ($current_user_permissions['update']): ?>
                                    <a href="?id=<?= (int)$fabricante['id'] ?>" class="btn btn-sm btn-warning">Editar</a>
                                <?php endif; ?>

                                <?php if ($current_user_permissions['delete']): ?>
                                    <form method="post" onsubmit="return confirm('Tem certeza que deseja excluir este fabricante?');" style="display: inline;">
                                        <input type="hidden" name="id" value="<?= (int)$fabricante['id'] ?>">
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
        $pagination_extra = !empty($search) ? '&search=' . urlencode($search) : '';
        include __DIR__ . '/../includes/pagination.php';
        ?>
    <?php else: ?>
        <div class="alert alert-info">
            <p>Nenhum fabricante encontrado.</p>
        </div>
    <?php endif; ?>

    <div class="mt-4">
        <a href="../index.php" class="btn btn-secondary">Voltar para a Página Inicial</a>
    </div>
</div>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
