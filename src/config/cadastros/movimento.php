<?php
// cadastros/movimento.php
require_once __DIR__ . '/../../config/db.php';

// Buscar produtos, pessoas e lugares para preencher os selects
$produtos = $pdo->query("SELECT id, nome FROM produtos ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);
$pessoas  = $pdo->query("SELECT id, nome FROM pessoas ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);
$lugares  = $pdo->query("SELECT id, nome FROM lugares ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_produto  = $_POST['id_produto'];
    $id_pessoa   = $_POST['id_pessoa'];
    $id_lugar    = $_POST['id_lugar'];
    $tipo        = $_POST['tipo'];
    $quantidade  = $_POST['quantidade'];

    $sql = "INSERT INTO movimentos (id_produto, id_pessoa, id_lugar, tipo, quantidade)
            VALUES (:id_produto, :id_pessoa, :id_lugar, :tipo, :quantidade)";
    $stmt = $pdo->prepare($sql);
    if ($stmt->execute([
        'id_produto' => $id_produto,
        'id_pessoa'  => $id_pessoa,
        'id_lugar'   => $id_lugar,
        'tipo'       => $tipo,
        'quantidade' => $quantidade
    ])) {
        echo "Movimentação registrada com sucesso!";
    } else {
        echo "Erro ao registrar movimentação.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Movimentação de Produtos</title>
</head>
<body>
    <h1>Movimentação de Produtos</h1>
    <form method="post">
        <label for="id_produto">Produto:</label>
        <select name="id_produto" id="id_produto" required>
            <option value="">Selecione</option>
            <?php foreach ($produtos as $produto): ?>
            <option value="<?= $produto['id'] ?>"><?= $produto['nome'] ?></option>
            <?php endforeach; ?>
        </select><br><br>

        <label for="id_pessoa">Pessoa:</label>
        <select name="id_pessoa" id="id_pessoa" required>
            <option value="">Selecione</option>
            <?php foreach ($pessoas as $pessoa): ?>
            <option value="<?= $pessoa['id'] ?>"><?= $pessoa['nome'] ?></option>
            <?php endforeach; ?>
        </select><br><br>

        <label for="id_lugar">Lugar de Estoque:</label>
        <select name="id_lugar" id="id_lugar" required>
            <option value="">Selecione</option>
            <?php foreach ($lugares as $lugar): ?>
            <option value="<?= $lugar['id'] ?>"><?= $lugar['nome'] ?></option>
            <?php endforeach; ?>
        </select><br><br>

        <label for="tipo">Tipo:</label>
        <select name="tipo" id="tipo" required>
            <option value="entrada">Entrada</option>
            <option value="saida">Saída</option>
        </select><br><br>

        <label for="quantidade">Quantidade:</label>
        <input type="number" name="quantidade" id="quantidade" min="1" required><br><br>

        <input type="submit" value="Registrar Movimentação">
    </form>

    <p><a href="../../../index.php">Voltar para a Página Inicial</a></p>
</body>
</html>
