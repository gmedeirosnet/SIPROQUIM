<?php
// cadastros/grupo.php
require_once __DIR__ . '/../config/db.php';

// Verificação de permissões
require_once __DIR__ . '/../auth/auth_check.php';

// Set page title
$pageTitle = 'Cadastro de Grupo de Produtos';

// Check if editing existing record
$editing = false;
$grupo = null;
if (isset($_GET['id'])) {
    // Verificar permissão de leitura para edição
    requirePermission(PERMISSION_READ, $current_user_grupo);

    $stmt = $pdo->prepare("SELECT * FROM grupos WHERE id = :id");
    $stmt->execute(['id' => $_GET['id']]);
    $grupo = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($grupo) {
        $editing = true;
        $pageTitle = 'Editar Grupo de Produtos';
    }
} else {
    // Se não for edição, é criação - verificar permissão
    requirePermission(PERMISSION_CREATE, $current_user_grupo);
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verificar permissão de escrita/atualização
    if ($editing) {
        requirePermission(PERMISSION_UPDATE, $current_user_grupo);
    } else {
        requirePermission(PERMISSION_CREATE, $current_user_grupo);
    }

    $nome = trim($_POST['nome']);
    $descricao = trim($_POST['descricao'] ?? '');

    // Validate required fields
    $errors = [];
    if (empty($nome)) {
        $errors[] = "O nome do grupo é obrigatório.";
    }

    if (empty($errors)) {
        try {
            if ($editing) {
                // Update existing record
                $sql = "UPDATE grupos SET nome = :nome, descricao = :descricao WHERE id = :id";
                $stmt = $pdo->prepare($sql);
                $result = $stmt->execute([
                    'nome' => $nome,
                    'descricao' => $descricao,
                    'id' => $_GET['id']
                ]);

                if ($result) {
                    $message = "Grupo atualizado com sucesso!";
                    $messageType = "success";

                    // Refresh data
                    $stmt = $pdo->prepare("SELECT * FROM grupos WHERE id = :id");
                    $stmt->execute(['id' => $_GET['id']]);
                    $grupo = $stmt->fetch(PDO::FETCH_ASSOC);
                } else {
                    $message = "Erro ao atualizar o grupo.";
                    $messageType = "error";
                }
            } else {
                // Insert new record
                $sql = "INSERT INTO grupos (nome, descricao) VALUES (:nome, :descricao)";
                $stmt = $pdo->prepare($sql);
                $result = $stmt->execute([
                    'nome' => $nome,
                    'descricao' => $descricao
                ]);

                if ($result) {
                    $message = "Grupo cadastrado com sucesso!";
                    $messageType = "success";
                    $nome = '';
                    $descricao = '';
                } else {
                    $message = "Erro ao cadastrar o grupo.";
                    $messageType = "error";
                }
            }
        } catch (PDOException $e) {
            $message = "Erro no banco de dados: " . $e->getMessage();
            $messageType = "error";
        }
    } else {
        $message = implode("<br>", $errors);
        $messageType = "error";
    }
}

// Include the header
include_once __DIR__ . '/../includes/header.php';
?>

<div class="content">
    <div class="container">
        <?php if (isset($message)): ?>
            <div class="alert alert-<?= $messageType === 'error' ? 'danger' : 'success' ?>">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <form method="post" class="form">
            <div class="form-group">
                <label for="nome" class="form-label">Nome do Grupo: <span class="required-indicator">*</span></label>
                <input type="text" name="nome" id="nome" required class="form-control"
                       value="<?= $editing ? htmlspecialchars($grupo['nome']) : (isset($nome) ? htmlspecialchars($nome) : '') ?>">
            </div>

            <div class="form-group">
                <label for="descricao" class="form-label">Descrição:</label>
                <?php
                if ($editing) {
                    $descricao_value = htmlspecialchars($grupo['descricao'] ?? '');
                } elseif (isset($descricao)) {
                    $descricao_value = htmlspecialchars($descricao);
                } else {
                    $descricao_value = '';
                }
                ?>
                <textarea name="descricao" id="descricao" class="form-control"><?= $descricao_value ?></textarea>
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary"><?= $editing ? 'Atualizar' : 'Cadastrar' ?></button>

                <?php if ($editing): ?>
                    <a href="list_grupos.php" class="btn btn-secondary">Cancelar</a>
                <?php else: ?>
                    <a href="../index.php" class="btn btn-secondary">Voltar para a Página Inicial</a>
                    <a href="list_grupos.php" class="btn btn-outline-primary">Ver Lista de Grupos</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
