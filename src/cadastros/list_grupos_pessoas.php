<?php
// cadastros/list_grupos_pessoas.php
require_once __DIR__ . '/../config/db.php';

// Verificação de permissões
require_once __DIR__ . '/../auth/auth_check.php';
requirePermission(PERMISSION_READ, $current_user_grupo);

// Set page title for the header
// $pageTitle = 'Lista de Grupos de Pessoas';

// Pagination setup
$per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $per_page;

// Get total count for pagination
$stmt_count = $pdo->query("SELECT COUNT(*) FROM grupos_pessoas");
$total_records = $stmt_count->fetchColumn();
$total_pages = ceil($total_records / $per_page);




// Ensure variables are defined after search removal
$where_clause = '';
$params = [];

// Get person groups with pagination
$sql = "SELECT gp.*,
    (SELECT COUNT(*) FROM pessoas WHERE id_grupo_pessoa = gp.id) AS total_pessoas
    FROM grupos_pessoas gp
    ORDER BY gp.nome ASC
    LIMIT :limit OFFSET :offset";

$stmt = $pdo->prepare($sql);
$stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$grupos = $stmt->fetchAll(PDO::FETCH_ASSOC);



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
        <h2>Lista de Grupos de Pessoas</h2>

    </div>

    <br>
    <?php if (count($grupos) > 0): ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Descrição</th>
                        <th>Pessoas</th>
                        <th>Permissão</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    function grupoPermissaoLabel($id) {
                        if ($id == 1) return 'Administrador (CRUD)';
                        if ($id == 3) return 'Técnico (CRU)';
                        if ($id == 4) return 'Supervisor (CRU)';
                        if ($id == 5) return 'Auditor (R)';
                        return 'Leitura';
                    }
                    ?>
                    <?php foreach ($grupos as $grupo): ?>
                        <tr>
                            <td><?= $grupo['id'] ?></td>
                            <td><?= htmlspecialchars($grupo['nome']) ?></td>
                            <td><?= htmlspecialchars($grupo['descricao'] ?? '-') ?></td>
                            <td>
                                <span class="badge badge-secondary"><?= $grupo['total_pessoas'] ?></span>
                                <?php if ($grupo['total_pessoas'] > 0): ?>
                                    <a href="list_pessoas.php?grupo=<?= $grupo['id'] ?>" class="btn btn-sm btn-link">Ver pessoas</a>
                                <?php endif; ?>
                            </td>
                            <td><?= grupoPermissaoLabel($grupo['id']) ?></td>
                            <td class="actions">
                                <?php if (isAdmin($current_user_grupo)): ?>
                                <a href="grupo_pessoa.php?id=<?= $grupo['id'] ?>" class="btn btn-sm btn-warning">Editar</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <br>
        <?php if ($total_pages > 1): ?>
            <ul class="pagination">
                <?php if ($page > 1): ?>
                    <li><a href="?page=1<?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">Primeira</a></li>
                    <li><a href="?page=<?= ($page - 1) . (!empty($search) ? '&search=' . urlencode($search) : '') ?>">Anterior</a></li>
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
                        <li><a href="?page=<?= $i . (!empty($search) ? '&search=' . urlencode($search) : '') ?>"><?= $i ?></a></li>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <li><a href="?page=<?= ($page + 1) . (!empty($search) ? '&search=' . urlencode($search) : '') ?>">Próxima</a></li>
                    <li><a href="?page=<?= $total_pages . (!empty($search) ? '&search=' . urlencode($search) : '') ?>">Última</a></li>
                <?php else: ?>
                    <li class="disabled"><span>Próxima</span></li>
                    <li class="disabled"><span>Última</span></li>
                <?php endif; ?>
            </ul>
        <?php endif; ?>
    <?php else: ?>
        <div class="alert alert-info">
            <p>Nenhum grupo de pessoas cadastrado.</p>
            <?php if (isAdmin($current_user_grupo)): ?>
            <p><a href="grupo_pessoa.php" class="btn btn-primary mt-2">Cadastrar Grupo de Pessoas</a></p>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="mt-4">
        <a href="../index.php" class="btn btn-secondary">Voltar para a Página Inicial</a>
    </div>
</div>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>