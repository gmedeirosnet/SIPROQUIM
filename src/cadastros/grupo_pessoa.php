<?php
// cadastros/grupo_pessoa.php
require_once __DIR__ . '/../config/db.php';

// Check if editing existing record
$editing = false;
$grupo = null;

if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM grupos_pessoas WHERE id = :id");
    $stmt->execute(['id' => $_GET['id']]);
    $grupo = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($grupo) {
        $editing = true;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
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
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $editing ? 'Editar' : 'Cadastro de' ?> Grupo de Pessoas</title>
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
            margin-top: 10px;
        }
        input[type="submit"]:hover {
            background-color: #0056b3;
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
        .buttons {
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><?= $editing ? 'Editar' : 'Cadastro de' ?> Grupo de Pessoas</h1>

        <?php if (isset($message)): ?>
            <div class="message <?= $messageType ?>">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="message error">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="post">
            <div class="form-group">
                <label for="nome">Nome:</label>
                <input type="text" name="nome" id="nome" required
                       value="<?= isset($nome) ? htmlspecialchars($nome) : ($editing ? htmlspecialchars($grupo['nome']) : '') ?>">
            </div>

            <div class="form-group">
                <label for="descricao">Descrição:</label>
                <textarea name="descricao" id="descricao"><?= isset($descricao) ? htmlspecialchars($descricao) : ($editing ? htmlspecialchars($grupo['descricao'] ?? '') : '') ?></textarea>
            </div>

            <div class="buttons">
                <input type="submit" value="<?= $editing ? 'Atualizar' : 'Cadastrar' ?>">
                <?php if ($editing): ?>
                    <a href="list_grupos_pessoas.php" class="btn">Cancelar</a>
                <?php else: ?>
                    <a href="list_grupos_pessoas.php" class="btn">Ver Todos os Grupos</a>
                <?php endif; ?>
            </div>
        </form>

        <?php if (!$editing): ?>
            <p>
                <a href="../index.php">Voltar para a Página Inicial</a>
            </p>
        <?php endif; ?>
    </div>
</body>
</html>