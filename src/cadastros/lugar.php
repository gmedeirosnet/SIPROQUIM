<?php
// cadastros/lugar.php
require_once __DIR__ . '/../config/db.php';

// Set page title
// $pageTitle = $editing ? 'Editar Almoxarifado' : 'Cadastro de Almoxarifado';

// Check if editing existing record
$editing = false;
$lugar = null;
if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM lugares WHERE id = :id");
    $stmt->execute(['id' => $_GET['id']]);
    $lugar = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($lugar) {
        $editing = true;
        $pageTitle = 'Editar Almoxarifado';
    }
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = trim($_POST['nome']);
    $descricao = trim($_POST['descricao'] ?? '');

    // Validate required fields
    $errors = [];
    if (empty($nome)) {
        $errors[] = "O nome do lugar é obrigatório.";
    }

    if (empty($errors)) {
        try {
            if ($editing) {
                // Update existing record
                $sql = "UPDATE lugares SET nome = :nome, descricao = :descricao WHERE id = :id";
                $stmt = $pdo->prepare($sql);
                $result = $stmt->execute([
                    'nome' => $nome,
                    'descricao' => $descricao,
                    'id' => $_GET['id']
                ]);

                if ($result) {
                    $message = "Almoxarifado atualizado com sucesso!";
                    $messageType = "success";

                    // Refresh data
                    $stmt = $pdo->prepare("SELECT * FROM lugares WHERE id = :id");
                    $stmt->execute(['id' => $_GET['id']]);
                    $lugar = $stmt->fetch(PDO::FETCH_ASSOC);
                } else {
                    $message = "Erro ao atualizar o almoxarifado.";
                    $messageType = "error";
                }
            } else {
                // Insert new record
                $sql = "INSERT INTO lugares (nome, descricao) VALUES (:nome, :descricao)";
                $stmt = $pdo->prepare($sql);
                $result = $stmt->execute([
                    'nome' => $nome,
                    'descricao' => $descricao
                ]);

                if ($result) {
                    $message = "Almoxarifado cadastrado com sucesso!";
                    $messageType = "success";
                    $nome = '';
                    $descricao = '';
                } else {
                    $message = "Erro ao cadastrar o almoxarifado.";
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
                <label for="nome" class="form-label">Nome do Almoxarifado: <span class="required-indicator">*</span></label>
                <input type="text" name="nome" id="nome" required class="form-control"
                       value="<?= $editing ? htmlspecialchars($lugar['nome']) : (isset($nome) ? htmlspecialchars($nome) : '') ?>">
            </div>

            <div class="form-group">
                <label for="descricao" class="form-label">Descrição:</label>
                <textarea name="descricao" id="descricao" class="form-control"><?= $editing ? htmlspecialchars($lugar['descricao'] ?? '') : (isset($descricao) ? htmlspecialchars($descricao) : '') ?></textarea>
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary"><?= $editing ? 'Atualizar' : 'Cadastrar' ?></button>

                <?php if ($editing): ?>
                    <a href="list_lugares.php" class="btn btn-secondary">Cancelar</a>
                <?php else: ?>
                    <a href="../index.php" class="btn btn-secondary">Voltar para a Página Inicial</a>
                    <a href="list_lugares.php" class="btn btn-outline-primary">Ver Lista de Almoxarifados</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
