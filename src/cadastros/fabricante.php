<?php
// cadastros/fabricante.php
require_once __DIR__ . '/../config/db.php';

// Set page title for the header
$pageTitle = ($editing = isset($_GET['id'])) ? 'Editar Fabricante' : 'Cadastrar Fabricante';

// Check if editing existing record
$fabricante = null;
if ($editing) {
    $stmt = $pdo->prepare("SELECT * FROM fabricantes WHERE id = :id");
    $stmt->execute(['id' => $_GET['id']]);
    $fabricante = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$fabricante) {
        $editing = false;
    }
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = trim($_POST['nome']);
    $cnpj = trim($_POST['cnpj']);
    $endereco = trim($_POST['endereco'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $observacao = trim($_POST['observacao'] ?? '');

    // Validate required fields
    $errors = [];
    if (empty($nome)) {
        $errors[] = "O nome do fabricante é obrigatório.";
    }
    if (empty($cnpj)) {
        $errors[] = "O CNPJ é obrigatório.";
    }

    // If no validation errors, proceed with database operation
    if (empty($errors)) {
        if ($editing) {
            // Update existing record
            $sql = "UPDATE fabricantes SET
                    nome = :nome,
                    cnpj = :cnpj,
                    endereco = :endereco,
                    email = :email,
                    observacao = :observacao
                    WHERE id = :id";
            $params = [
                'nome' => $nome,
                'cnpj' => $cnpj,
                'endereco' => $endereco,
                'email' => $email,
                'observacao' => $observacao,
                'id' => $_GET['id']
            ];

            $stmt = $pdo->prepare($sql);
            if ($stmt->execute($params)) {
                $message = "Fabricante atualizado com sucesso!";
                $messageType = "success";
            } else {
                $message = "Erro ao atualizar fabricante.";
                $messageType = "error";
            }
        } else {
            // Check if CNPJ already exists
            $check = $pdo->prepare("SELECT id FROM fabricantes WHERE cnpj = :cnpj");
            $check->execute(['cnpj' => $cnpj]);
            if ($check->fetchColumn()) {
                $message = "Este CNPJ já está cadastrado.";
                $messageType = "error";
            } else {
                // Insert new record
                $sql = "INSERT INTO fabricantes (nome, cnpj, endereco, email, observacao)
                        VALUES (:nome, :cnpj, :endereco, :email, :observacao)";
                $params = [
                    'nome' => $nome,
                    'cnpj' => $cnpj,
                    'endereco' => $endereco,
                    'email' => $email,
                    'observacao' => $observacao
                ];

                $stmt = $pdo->prepare($sql);
                if ($stmt->execute($params)) {
                    $message = "Fabricante cadastrado com sucesso!";
                    $messageType = "success";

                    // Clear form fields after successful insert
                    $nome = $cnpj = $endereco = $email = $observacao = '';
                } else {
                    $message = "Erro ao cadastrar fabricante.";
                    $messageType = "error";
                }
            }
        }
    } else {
        // Display validation errors
        $message = implode("<br>", $errors);
        $messageType = "error";
    }
}

// Include the header
include_once __DIR__ . '/../includes/header.php';
?>

<div class="content">
    <h2 class="section-title"><?= $editing ? 'Editar' : 'Cadastro de' ?> Fabricante</h2>

    <?php if (isset($message)): ?>
        <div class="alert <?= $messageType === 'success' ? 'alert-success' : 'alert-danger' ?>">
            <?= $message ?>
        </div>
    <?php endif; ?>

    <form method="post" id="fabricante-form" class="form">
        <div class="form-group">
            <label for="nome" class="form-label">Fabricante: <span class="text-danger">*</span></label>
            <input type="text" name="nome" id="nome" class="form-control" required
                   value="<?= $editing ? htmlspecialchars($fabricante['nome']) : (isset($nome) ? htmlspecialchars($nome) : '') ?>">
        </div>

        <div class="form-group">
            <label for="cnpj" class="form-label">CNPJ: <span class="text-danger">*</span></label>
            <input type="text" name="cnpj" id="cnpj" class="form-control" oninput="formatCNPJ(this)" required maxlength="18"
                   value="<?= $editing ? htmlspecialchars($fabricante['cnpj']) : (isset($cnpj) ? htmlspecialchars($cnpj) : '') ?>">
        </div>

        <div class="form-group">
            <label for="endereco" class="form-label">Endereço:</label>
            <input type="text" name="endereco" id="endereco" class="form-control"
                   value="<?= $editing ? htmlspecialchars($fabricante['endereco'] ?? '') : (isset($endereco) ? htmlspecialchars($endereco) : '') ?>">
        </div>

        <div class="form-group">
            <label for="email" class="form-label">E-mail:</label>
            <input type="email" name="email" id="email" class="form-control"
                   value="<?= $editing ? htmlspecialchars($fabricante['email'] ?? '') : (isset($email) ? htmlspecialchars($email) : '') ?>">
        </div>

        <div class="form-group">
            <label for="observacao" class="form-label">Observação:</label>
            <textarea name="observacao" id="observacao" class="form-control"><?= $editing ? htmlspecialchars($fabricante['observacao'] ?? '') : (isset($observacao) ? htmlspecialchars($observacao) : '') ?></textarea>
        </div>

        <div class="btn-group mt-4">
            <button type="submit" class="btn btn-primary"><?= $editing ? 'Atualizar' : 'Cadastrar' ?></button>

            <?php if ($editing): ?>
                <a href="list_fabricantes.php" class="btn btn-secondary">Cancelar</a>
            <?php else: ?>
                <a href="list_fabricantes.php" class="btn btn-outline-primary">Ver Lista de Fabricantes</a>
                <a href="../index.php" class="btn btn-secondary">Voltar para a Página Inicial</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<script>
    // CNPJ formatting
    function formatCNPJ(input) {
        let value = input.value.replace(/\D/g, '');
        if (value.length > 14) {
            value = value.slice(0, 14);
        }

        if (value.length > 12) {
            value = value.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2}).*/, '$1.$2.$3/$4-$5');
        } else if (value.length > 8) {
            value = value.replace(/^(\d{2})(\d{3})(\d{3})(\d*).*/, '$1.$2.$3/$4');
        } else if (value.length > 5) {
            value = value.replace(/^(\d{2})(\d{3})(\d*).*/, '$1.$2.$3');
        } else if (value.length > 2) {
            value = value.replace(/^(\d{2})(\d*).*/, '$1.$2');
        }

        input.value = value;
    }
</script>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>