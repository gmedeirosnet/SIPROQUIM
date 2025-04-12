<?php
// cadastros/produto.php
require_once __DIR__ . '/../../config/db.php';

// Busca os grupos para popular o select
$stmtGrupos = $pdo->query("SELECT id, nome FROM grupos ORDER BY nome");
$grupos = $stmtGrupos->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome'];
    $id_grupo = $_POST['id_grupo'];
    $preco = $_POST['preco'];

    $sql = "INSERT INTO produtos (nome, id_grupo, preco) VALUES (:nome, :id_grupo, :preco)";
    $stmt = $pdo->prepare($sql);
    if ($stmt->execute(['nome' => $nome, 'id_grupo' => $id_grupo, 'preco' => $preco])) {
        echo "Produto cadastrado com sucesso!";
    } else {
        echo "Erro ao cadastrar produto.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Cadastro de Produto</title>
</head>
<body>
    <h1>Cadastro de Produto</h1>
    <form method="post">
        <label for="nome">Nome do Produto:</label>
        <input type="text" name="nome" id="nome" required><br><br>

        <label for="id_grupo">Grupo:</label>
        <select name="id_grupo" id="id_grupo" required>
            <option value="">Selecione</option>
            <?php foreach ($grupos as $grupo): ?>
            <option value="<?= $grupo['id'] ?>"><?= $grupo['nome'] ?></option>
            <?php endforeach; ?>
        </select><br><br>

        <label for="preco">Preço:</label>
        <input type="text" name="preco" id="preco"><br><br>

        <input type="submit" value="Cadastrar">
    </form>

    <p><a href="../../../src/index.php">Voltar para a Página Inicial</a></p>
</body>
</html>
