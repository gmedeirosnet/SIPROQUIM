<?php
// cadastros/list_fabricantes.php
require_once __DIR__ . '/../config/db.php';

// Set page title for header
$pageTitle = "Lista de Fabricantes";

// Pagination setup
$per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $per_page;

// Get total count for pagination
$stmt_count = $pdo->query("SELECT COUNT(*) FROM fabricantes");
$total_records = $stmt_count->fetchColumn();
$total_pages = ceil($total_records / $per_page);

// Search functionality
$search = isset($_GET['search']) ? $_GET['search'] : '';
$where_clause = '';
$params = [];

if (!empty($search)) {
    $where_clause = "WHERE nome LIKE :search OR cnpj LIKE :search OR email LIKE :search";
    $params[':search'] = "%{$search}%";
}

// Get fabricantes with pagination and search
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
    $id = (int)$_POST['id'];

    try {
        // Check if there are any products using this fabricante
        $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM produtos WHERE id_fabricante = :id");
        $stmt_check->execute([':id' => $id]);
        $produtos_count = $stmt_check->fetchColumn();

        if ($produtos_count > 0) {
            $error = "Não é possível excluir este fabricante pois existem produtos associados a ele.";
        } else {
            $stmt = $pdo->prepare("DELETE FROM fabricantes WHERE id = :id");
            $stmt->execute([':id' => $id]);

            // Redirect to avoid resubmission
            header("Location: list_fabricantes.php?deleted=1");
            exit;
        }
    } catch (PDOException $e) {
        $error = "Não foi possível excluir este fabricante. Erro: " . $e->getMessage();
    }
}

// Include header
include_once __DIR__ . '/../includes/header.php';
?>

<div class="content">
    <div class="widget">
        <div class="widget-header d-flex justify-content-between align-items-center">
            <h3 class="widget-title">Lista de Fabricantes</h3>
            <a href="fabricante.php" class="btn btn-primary btn-sm">Cadastrar Novo Fabricante</a>
        </div>

        <?php if (isset($_GET['deleted'])): ?>
            <div class="alert alert-success">Fabricante excluído com sucesso!</div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <div class="search-box mb-4">
            <form method="get" action="" class="d-flex">
                <input type="text" name="search" class="form-control" placeholder="Buscar por nome, CNPJ ou email" value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="btn btn-primary">Buscar</button>
            </form>
        </div>

        <?php if (count($fabricantes) > 0): ?>
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>CNPJ</th>
                            <th>Email</th>
                            <th>Endereço</th>
                            <th class="text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($fabricantes as $fabricante): ?>
                            <tr>
                                <td><?= htmlspecialchars($fabricante['nome']) ?></td>
                                <td><?= htmlspecialchars($fabricante['cnpj']) ?></td>
                                <td><?= htmlspecialchars($fabricante['email'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($fabricante['endereco'] ?? '-') ?></td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="fabricante.php?id=<?= $fabricante['id'] ?>" class="btn btn-outline-primary">Editar</a>
                                        <form method="post" onsubmit="return confirm('Tem certeza que deseja excluir este fabricante?');" style="display: inline;">
                                            <input type="hidden" name="id" value="<?= $fabricante['id'] ?>">
                                            <button type="submit" name="delete" class="btn btn-outline-danger">Excluir</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($total_pages > 1): ?>
                <div class="pagination-container">
                    <ul class="pagination">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=1<?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">Primeira</a>
                            </li>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= ($page - 1) ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">Anterior</a>
                            </li>
                        <?php else: ?>
                            <li class="page-item disabled">
                                <span class="page-link">Primeira</span>
                            </li>
                            <li class="page-item disabled">
                                <span class="page-link">Anterior</span>
                            </li>
                        <?php endif; ?>

                        <?php
                        $start_page = max(1, $page - 2);
                        $end_page = min($start_page + 4, $total_pages);
                        for ($i = $start_page; $i <= $end_page; $i++): ?>
                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                <?php if ($i == $page): ?>
                                    <span class="page-link"><?= $i ?></span>
                                <?php else: ?>
                                    <a class="page-link" href="?page=<?= $i ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>"><?= $i ?></a>
                                <?php endif; ?>
                            </li>
                        <?php endfor; ?>

                        <?php if ($page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= ($page + 1) ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">Próxima</a>
                            </li>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $total_pages ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">Última</a>
                            </li>
                        <?php else: ?>
                            <li class="page-item disabled">
                                <span class="page-link">Próxima</span>
                            </li>
                            <li class="page-item disabled">
                                <span class="page-link">Última</span>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="alert alert-info">
                <p>Nenhum fabricante encontrado.</p>
            </div>
        <?php endif; ?>

        <div class="btn-group mt-4">
            <a href="../index.php" class="btn btn-secondary">Voltar para a Página Inicial</a>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>