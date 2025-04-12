<?php
// cadastros/pessoa.php
require_once __DIR__ . '/../config/db.php';

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
    $stmt = $pdo->prepare("SELECT * FROM pessoas WHERE id = :id");
    $stmt->execute(['id' => $_GET['id']]);
    $pessoa = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($pessoa) {
        $editing = true;
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
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $editing ? 'Editar' : 'Cadastro de' ?> Pessoa</title>
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
        input, select {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
            border: 1px solid #ddd;
            border-radius: 4px;
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
        .btn-link {
            background: none;
            color: #007bff;
            text-decoration: underline;
            border: none;
            padding: 0;
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
    </style>
</head>
<body>
    <div class="container">
        <h1><?= $editing ? 'Editar' : 'Cadastro de' ?> Pessoa</h1>

        <?php if (isset($message)): ?>
            <div class="message <?= $messageType ?>">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <form method="post">
            <div class="form-group">
                <label for="nome">Nome:</label>
                <input type="text" name="nome" id="nome" required value="<?= $editing ? htmlspecialchars($pessoa['nome']) : '' ?>">
            </div>

            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" name="email" id="email" value="<?= $editing ? htmlspecialchars($pessoa['email'] ?? '') : '' ?>">
            </div>

            <div class="form-group">
                <label for="id_grupo_pessoa">Grupo:</label>
                <select name="id_grupo_pessoa" id="id_grupo_pessoa" required>
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

            <input type="submit" value="<?= $editing ? 'Atualizar' : 'Cadastrar' ?>">
        </form>

        <div>
            <?php if ($editing): ?>
                <a href="list_pessoas.php" class="btn">Cancelar</a>
            <?php else: ?>
                <a href="../index.php" class="btn">Voltar para a Página Inicial</a>
                <a href="list_pessoas.php" class="btn btn-link">Ver todas as pessoas</a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
