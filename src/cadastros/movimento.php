<?php
// cadastros/movimento.php
require_once __DIR__ . '/../config/db.php';

// Verificação de permissões
require_once __DIR__ . '/../auth/auth_check.php';

// Para movimentações, exigimos permissão de criação
requirePermission(PERMISSION_CREATE, $current_user_grupo);

// Set page title
$pageTitle = 'Movimentação de Produtos';

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
    $id_lugar_destino = $_POST['id_lugar_destino'] ?? '';

    // Validate required fields
    $errors = [];
    if (empty($id_produto)) {
        $errors[] = "O produto é obrigatório.";
    }
    if (empty($id_pessoa)) {
        $errors[] = "A pessoa responsável é obrigatória.";
    }
    if (empty($id_lugar)) {
        $errors[] = "O lugar é obrigatório.";
    }
    if (empty($tipo)) {
        $errors[] = "O tipo de movimentação é obrigatório.";
    }
    if ($tipo == 'transferencia') {
        if (empty($id_lugar_destino)) {
            $errors[] = "O almoxarifado de destino é obrigatório.";
        } elseif ($id_lugar_destino == $id_lugar) {
            $errors[] = "O almoxarifado de origem e destino não podem ser iguais.";
        }
    }
    if (empty($quantidade) || $quantidade <= 0) {
        $errors[] = "A quantidade deve ser maior que zero.";
    }

    // If no validation errors, proceed with database operation
    if (empty($errors)) {
        try {
            // If it's a saida, check if there's enough stock
            if ($tipo == 'saida' || $tipo == 'transferencia') {
                // Get current stock for this product in this location
                $stmt = $pdo->prepare("
                    SELECT COALESCE(SUM(CASE WHEN tipo = 'entrada' THEN quantidade ELSE -quantidade END), 0) as saldo
                    FROM movimentos
                    WHERE id_produto = :id_produto AND id_lugar = :id_lugar
                ");
                $stmt->execute([
                    'id_produto' => $id_produto,
                    'id_lugar' => $id_lugar
                ]);
                $current_stock = $stmt->fetchColumn();

                if ($current_stock < $quantidade) {
                    $errors[] = "Estoque insuficiente. Saldo atual: {$current_stock}.";
                    $messageType = "error";
                    $message = implode("<br>", $errors);
                }
            }

            if (empty($errors)) {
                // Insert the movement
                $sql = "INSERT INTO movimentos (id_produto, id_pessoa, id_lugar, tipo, quantidade, data_movimento, observacao, id_lugar_destino)
                        VALUES (:id_produto, :id_pessoa, :id_lugar, :tipo, :quantidade, :data_movimento, :observacao, :id_lugar_destino)";
                $stmt = $pdo->prepare($sql);
                $result = $stmt->execute([
                    'id_produto'       => $id_produto,
                    'id_pessoa'        => $id_pessoa,
                    'id_lugar'         => $id_lugar,
                    'tipo'             => $tipo,
                    'quantidade'       => $quantidade,
                    'data_movimento'   => $data_movimento,
                    'observacao'       => $observacao,
                    'id_lugar_destino' => ($tipo == 'transferencia' && !empty($id_lugar_destino)) ? $id_lugar_destino : null,
                ]);

                if ($result) {
                    $message = "Movimentação registrada com sucesso!";
                    $messageType = "success";
                    // Clear form
                    $id_produto = $id_pessoa = $id_lugar = '';
                    $tipo = 'saida'; // Default to saida
                    $quantidade = '';
                    $observacao = '';
                } else {
                    $message = "Erro ao registrar movimentação.";
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

// Include header
include_once __DIR__ . '/../includes/header.php';
?>

<div class="content">
    <div class="container">
        <h1 class="page-title">Registro de Movimentação de Produtos</h1>

        <?php if (isset($message)): ?>
            <div class="alert alert-<?= $messageType === 'error' ? 'danger' : 'success' ?>">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <form method="post" class="form">
            <div class="form-group">
                <label for="id_produto" class="form-label">Produto: <span class="required">*</span></label>
                <select name="id_produto" id="id_produto" class="form-select" required>
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
                    <label for="id_pessoa" class="form-label">Pessoa responsável: <span class="required">*</span></label>
                    <select name="id_pessoa" id="id_pessoa" class="form-select" required>
                        <option value="">Selecione uma pessoa</option>
                        <?php foreach ($pessoas as $pessoa): ?>
                        <option value="<?= $pessoa['id'] ?>" <?= isset($id_pessoa) && $id_pessoa == $pessoa['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($pessoa['nome']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-col">
                    <label for="id_lugar" class="form-label" id="label_id_lugar">Almoxarifado: <span class="required">*</span></label>
                    <select name="id_lugar" id="id_lugar" class="form-select" required>
                        <option value="">Selecione um local</option>
                        <?php foreach ($lugares as $lugar): ?>
                        <option value="<?= $lugar['id'] ?>" <?= isset($id_lugar) && $id_lugar == $lugar['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($lugar['nome']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-col" id="col_lugar_destino" style="display:none;">
                    <label for="id_lugar_destino" class="form-label">Almoxarifado de destino: <span class="required">*</span></label>
                    <select name="id_lugar_destino" id="id_lugar_destino" class="form-select">
                        <option value="">Selecione um local</option>
                        <?php foreach ($lugares as $lugar): ?>
                        <option value="<?= $lugar['id'] ?>" <?= isset($id_lugar_destino) && $id_lugar_destino == $lugar['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($lugar['nome']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="tipo" class="form-label">Tipo de Movimentação: <span class="required">*</span></label>
                <div class="tipo-selector">
                    <button type="button" class="card tipo-card entrada <?= (isset($tipo) && $tipo == 'entrada') ? 'selected' : '' ?>"
                            onclick="selectTipo('entrada')">
                        <div class="card-body">
                            <i class="fa fa-plus-circle"></i>
                            <h3>Entrada</h3>
                            <p>Adicionar itens ao estoque</p>
                        </div>
                    </button>
                    <button type="button" class="card tipo-card saida <?= (!isset($tipo) || (isset($tipo) && $tipo == 'saida')) ? 'selected' : '' ?>"
                            onclick="selectTipo('saida')">
                        <div class="card-body">
                            <i class="fa fa-minus-circle"></i>
                            <h3>Saída</h3>
                            <p>Remover itens do estoque</p>
                        </div>
                    </button>
                    <button type="button" class="card tipo-card transferencia <?= (isset($tipo) && $tipo == 'transferencia') ? 'selected' : '' ?>"
                            onclick="selectTipo('transferencia')">
                        <div class="card-body">
                            <i class="fa fa-exchange"></i>
                            <h3>Movimentar</h3>
                            <p>Mover o produto para outro almoxarifado</p>
                        </div>
                    </button>
                </div>
                <input type="hidden" name="tipo" id="tipo" value="<?= isset($tipo) ? $tipo : 'saida' ?>">
            </div>

            <div class="form-row">
                <div class="form-col">
                    <label for="quantidade" class="form-label">Quantidade: <span class="required">*</span></label>
                    <input type="number" name="quantidade" id="quantidade" class="form-control" min="1" value="<?= isset($quantidade) ? htmlspecialchars($quantidade) : '1' ?>" required>
                </div>
                <div class="form-col">
                    <label for="data_movimento" class="form-label">Data da Movimentação:</label>
                    <input type="datetime-local" name="data_movimento" id="data_movimento" class="form-control" value="<?= date('Y-m-d\TH:i') ?>">
                </div>
            </div>

            <div class="form-group">
                <label for="observacao" class="form-label">Observação:</label>
                <textarea name="observacao" id="observacao" class="form-control"><?= isset($observacao) ? htmlspecialchars($observacao) : '' ?></textarea>
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary">Registrar Movimentação</button>
                <a href="../index.php" class="btn btn-secondary">Voltar para a Página Inicial</a>
            </div>
        </form>

        <div class="action-links mt-4">
            <a href="../relatorios/relatorio_movimentos.php" class="btn btn-outline-primary">Ver Movimentações</a>
            <a href="../relatorios/relatorio_estoque.php" class="btn btn-outline-primary">Ver Estoque</a>
        </div>
    </div>
</div>

<script>
    function selectTipo(tipo) {
        document.getElementById('tipo').value = tipo;

        var cards = document.querySelectorAll('.tipo-card');
        cards.forEach(function(card) {
            card.classList.remove('selected');
            card.style.backgroundColor = '#f5f5f5';
            card.style.border = '';
        });

        var selected = document.querySelector('.tipo-card.' + tipo);
        selected.classList.add('selected');

        if (tipo === 'entrada') {
            selected.style.backgroundColor = '#4CAF50';
        } else if (tipo === 'saida') {
            selected.style.backgroundColor = '#f44336';
        } else {
            selected.style.backgroundColor = '';
            selected.style.border = '2px solid #4CAF50';
        }

        var colDestino = document.getElementById('col_lugar_destino');
        var labelOrigem = document.getElementById('label_id_lugar');
        if (tipo === 'transferencia') {
            colDestino.style.display = '';
            labelOrigem.innerHTML = 'Almoxarifado de origem: <span class="required">*</span>';
        } else {
            colDestino.style.display = 'none';
            labelOrigem.innerHTML = 'Almoxarifado: <span class="required">*</span>';
            document.getElementById('id_lugar_destino').value = '';
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

        // Initialize the card backgrounds to grey
        document.querySelectorAll('.tipo-card').forEach(function(card) {
            card.style.backgroundColor = '#f5f5f5';
        });

        // Set the color for the initially selected card
        if (document.querySelector('.tipo-card.entrada').classList.contains('selected')) {
            document.querySelector('.tipo-card.entrada').style.backgroundColor = '#4CAF50';
        } else if (document.querySelector('.tipo-card.saida').classList.contains('selected')) {
            document.querySelector('.tipo-card.saida').style.backgroundColor = '#f44336';
        } else if (document.querySelector('.tipo-card.transferencia').classList.contains('selected')) {
            document.querySelector('.tipo-card.transferencia').style.border = '2px solid #4CAF50';
            document.getElementById('col_lugar_destino').style.display = '';
            document.getElementById('label_id_lugar').innerHTML = 'Almoxarifado de origem: <span class="required">*</span>';
        }
    });
</script>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
