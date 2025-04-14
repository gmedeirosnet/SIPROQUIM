<?php
// cadastros/pessoa.php
require_once __DIR__ . '/../config/db.php';

// Set page title for the header
$pageTitle = ($editing = isset($_GET['id'])) ? 'Editar Pessoa' : 'Cadastrar Pessoa';

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
$pessoa = null;
if ($editing) {
    $stmt = $pdo->prepare("SELECT * FROM pessoas WHERE id = :id");
    $stmt->execute(['id' => $_GET['id']]);
    $pessoa = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$pessoa) {
        $editing = false;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $id_grupo_pessoa = $_POST['id_grupo_pessoa'];

    if ($editing) {
        // Update existing person
        $sql = "UPDATE pessoas SET nome = :nome, email = :email, id_grupo_pessoa = :id_grupo_pessoa WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute([
            'nome' => $nome,
            'email' => $email,
            'id_grupo_pessoa' => $id_grupo_pessoa,
            'id' => $_GET['id']
        ])) {
            $message = "Pessoa atualizada com sucesso!";
            $messageType = "success";
        } else {
            $message = "Erro ao atualizar pessoa.";
            $messageType = "error";
        }
    } else {
        // Insert new person
        $sql = "INSERT INTO pessoas (nome, email, id_grupo_pessoa) VALUES (:nome, :email, :id_grupo_pessoa)";
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute([
            'nome' => $nome,
            'email' => $email,
            'id_grupo_pessoa' => $id_grupo_pessoa
        ])) {
            $message = "Pessoa cadastrada com sucesso!";
            $messageType = "success";
        } else {
            $message = "Erro ao cadastrar pessoa.";
            $messageType = "error";
        }
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
