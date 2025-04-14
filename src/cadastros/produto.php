<?php
// cadastros/produto.php
require_once __DIR__ . '/../config/db.php';

// Set page title
$pageTitle = 'Cadastro de Produto';

// Check if editing existing record
$editing = false;
$produto = null;
if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM produtos WHERE id = :id");
    $stmt->execute(['id' => $_GET['id']]);
    $produto = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($produto) {
        $editing = true;
        $pageTitle = 'Editar Produto';
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
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute([
                'nome' => $nome,
                'id_grupo' => $id_grupo,
                'id_fabricante' => $id_fabricante,
                'tipo' => $tipo,
                'volume' => $volume,
                'unidade_medida' => $unidade_medida,
                'preco' => $preco,
                'descricao' => $descricao,
                'id' => $_GET['id']
            ]);

            if ($result) {
                $message = "Produto atualizado com sucesso!";
                $messageType = "success";

                // Refresh data
                $stmt = $pdo->prepare("SELECT * FROM produtos WHERE id = :id");
                $stmt->execute(['id' => $_GET['id']]);
                $produto = $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                $message = "Erro ao atualizar o produto.";
                $messageType = "error";
            }
        } else {
            // Insert new record
            $sql = "INSERT INTO produtos (nome, id_grupo, id_fabricante, tipo, volume, unidade_medida, preco, descricao)
                    VALUES (:nome, :id_grupo, :id_fabricante, :tipo, :volume, :unidade_medida, :preco, :descricao)";
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute([
                'nome' => $nome,
                'id_grupo' => $id_grupo,
                'id_fabricante' => $id_fabricante,
                'tipo' => $tipo,
                'volume' => $volume,
                'unidade_medida' => $unidade_medida,
                'preco' => $preco,
                'descricao' => $descricao
            ]);

            if ($result) {
                $message = "Produto cadastrado com sucesso!";
                $messageType = "success";
                // Clear form fields
                $nome = $tipo = $volume = $unidade_medida = $descricao = '';
                $id_grupo = $id_fabricante = null;
                $preco = '';
            } else {
                $message = "Erro ao cadastrar o produto.";
                $messageType = "error";
            }
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

        <form method="post" class="form" id="produto-form">
            <div class="form-group">
                <label for="nome" class="form-label">Nome do Produto: <span class="required-indicator">*</span></label>
                <input type="text" name="nome" id="nome" required class="form-control"
                       value="<?= $editing ? htmlspecialchars($produto['nome']) : (isset($nome) ? htmlspecialchars($nome) : '') ?>">
            </div>

            <div class="form-row">
                <div class="form-col">
                    <label for="id_fabricante" class="form-label">Fabricante:</label>
                    <select name="id_fabricante" id="id_fabricante" class="form-select">
                        <option value="">-- Selecione um Fabricante --</option>
                        <?php foreach ($fabricantes as $fab): ?>
                            <option value="<?= $fab['id'] ?>" <?= ($editing && $produto['id_fabricante'] == $fab['id']) ? 'selected' : (isset($id_fabricante) && $id_fabricante == $fab['id'] ? 'selected' : '') ?>>
                                <?= htmlspecialchars($fab['nome']) ?> <?= !empty($fab['cnpj']) ? '(' . htmlspecialchars($fab['cnpj']) . ')' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <a href="fabricante.php" class="add-new-link" target="_blank">Adicionar novo fabricante</a>
                </div>

                <div class="form-col">
                    <label for="id_grupo" class="form-label">Grupo:</label>
                    <select name="id_grupo" id="id_grupo" class="form-select">
                        <option value="">-- Selecione um Grupo --</option>
                        <?php foreach ($grupos as $grupo): ?>
                            <option value="<?= $grupo['id'] ?>" <?= ($editing && $produto['id_grupo'] == $grupo['id']) ? 'selected' : (isset($id_grupo) && $id_grupo == $grupo['id'] ? 'selected' : '') ?>>
                                <?= htmlspecialchars($grupo['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <a href="grupo.php" class="add-new-link" target="_blank">Adicionar novo grupo</a>
                </div>
            </div>

            <div class="form-group">
                <label for="tipo" class="form-label">Tipo:</label>
                <input type="text" name="tipo" id="tipo" class="form-control"
                       value="<?= $editing ? htmlspecialchars($produto['tipo'] ?? '') : (isset($tipo) ? htmlspecialchars($tipo) : '') ?>">
            </div>

            <div class="form-row">
                <div class="form-col">
                    <label for="volume" class="form-label">Volume:</label>
                    <input type="text" name="volume" id="volume" class="form-control"
                           value="<?= $editing ? htmlspecialchars($produto['volume'] ?? '') : (isset($volume) ? htmlspecialchars($volume) : '') ?>">
                </div>
                <div class="form-col">
                    <label for="unidade_medida" class="form-label">Unidade de Medida:</label>
                    <input type="text" name="unidade_medida" id="unidade_medida" class="form-control"
                           value="<?= $editing ? htmlspecialchars($produto['unidade_medida'] ?? '') : (isset($unidade_medida) ? htmlspecialchars($unidade_medida) : '') ?>" placeholder="Ex: ml, L, g, kg, etc">
                </div>
            </div>

            <div class="form-group">
                <label for="preco" class="form-label">Preço (R$):</label>
                <input type="text" name="preco" id="preco" class="form-control"
                       value="<?= $editing ? ($produto['preco'] ? number_format($produto['preco'], 2, ',', '.') : '') : (isset($preco) ? number_format($preco, 2, ',', '.') : '') ?>"
                       placeholder="Ex: 10,50" onblur="formatCurrency(this)">
            </div>

            <div class="form-group">
                <label for="descricao" class="form-label">Descrição:</label>
                <textarea name="descricao" id="descricao" class="form-control"><?= $editing ? htmlspecialchars($produto['descricao'] ?? '') : (isset($descricao) ? htmlspecialchars($descricao) : '') ?></textarea>
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary"><?= $editing ? 'Atualizar' : 'Cadastrar' ?></button>

                <?php if ($editing): ?>
                    <a href="list_produtos.php" class="btn btn-secondary">Cancelar</a>
                <?php else: ?>
                    <a href="../index.php" class="btn btn-secondary">Voltar para a Página Inicial</a>
                    <a href="list_produtos.php" class="btn btn-outline-primary">Ver Lista de Produtos</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

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
</script>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
