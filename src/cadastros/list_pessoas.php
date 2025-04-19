<?php
// cadastros/list_pessoas.php
require_once __DIR__ . '/../config/db.php';

// Set page title for the header
// $pageTitle = 'Lista de Pessoas';

// Pagination setup
$per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $per_page;

// Get total count for pagination
$stmt_count = $pdo->query("SELECT COUNT(*) FROM pessoas");
$total_records = $stmt_count->fetchColumn();
$total_pages = ceil($total_records / $per_page);

// Search functionality
$search = isset($_GET['search']) ? $_GET['search'] : '';
$where_clause = '';
$params = [];

if (!empty($search)) {
    $where_clause = "WHERE p.nome LIKE :search OR p.email LIKE :search";
    $params[':search'] = "%{$search}%";
}

// Filter by group if specified
$filter_grupo = isset($_GET['grupo']) ? (int)$_GET['grupo'] : 0;
if ($filter_grupo > 0) {
    $where_clause = !empty($where_clause) ? $where_clause . " AND p.id_grupo_pessoa = :grupo" : "WHERE p.id_grupo_pessoa = :grupo";
    $params[':grupo'] = $filter_grupo;
}

// Get persons with pagination and search
$sql = "SELECT p.id, p.nome, p.email, p.data_cadastro, p.enable, gp.nome AS grupo_nome, gp.id AS grupo_id
        FROM pessoas p
        LEFT JOIN grupos_pessoas gp ON p.id_grupo_pessoa = gp.id
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
$pessoas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle delete action
if (isset($_POST['delete']) && isset($_POST['id'])) {
    $id = (int)$_POST['id'];

    try {
        $stmt = $pdo->prepare("DELETE FROM pessoas WHERE id = :id");
        $stmt->execute([':id' => $id]);

        // Redirect to avoid resubmission
        header("Location: list_pessoas.php?deleted=1");
        exit;
    } catch (PDOException $e) {
        $error = "Não foi possível excluir esta pessoa. Ela pode estar vinculada a registros de movimentação.";
    }
}

// Handle enable/disable action
if (isset($_POST['toggle_enable']) && isset($_POST['id'])) {
    $id = (int)$_POST['id'];
    $enable = (int)$_POST['enable'];
    $new_status = $enable ? 0 : 1; // Toggle the current status

    try {
        $stmt = $pdo->prepare("UPDATE pessoas SET enable = :enable WHERE id = :id");
        $stmt->execute([':enable' => $new_status, ':id' => $id]);

        // Redirect to avoid resubmission with status message
        $status_msg = $new_status ? 'enabled' : 'disabled';
        header("Location: list_pessoas.php?status_change={$status_msg}");
        exit;
    } catch (PDOException $e) {
        $error = "Não foi possível alterar o status da pessoa.";
    }
}

// Include header
include_once __DIR__ . '/../includes/header.php';
?>

<div class="content">
    <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-success">Pessoa excluída com sucesso!</div>
    <?php endif; ?>

    <?php if (isset($_GET['status_change'])): ?>
        <div class="alert alert-success">Status da pessoa <?= htmlspecialchars($_GET['status_change']) ?> com sucesso!</div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <div class="header-actions">
        <div>
            <h2>Lista de Pessoas</h2>
            <a href="pessoa.php" class="btn btn-primary">Cadastrar Nova Pessoa</a>
        </div>

        <br>
        <form class="search-form" method="get">
            <div class="form-row">
                <div class="form-col">
                    <input type="text" name="search" placeholder="Buscar por nome ou email" class="form-control" value="<?= htmlspecialchars($search) ?>">
                    <?php if ($filter_grupo > 0): ?>
                        <input type="hidden" name="grupo" value="<?= $filter_grupo ?>">
                    <?php endif; ?>
                </div>
                <div>
                    <button type="submit" class="btn btn-primary">Buscar</button>
                    <?php if (!empty($search) || $filter_grupo > 0): ?>
                        <a href="list_pessoas.php" class="btn btn-outline-secondary">Limpar</a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>

    <br>
    <?php if (count($pessoas) > 0): ?>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Email</th>
                        <th>Grupo</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pessoas as $pessoa): ?>
                        <tr>
                            <td><?= $pessoa['id'] ?></td>
                            <td><?= htmlspecialchars($pessoa['nome']) ?></td>
                            <td><?= htmlspecialchars($pessoa['email'] ?? '-') ?></td>
                            <td>
                                <?php if (!empty($pessoa['grupo_nome'])): ?>
                                    <a href="list_pessoas.php?grupo=<?= $pessoa['grupo_id'] ?>"><?= htmlspecialchars($pessoa['grupo_nome']) ?></a>
                                <?php else: ?>
                                    <span class="text-muted">Não atribuído</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <form method="post" style="display: inline;">
                                    <input type="hidden" name="id" value="<?= $pessoa['id'] ?>">
                                    <input type="hidden" name="enable" value="<?= $pessoa['enable'] ?>">
                                    <button type="submit" name="toggle_enable" class="btn btn-sm <?= $pessoa['enable'] ? 'btn-success' : 'btn-secondary' ?>">
                                        <?= $pessoa['enable'] ? 'Habilitado' : 'Desabilitado' ?>
                                    </button>
                                </form>
                            </td>
                            <td class="actions">
                                <a href="pessoa.php?id=<?= $pessoa['id'] ?>" class="btn btn-sm btn-warning">Editar</a>
                                <form method="post" onsubmit="return confirm('Tem certeza que deseja excluir esta pessoa?');" style="display: inline;">
                                    <input type="hidden" name="id" value="<?= $pessoa['id'] ?>">
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
                    <li><a href="?page=1<?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= $filter_grupo ? '&grupo=' . $filter_grupo : '' ?>">Primeira</a></li>
                    <li><a href="?page=<?= ($page - 1) ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= $filter_grupo ? '&grupo=' . $filter_grupo : '' ?>">Anterior</a></li>
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
                        <li><a href="?page=<?= $i ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= $filter_grupo ? '&grupo=' . $filter_grupo : '' ?>"><?= $i ?></a></li>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <li><a href="?page=<?= ($page + 1) ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= $filter_grupo ? '&grupo=' . $filter_grupo : '' ?>">Próxima</a></li>
                    <li><a href="?page=<?= $total_pages ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= $filter_grupo ? '&grupo=' . $filter_grupo : '' ?>">Última</a></li>
                <?php else: ?>
                    <li class="disabled"><span>Próxima</span></li>
                    <li class="disabled"><span>Última</span></li>
                <?php endif; ?>
            </ul>
        <?php endif; ?>
    <?php else: ?>
        <div class="alert alert-info">
            <?php if (!empty($search) || $filter_grupo > 0): ?>
                Nenhuma pessoa encontrada com os filtros selecionados.
                <p><a href="list_pessoas.php" class="btn btn-outline-primary mt-2">Limpar filtros</a></p>
            <?php else: ?>
                Nenhuma pessoa cadastrada.
                <p><a href="pessoa.php" class="btn btn-primary mt-2">Cadastrar Pessoa</a></p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>