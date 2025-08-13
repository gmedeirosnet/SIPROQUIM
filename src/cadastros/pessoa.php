<?php
// cadastros/pessoa.php
require_once __DIR__ . '/../config/db.php';

// Verificação de permissões
require_once __DIR__ . '/../auth/auth_check.php';

// Verificar se o usuário é administrador para realizar operações em pessoas
requireAdmin($current_user_grupo);

// Set page title for the header
$pageTitle = 'Cadastro de Pessoa';

// Fetch person groups for dropdown
$stmt_grupos = $pdo->query("SELECT id, nome FROM grupos_pessoas ORDER BY nome");
$grupos = $stmt_grupos->fetchAll(PDO::FETCH_ASSOC);

// Get default group ID (Usuários)
$default_grupo_id = null;
foreach ($grupos as $grupo) {
    if ($grupo['nome'] == 'Usuários') {
        $default_grupo_id = $grupo['id'];
        break;
    }
}

// Check if editing existing record
$editing = false;
$pessoa = null;
if (isset($_GET['id'])) {
    // Verificar permissão de leitura para edição
    requirePermission(PERMISSION_READ, $current_user_grupo);

    $stmt = $pdo->prepare("SELECT * FROM pessoas WHERE id = :id");
    $stmt->execute(['id' => $_GET['id']]);
    $pessoa = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($pessoa) {
        $editing = true;
        $pageTitle = 'Editar Pessoa';
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

    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $id_grupo_pessoa = $_POST['id_grupo_pessoa'];
    $enable = isset($_POST['enable']) ? true : false;
    $password = $_POST['password'] ?? '';

    // Validate required fields
    $errors = [];
    if (empty($nome)) {
        $errors[] = "O nome da pessoa é obrigatório.";
    }
    if (empty($id_grupo_pessoa)) {
        $errors[] = "O grupo da pessoa é obrigatório.";
    }

    // If no validation errors, proceed with database operation
    if (empty($errors)) {
        // Se estiver editando e a senha estiver vazia, mantenha a senha atual
        $passwordSql = '';
        $params = [
            'nome' => $nome,
            'email' => $email,
            'id_grupo_pessoa' => $id_grupo_pessoa,
            'enable' => $enable
        ];

        if (!empty($password)) {
            // Hash da senha
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $passwordSql = ', password = :password';
            $params['password'] = $passwordHash;
        }

        if ($editing) {
            // Update existing person
            $sql = "UPDATE pessoas SET nome = :nome, email = :email, id_grupo_pessoa = :id_grupo_pessoa, enable = :enable$passwordSql WHERE id = :id";
            $params['id'] = $_GET['id'];
            $stmt = $pdo->prepare($sql);
            if ($stmt->execute($params)) {
                $message = "Pessoa atualizada com sucesso!";
                $messageType = "success";
            } else {
                $message = "Erro ao atualizar pessoa.";
                $messageType = "error";
            }
        } else {
            // Insert new person - require password for new users
            if (empty($password)) {
                $message = "A senha é obrigatória para novos usuários.";
                $messageType = "error";
            } else {
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                $sql = "INSERT INTO pessoas (nome, email, id_grupo_pessoa, enable, password) VALUES (:nome, :email, :id_grupo_pessoa, :enable, :password)";
                $stmt = $pdo->prepare($sql);
                if ($stmt->execute([
                    'nome' => $nome,
                    'email' => $email,
                    'id_grupo_pessoa' => $id_grupo_pessoa,
                    'enable' => $enable,
                    'password' => $passwordHash
                ])) {
                    $message = "Pessoa cadastrada com sucesso!";
                    $messageType = "success";
                } else {
                    $message = "Erro ao cadastrar pessoa.";
                    $messageType = "error";
                }
            }
        }
    } else {
        // Display validation errors
        $message = implode('<br>', $errors);
        $messageType = "error";
    }
}

// Include the header
include_once __DIR__ . '/../includes/header.php';
?>

<div class="content">
    <h2 class="section-title"><?= $editing ? 'Editar' : 'Cadastro de' ?> Pessoa</h2>

    <?php if (isset($message)): ?>
        <div class="alert <?= $messageType === 'success' ? 'alert-success' : 'alert-danger' ?>">
            <?= $message ?>
        </div>
    <?php endif; ?>

    <form method="post" class="form">
        <div class="form-group">
            <label for="nome" class="form-label">Nome: <span class="text-danger">*</span></label>
            <input type="text" name="nome" id="nome" class="form-control" required
                   value="<?= $editing ? htmlspecialchars($pessoa['nome']) : '' ?>">
        </div>

        <div class="form-group">
            <label for="email" class="form-label">Email:</label>
            <input type="email" name="email" id="email" class="form-control"
                   value="<?= $editing ? htmlspecialchars($pessoa['email'] ?? '') : '' ?>">
        </div>

        <div class="form-group">
            <label for="id_grupo_pessoa" class="form-label">Grupo: <span class="text-danger">*</span></label>
            <select name="id_grupo_pessoa" id="id_grupo_pessoa" class="form-control" required>
                <option value="">Selecione um grupo</option>
                <?php foreach ($grupos as $grupo): ?>
                    <option value="<?= $grupo['id'] ?>" <?=
                        ($editing && $pessoa['id_grupo_pessoa'] == $grupo['id']) ||
                        (!$editing && $default_grupo_id == $grupo['id'])
                            ? 'selected' : ''
                    ?>>
                        <?= htmlspecialchars($grupo['nome']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="enable" class="form-label">Enable:</label>
            <input type="checkbox" name="enable" id="enable" class="form-check-input"
                   <?= $editing && $pessoa['enable'] ? 'checked' : '' ?>>
        </div>

        <div class="form-group">
            <label for="password" class="form-label">Senha:<?php if (!$editing): ?> <span class="text-danger">*</span><?php endif; ?></label>
            <input type="password" name="password" id="password" class="form-control" 
                   <?php if (!$editing): ?>required<?php endif; ?>
                   placeholder="<?= $editing ? 'Deixe em branco para manter a senha atual' : 'Digite a senha' ?>">
        </div>

        <div class="btn-group mt-4">
            <button type="submit" class="btn btn-primary"><?= $editing ? 'Atualizar' : 'Cadastrar' ?></button>

            <?php if ($editing): ?>
                <a href="list_pessoas.php" class="btn btn-secondary">Cancelar</a>
            <?php else: ?>
                <a href="list_pessoas.php" class="btn btn-outline-primary">Ver todas as pessoas</a>
                <a href="../index.php" class="btn btn-secondary">Voltar para a Página Inicial</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
