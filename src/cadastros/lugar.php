<?php
// cadastros/lugar.php
require_once __DIR__ . '/../config/db.php';

// Check if editing existing record
$editing = false;
$lugar = null;
if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM lugares WHERE id = :id");
    $stmt->execute(['id' => $_GET['id']]);
    $lugar = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($lugar) {
        $editing = true;
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
                if ($stmt->execute([
                    'nome' => $nome,
                    'descricao' => $descricao,
                    'id' => $_GET['id']
                ])) {
                    $message = "Lugar atualizado com sucesso!";
                    $messageType = "success";
                } else {
                    $message = "Erro ao atualizar lugar.";
                    $messageType = "error";
                }
            } else {
                // Check if name already exists
                $check = $pdo->prepare("SELECT id FROM lugares WHERE nome = :nome");
                $check->execute(['nome' => $nome]);
                if ($check->fetchColumn()) {
                    $message = "Já existe um lugar com este nome.";
                    $messageType = "error";
                } else {
                    // Insert new record
                    $sql = "INSERT INTO lugares (nome, descricao) VALUES (:nome, :descricao)";
                    $stmt = $pdo->prepare($sql);
                    if ($stmt->execute([
                        'nome' => $nome,
                        'descricao' => $descricao
                    ])) {
                        $message = "Lugar cadastrado com sucesso!";
                        $messageType = "success";

                        // Clear form fields after successful insert
                        $nome = $descricao = '';
                    } else {
                        $message = "Erro ao cadastrar lugar.";
                        $messageType = "error";
                    }
                }
            }
        } catch (PDOException $e) {
            $message = "Erro no banco de dados: " . $e->getMessage();
            $messageType = "error";
        }
    } else {
        // Display validation errors
        $message = implode("<br>", $errors);
        $messageType = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $editing ? 'Editar' : 'Cadastro de' ?> Lugar de Estoque</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #333;
            text-align: center;
        }
        form {
            margin-top: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input, textarea {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        textarea {
            height: 100px;
            resize: vertical;
        }
        input[type="submit"] {
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
            padding: 10px;
        }
        input[type="submit"]:hover {
            background-color: #0056b3;
        }
        .message {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
        }
        .btn {
            display: inline-block;
            padding: 8px 12px;
            background-color: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 10px;
        }
        .navigation {
            margin-top: 20px;
            display: flex;
            gap: 10px;
        }
        .navigation a {
            color: #007bff;
            text-decoration: none;
        }
        .navigation a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><?= $editing ? 'Editar' : 'Cadastro de' ?> Lugar de Estoque</h1>

        <?php if (isset($message)): ?>
            <div class="message <?= $messageType ?>">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <form method="post">
            <div class="form-group">
                <label for="nome">Nome do Lugar: <span style="color: red;">*</span></label>
                <input type="text" name="nome" id="nome" required
                       value="<?= $editing ? htmlspecialchars($lugar['nome']) : (isset($nome) ? htmlspecialchars($nome) : '') ?>">
            </div>

            <div class="form-group">
                <label for="descricao">Descrição:</label>
                <textarea name="descricao" id="descricao"><?= $editing ? htmlspecialchars($lugar['descricao'] ?? '') : (isset($descricao) ? htmlspecialchars($descricao) : '') ?></textarea>
            </div>

            <input type="submit" value="<?= $editing ? 'Atualizar' : 'Cadastrar' ?>">
        </form>

        <div class="navigation">
            <?php if ($editing): ?>
                <a href="list_lugares.php">Cancelar</a>
            <?php else: ?>
                <a href="list_lugares.php">Ver Lista de Lugares</a> |
                <a href="../index.php">Voltar para a Página Inicial</a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
