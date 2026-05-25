<?php
// cadastros/grupo_pessoa.php
require_once __DIR__ . '/../config/db.php';

// Verificação de permissões
require_once __DIR__ . '/../auth/auth_check.php';

// Verificar se o usuário é administrador para operações em grupos de pessoas
requireAdmin($current_user_grupo);

// Set page title
$pageTitle = 'Cadastro de Grupo de Pessoas';

// Check if editing existing record
$editing = false;
$grupo = null;
if (isset($_GET['id'])) {
    // Verificar permissão de leitura para edição
    requirePermission(PERMISSION_READ, $current_user_grupo);

    $stmt = $pdo->prepare("SELECT * FROM grupos_pessoas WHERE id = :id");
    $stmt->execute(['id' => $_GET['id']]);
    $grupo = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($grupo) {
        $editing = true;
        $pageTitle = 'Editar Grupo de Pessoas';
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
    $descricao = trim($_POST['descricao']);

    // Basic validation
    if (empty($nome)) {
        $error = "O nome do grupo é obrigatório.";
    } else {
        // Check if the name already exists (for new records only)
        if (!$editing) {
            $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM grupos_pessoas WHERE nome = :nome");
            $stmt_check->execute(['nome' => $nome]);
            if ($stmt_check->fetchColumn() > 0) {
                $error = "Já existe um grupo com este nome.";
            }
        }

        if (!isset($error)) {
            try {
                if ($editing) {
                    // Update existing group
                    $sql = "UPDATE grupos_pessoas SET nome = :nome, descricao = :descricao WHERE id = :id";
                    $stmt = $pdo->prepare($sql);
                    if ($stmt->execute([
                        'nome' => $nome,
                        'descricao' => $descricao,
                        'id' => $_GET['id']
                    ])) {
                        $message = "Grupo atualizado com sucesso!";
                        $messageType = "success";
                    } else {
                        $error = "Erro ao atualizar grupo.";
                    }
                } else {
                    // Insert new group
                    $sql = "INSERT INTO grupos_pessoas (nome, descricao) VALUES (:nome, :descricao)";
                    $stmt = $pdo->prepare($sql);
                    if ($stmt->execute([
                        'nome' => $nome,
                        'descricao' => $descricao
                    ])) {
                        $message = "Grupo cadastrado com sucesso!";
                        $messageType = "success";
                        // Clear form after successful submission
                        $nome = $descricao = '';
                    } else {
                        $error = "Erro ao cadastrar grupo.";
                    }
                }
            } catch (PDOException $e) {
                $error = "Erro no banco de dados: " . $e->getMessage();
            }
        }
    }
}

// Include the header
include_once __DIR__ . '/../includes/header.php';
?>

<div class="content">
    <h2 class="section-title"><?= $editing ? 'Editar' : 'Cadastro de' ?> Grupo de Pessoas</h2>

    <?php if (isset($message)): ?>
        <div class="alert <?= $messageType === 'success' ? 'alert-success' : 'alert-danger' ?>">
            <?= $message ?>
        </div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger">
            <?= $error ?>
        </div>
    <?php endif; ?>

    <form method="post" class="form">
        <div class="form-group">
            <label for="nome" class="form-label">Nome: <span class="text-danger">*</span></label>
            <input type="text" name="nome" id="nome" class="form-control" required
                   value="<?= isset($nome) ? htmlspecialchars($nome) : ($editing ? htmlspecialchars($grupo['nome']) : '') ?>">
        </div>

        <div class="form-group">
            <label for="descricao" class="form-label">Descrição:</label>
            <?php
            if (isset($descricao)) {
                $descricao_value = htmlspecialchars($descricao);
            } elseif ($editing) {
                $descricao_value = htmlspecialchars($grupo['descricao'] ?? '');
            } else {
                $descricao_value = '';
            }
            ?>
            <textarea name="descricao" id="descricao" class="form-control"><?= $descricao_value ?></textarea>
        </div>

        <div class="btn-group mt-4">
            <button type="submit" class="btn btn-primary"><?= $editing ? 'Atualizar' : 'Cadastrar' ?></button>

            <?php if ($editing): ?>
                <a href="list_grupos_pessoas.php" class="btn btn-secondary">Cancelar</a>
            <?php else: ?>
                <a href="list_grupos_pessoas.php" class="btn btn-outline-primary">Ver Todos os Grupos</a>
                <a href="../index.php" class="btn btn-secondary">Voltar para a Página Inicial</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
