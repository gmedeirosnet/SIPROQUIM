<?php
// cadastros/list_lugares.php
require_once __DIR__ . '/../config/db.php';

// Verificação de permissões
require_once __DIR__ . '/../auth/auth_check.php';
requirePermission(PERMISSION_READ, $current_user_grupo);

define('SEARCH_PARAM', '&search=');

// Set page title
// $pageTitle = 'Lista de Almoxarifados';

// Pagination setup
$per_page = 25;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $per_page;

// Get total count for pagination
$stmt_count = $pdo->query("SELECT COUNT(*) FROM lugares");
$total_records = $stmt_count->fetchColumn();
$total_pages = ceil($total_records / $per_page);



// Get lugares with pagination
$sql = "SELECT * FROM lugares ORDER BY nome ASC LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$lugares = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle delete action
if (isset($_POST['delete']) && isset($_POST['id'])) {
    // Verificar se o usuário tem permissão para excluir
    if (!userCan(PERMISSION_DELETE)) {
        header('Location: /auth/access_denied.php');
        exit;
    }

    $id = (int)$_POST['id'];

    try {
        // Check if there are any movements using this place
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM movimentos WHERE id_lugar = :id");
        $stmt->execute([':id' => $id]);
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            $error = "Não é possível excluir este almoxarifado pois existem {$count} movimentações associadas a ele.";
        } else {
            $stmt = $pdo->prepare("DELETE FROM lugares WHERE id = :id");
            $stmt->execute([':id' => $id]);

            // Redirect to avoid resubmission
            header("Location: list_lugares.php?deleted=1");
            exit;
        }
    } catch (PDOException $e) {
        $error = "Não foi possível excluir este almoxarifado. Erro: " . $e->getMessage();
    }
}

// Include header
include_once __DIR__ . '/../includes/header.php';
?>

<div class="content">
    <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-success">Almoxarifado excluído com sucesso!</div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <div class="header-actions">
        <div>
            <h2>Lista de Almoxarifados</h2>
        </div>


    </div>

    </br>
    <?php if (count($lugares) > 0): ?>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Descrição</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($lugares as $lugar): ?>
                        <tr>
                            <td><?= $lugar['id'] ?></td>
                            <td><?= htmlspecialchars($lugar['nome']) ?></td>
                            <td><?= htmlspecialchars($lugar['descricao'] ?? '-') ?></td>
                            <td class="actions">
                                <?php if ($current_user_permissions['update']): ?>
                                <a href="lugar.php?id=<?= $lugar['id'] ?>" class="btn btn-sm btn-warning">Editar</a>
                                <?php endif; ?>

                                <?php if ($current_user_permissions['delete']): ?>
                                <form method="post" onsubmit="return confirm('Tem certeza que deseja excluir este almoxarifado?');" style="display: inline;">
                                    <input type="hidden" name="id" value="<?= $lugar['id'] ?>">
                                    <button type="submit" name="delete" class="btn btn-sm btn-danger">Excluir</button>
                                </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($total_pages > 1): ?>
            <ul class="pagination">
                <?php if ($page > 1): ?>
                    <li><a href="?page=1<?= !empty($search) ? SEARCH_PARAM . urlencode($search) : '' ?>">Primeira</a></li>
                    <li><a href="?page=<?= ($page - 1) . (!empty($search) ? SEARCH_PARAM . urlencode($search) : '') ?>">Anterior</a></li>
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
                        <li><a href="?page=<?= $i . (!empty($search) ? SEARCH_PARAM . urlencode($search) : '') ?>" aria-label="Página <?= $i ?>"><?= $i ?></a></li>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <li><a href="?page=<?= ($page + 1) . (!empty($search) ? SEARCH_PARAM . urlencode($search) : '') ?>">Próxima</a></li>
                    <li><a href="?page=<?= $total_pages . (!empty($search) ? SEARCH_PARAM . urlencode($search) : '') ?>">Última</a></li>
                <?php else: ?>
                    <li class="disabled"><span>Próxima</span></li>
                    <li class="disabled"><span>Última</span></li>
                <?php endif; ?>
            </ul>
        <?php endif; ?>
    <?php else: ?>
        <div class="alert alert-info">
            <?php if (!empty($search)): ?>
                Nenhum almoxarifado encontrado com o termo "<?= htmlspecialchars($search) ?>".
                <p><a href="list_lugares.php" class="btn btn-outline-primary mt-2">Limpar busca</a></p>
            <?php else: ?>
                Nenhum almoxarifado cadastrado.
                <p><a href="lugar.php" class="btn btn-primary mt-2">Cadastrar Almoxarifado</a></p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
