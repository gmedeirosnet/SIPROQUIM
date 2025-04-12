<?php
// cadastros/produto.php
require_once __DIR__ . '/../config/db.php';

// Check if editing existing record
$editing = false;
$produto = null;
if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM produtos WHERE id = :id");
    $stmt->execute(['id' => $_GET['id']]);
    $produto = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($produto) {
        $editing = true;
    }
}

// Get all groups for dropdown
$stmt_grupos = $pdo->query("SELECT id, nome FROM grupos ORDER BY nome");
$grupos = $stmt_grupos->fetchAll(PDO::FETCH_ASSOC);

// Get all fabricantes for dropdown
$stmt_fabricantes = $pdo->query("SELECT id, nome, cnpj FROM fabricantes ORDER BY nome");
$fabricantes = $stmt_fabricantes->fetchAll(PDO::FETCH_ASSOC);

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = trim($_POST['nome']);
    $id_grupo = !empty($_POST['id_grupo']) ? (int)$_POST['id_grupo'] : null;
    $id_fabricante = !empty($_POST['id_fabricante']) ? (int)$_POST['id_fabricante'] : null;
    $tipo = trim($_POST['tipo'] ?? '');
    $volume = trim($_POST['volume'] ?? '');
    $unidade_medida = trim($_POST['unidade_medida'] ?? '');
    $preco = !empty($_POST['preco']) ? str_replace(',', '.', $_POST['preco']) : null;
    $descricao = trim($_POST['descricao'] ?? '');

    // Validate required fields
    $errors = [];
    if (empty($nome)) {
        $errors[] = "O nome do produto é obrigatório.";
    }

    // If no validation errors, proceed with database operation
    if (empty($errors)) {
        if ($editing) {
            // Update existing record
            $sql = "UPDATE produtos SET
                    nome = :nome,
                    id_grupo = :id_grupo,
                    id_fabricante = :id_fabricante,
                    tipo = :tipo,
                    volume = :volume,
                    unidade_medida = :unidade_medida,
                    preco = :preco,
                    descricao = :descricao
                    WHERE id = :id";
            $params = [
                'nome' => $nome,
                'id_grupo' => $id_grupo,
                'id_fabricante' => $id_fabricante,
                'tipo' => $tipo,
                'volume' => $volume,
                'unidade_medida' => $unidade_medida,
                'preco' => $preco,
                'descricao' => $descricao,
                'id' => $_GET['id']
            ];

            $stmt = $pdo->prepare($sql);
            if ($stmt->execute($params)) {
                $message = "Produto atualizado com sucesso!";
                $messageType = "success";
            } else {
                $message = "Erro ao atualizar produto.";
                $messageType = "error";
            }
        } else {
            // Insert new record
            $sql = "INSERT INTO produtos (nome, id_grupo, id_fabricante, tipo, volume, unidade_medida, preco, descricao)
                    VALUES (:nome, :id_grupo, :id_fabricante, :tipo, :volume, :unidade_medida, :preco, :descricao)";
            $params = [
                'nome' => $nome,
                'id_grupo' => $id_grupo,
                'id_fabricante' => $id_fabricante,
                'tipo' => $tipo,
                'volume' => $volume,
                'unidade_medida' => $unidade_medida,
                'preco' => $preco,
                'descricao' => $descricao
            ];

            $stmt = $pdo->prepare($sql);
            if ($stmt->execute($params)) {
                $message = "Produto cadastrado com sucesso!";
                $messageType = "success";

                // Clear form fields after successful insert
                $nome = $id_grupo = $id_fabricante = $tipo = $volume = $unidade_medida = $preco = $descricao = '';
            } else {
                $message = "Erro ao cadastrar produto.";
                $messageType = "error";
            }
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
    <title><?= $editing ? 'Editar' : 'Cadastro de' ?> Produto</title>
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
        input, textarea, select {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .form-row {
            display: flex;
            gap: 15px;
        }
        .form-col {
            flex: 1;
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
        a {
            color: #007bff;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
        .required-indicator {
            color: red;
            margin-left: 3px;
        }
        .add-new-link {
            display: block;
            margin-top: 5px;
            font-size: 0.9em;
        }
    </style>
    <script>
        function formatCurrency(input) {
            let value = input.value.replace(/\D/g, '');
            if (value === '') {
                input.value = '';
                return;
            }

            value = (parseFloat(value) / 100).toFixed(2);
            input.value = value.replace('.', ',');
        }

        // Form validation
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('produto-form');
            form.addEventListener('submit', function(event) {
                const nomeField = document.getElementById('nome');

                let isValid = true;

                if (!nomeField.value.trim()) {
                    isValid = false;
                    alert('O nome do produto é obrigatório');
                    nomeField.focus();
                }

                if (!isValid) {
                    event.preventDefault();
                }
            });
        });
    </script>
</head>
<body>
    <div class="container">
        <h1><?= $editing ? 'Editar' : 'Cadastro de' ?> Produto</h1>

        <?php if (isset($message)): ?>
            <div class="message <?= $messageType ?>">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <form method="post" id="produto-form">
            <div class="form-group">
                <label for="nome">Nome do Produto: <span class="required-indicator">*</span></label>
                <input type="text" name="nome" id="nome" required
                       value="<?= $editing ? htmlspecialchars($produto['nome']) : (isset($nome) ? htmlspecialchars($nome) : '') ?>">
            </div>

            <div class="form-group">
                <label for="id_fabricante">Fabricante:</label>
                <select name="id_fabricante" id="id_fabricante">
                    <option value="">-- Selecione um Fabricante --</option>
                    <?php foreach ($fabricantes as $fab): ?>
                        <option value="<?= $fab['id'] ?>" <?= ($editing && $produto['id_fabricante'] == $fab['id']) ? 'selected' : (isset($id_fabricante) && $id_fabricante == $fab['id'] ? 'selected' : '') ?>>
                            <?= htmlspecialchars($fab['nome']) ?> (<?= htmlspecialchars($fab['cnpj']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <a href="fabricante.php" class="add-new-link" target="_blank">Adicionar novo fabricante</a>
            </div>

            <div class="form-group">
                <label for="id_grupo">Grupo:</label>
                <select name="id_grupo" id="id_grupo">
                    <option value="">-- Selecione um Grupo --</option>
                    <?php foreach ($grupos as $grupo): ?>
                        <option value="<?= $grupo['id'] ?>" <?= ($editing && $produto['id_grupo'] == $grupo['id']) ? 'selected' : (isset($id_grupo) && $id_grupo == $grupo['id'] ? 'selected' : '') ?>>
                            <?= htmlspecialchars($grupo['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <a href="grupo.php" class="add-new-link" target="__blank">Adicionar novo grupo</a>
            </div>

            <div class="form-group">
                <label for="tipo">Tipo:</label>
                <input type="text" name="tipo" id="tipo"
                       value="<?= $editing ? htmlspecialchars($produto['tipo'] ?? '') : (isset($tipo) ? htmlspecialchars($tipo) : '') ?>">
            </div>

            <div class="form-row">
                <div class="form-col">
                    <label for="volume">Volume:</label>
                    <input type="text" name="volume" id="volume"
                           value="<?= $editing ? htmlspecialchars($produto['volume'] ?? '') : (isset($volume) ? htmlspecialchars($volume) : '') ?>">
                </div>
                <div class="form-col">
                    <label for="unidade_medida">Unidade de Medida:</label>
                    <input type="text" name="unidade_medida" id="unidade_medida" placeholder="Ex: ml, L, kg"
                           value="<?= $editing ? htmlspecialchars($produto['unidade_medida'] ?? '') : (isset($unidade_medida) ? htmlspecialchars($unidade_medida) : '') ?>">
                </div>
            </div>

            <div class="form-group">
                <label for="preco">Preço (R$):</label>
                <input type="text" name="preco" id="preco" placeholder="0,00" oninput="formatCurrency(this)"
                       value="<?= $editing && isset($produto['preco']) ? str_replace('.', ',', $produto['preco']) : (isset($preco) ? str_replace('.', ',', $preco) : '') ?>">
            </div>

            <div class="form-group">
                <label for="descricao">Descrição:</label>
                <textarea name="descricao" id="descricao"><?= $editing ? htmlspecialchars($produto['descricao'] ?? '') : (isset($descricao) ? htmlspecialchars($descricao) : '') ?></textarea>
            </div>

            <input type="submit" value="<?= $editing ? 'Atualizar' : 'Cadastrar' ?>">
        </form>

        <p>
            <a href="list_produtos.php">Ver Lista de Produtos</a> |
            <a href="../index.php">Voltar para a Página Inicial</a>
        </p>
    </div>
</body>
</html>
