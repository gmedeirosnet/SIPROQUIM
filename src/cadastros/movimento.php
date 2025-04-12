<?php
// cadastros/movimento.php
require_once __DIR__ . '/../config/db.php';

// Buscar produtos, pessoas e lugares para preencher os selects
$produtos = $pdo->query("SELECT id, nome FROM produtos ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);
$pessoas  = $pdo->query("SELECT id, nome FROM pessoas ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);
$lugares  = $pdo->query("SELECT id, nome FROM lugares ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $id_produto  = $_POST['id_produto'] ?? '';
    $id_pessoa   = $_POST['id_pessoa'] ?? '';
    $id_lugar    = $_POST['id_lugar'] ?? '';
    $tipo        = $_POST['tipo'] ?? '';
    $quantidade  = $_POST['quantidade'] ?? '';
    $data_movimento = $_POST['data_movimento'] ?? date('Y-m-d H:i:s');
    $observacao  = $_POST['observacao'] ?? '';

    // Validate required fields
    $errors = [];
    if (empty($id_produto)) {
        $errors[] = "O produto é obrigatório.";
    }
    if (empty($id_pessoa)) {
        $errors[] = "A pessoa é obrigatória.";
    }
    if (empty($id_lugar)) {
        $errors[] = "O lugar é obrigatório.";
    }
    if (empty($tipo)) {
        $errors[] = "O tipo de movimentação é obrigatório.";
    }
    if (empty($quantidade) || !is_numeric($quantidade) || $quantidade <= 0) {
        $errors[] = "A quantidade deve ser um número maior que zero.";
    }

    if (empty($errors)) {
        try {
            // Format the date if it's not already in the correct format
            if (!empty($data_movimento)) {
                $data_movimento = date('Y-m-d H:i:s', strtotime($data_movimento));
            } else {
                $data_movimento = date('Y-m-d H:i:s'); // Use current date/time if empty
            }

            $sql = "INSERT INTO movimentos (id_produto, id_pessoa, id_lugar, tipo, quantidade, data_movimento, observacao)
                    VALUES (:id_produto, :id_pessoa, :id_lugar, :tipo, :quantidade, :data_movimento, :observacao)";
            $stmt = $pdo->prepare($sql);
            if ($stmt->execute([
                'id_produto' => $id_produto,
                'id_pessoa'  => $id_pessoa,
                'id_lugar'   => $id_lugar,
                'tipo'       => $tipo,
                'quantidade' => $quantidade,
                'data_movimento' => $data_movimento,
                'observacao' => $observacao
            ])) {
                $message = "Movimentação registrada com sucesso!";
                $messageType = "success";

                // Clear form data after successful submission
                $id_produto = $id_pessoa = $id_lugar = '';
                $quantidade = '';
                $observacao = '';
                // Keep the default tipo selection
            } else {
                $message = "Erro ao registrar movimentação.";
                $messageType = "error";
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
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Movimentação de Produtos</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 5px;
        }
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        select, input, textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
            font-family: inherit;
            font-size: 14px;
        }
        textarea {
            height: 100px;
            resize: vertical;
        }
        .form-row {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }
        .form-col {
            flex: 1;
        }
        .tipo-options {
            display: flex;
            gap: 20px;
        }
        .tipo-option {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .tipo-option input[type="radio"] {
            width: auto;
        }
        input[type="submit"] {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 12px;
            cursor: pointer;
            font-weight: bold;
            margin-top: 10px;
            border-radius: 4px;
        }
        input[type="submit"]:hover {
            background-color: #0056b3;
        }
        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .buttons {
            margin-top: 20px;
            display: flex;
            gap: 10px;
            justify-content: space-between;
        }
        .btn {
            display: inline-block;
            padding: 10px 15px;
            background-color: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 14px;
        }
        .btn-primary {
            background-color: #007bff;
        }
        .required {
            color: #ff0000;
            margin-left: 3px;
        }
        .tipo-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 15px;
            font-weight: bold;
            margin-left: 10px;
        }
        .entrada {
            background-color: #d4edda;
            color: #155724;
        }
        .saida {
            background-color: #f8d7da;
            color: #721c24;
        }
        .tipo-selector {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }
        .tipo-card {
            flex: 1;
            border: 2px solid #6c757d; /* Grey border */
            background-color: #f8f9fa; /* Light grey background */
            border-radius: 5px;
            padding: 15px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .tipo-card:hover {
            transform: translateY(-3px);
        }
        .tipo-card.selected {
            border-color: #007bff;
            box-shadow: 0 0 10px rgba(0, 123, 255, 0.3);
        }
        .tipo-card.entrada.selected {
            background-color: #d4edda;
            border-color: #28a745;
            box-shadow: 0 0 10px rgba(40, 167, 69, 0.3);
        }
        .tipo-card.saida.selected {
            background-color: #f8d7da;
            border-color: #dc3545;
            box-shadow: 0 0 10px rgba(220, 53, 69, 0.3);
        }
        .tipo-card i {
            font-size: 24px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Registro de Movimentação de Produtos</h1>

        <?php if (isset($message)): ?>
            <div class="message <?= $messageType ?>">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <form method="post">
            <div class="form-group">
                <label for="id_produto">Produto: <span class="required">*</span></label>
                <select name="id_produto" id="id_produto" required>
                    <option value="">Selecione um produto</option>
                    <?php foreach ($produtos as $produto): ?>
                    <option value="<?= $produto['id'] ?>" <?= isset($id_produto) && $id_produto == $produto['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($produto['nome']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-row">
                <div class="form-col">
                    <label for="id_pessoa">Pessoa responsável: <span class="required">*</span></label>
                    <select name="id_pessoa" id="id_pessoa" required>
                        <option value="">Selecione uma pessoa</option>
                        <?php foreach ($pessoas as $pessoa): ?>
                        <option value="<?= $pessoa['id'] ?>" <?= isset($id_pessoa) && $id_pessoa == $pessoa['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($pessoa['nome']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-col">
                    <label for="id_lugar">Lugar de Estoque: <span class="required">*</span></label>
                    <select name="id_lugar" id="id_lugar" required>
                        <option value="">Selecione um local</option>
                        <?php foreach ($lugares as $lugar): ?>
                        <option value="<?= $lugar['id'] ?>" <?= isset($id_lugar) && $id_lugar == $lugar['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($lugar['nome']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Tipo de Movimentação: <span class="required">*</span></label>
                <div class="tipo-selector">
                    <div class="tipo-card entrada <?= (!isset($tipo) || (isset($tipo) && $tipo == 'entrada')) ? 'selected' : '' ?>" onclick="selectTipo('entrada')">
                        <i>➕</i>
                        <h3>Entrada</h3>
                        <p>Adicionar itens ao estoque</p>
                    </div>
                    <div class="tipo-card saida <?= (isset($tipo) && $tipo == 'saida') ? 'selected' : '' ?>" onclick="selectTipo('saida')">
                        <i>➖</i>
                        <h3>Saída</h3>
                        <p>Remover itens do estoque</p>
                    </div>
                </div>
                <input type="hidden" name="tipo" id="tipo" value="<?= isset($tipo) ? $tipo : 'entrada' ?>">
            </div>

            <div class="form-row">
                <div class="form-col">
                    <label for="quantidade">Quantidade: <span class="required">*</span></label>
                    <input type="number" name="quantidade" id="quantidade" min="1" value="<?= isset($quantidade) ? htmlspecialchars($quantidade) : '1' ?>" required>
                </div>
                <div class="form-col">
                    <label for="data_movimento">Data da Movimentação:</label>
                    <input type="datetime-local" name="data_movimento" id="data_movimento" value="<?= date('Y-m-d\TH:i') ?>">
                </div>
            </div>

            <div class="form-group">
                <label for="observacao">Observação:</label>
                <textarea name="observacao" id="observacao"><?= isset($observacao) ? htmlspecialchars($observacao) : '' ?></textarea>
            </div>

            <div class="buttons">
                <a href="../index.php" class="btn">Cancelar</a>
                <input type="submit" value="Registrar Movimentação" class="btn-primary">
            </div>
        </form>

        <div class="buttons" style="margin-top: 30px;">
            <a href="../relatorios/relatorio_movimentos.php" class="btn">Ver Relatório de Movimentações</a>
            <a href="../relatorios/relatorio_estoque.php" class="btn">Ver Relatório de Estoque</a>
        </div>
    </div>

    <script>
        function selectTipo(tipo) {
            // Update hidden input
            document.getElementById('tipo').value = tipo;

            // Update visual selection
            if (tipo === 'entrada') {
                document.querySelector('.tipo-card.entrada').classList.add('selected');
                document.querySelector('.tipo-card.saida').classList.remove('selected');
            } else {
                document.querySelector('.tipo-card.entrada').classList.remove('selected');
                document.querySelector('.tipo-card.saida').classList.add('selected');
            }
        }

        // Set current date and time in the datetime-local input
        document.addEventListener('DOMContentLoaded', function() {
            var now = new Date();
            var year = now.getFullYear();
            var month = (now.getMonth() + 1).toString().padStart(2, '0');
            var day = now.getDate().toString().padStart(2, '0');
            var hours = now.getHours().toString().padStart(2, '0');
            var minutes = now.getMinutes().toString().padStart(2, '0');

            var formattedDateTime = `${year}-${month}-${day}T${hours}:${minutes}`;
            document.getElementById('data_movimento').value = formattedDateTime;
        });
    </script>
</body>
</html>
