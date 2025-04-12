<?php
// cadastros/lugar.php
require_once __DIR__ . '/../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];

    $sql = "INSERT INTO lugares (nome, descricao) VALUES (:nome, :descricao)";
    $stmt = $pdo->prepare($sql);
    if ($stmt->execute(['nome' => $nome, 'descricao' => $descricao])) {
        echo "Lugar cadastrado com sucesso!";
    } else {
        echo "Erro ao cadastrar o lugar.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Cadastro de Lugar de Estoque</title>
</head>
<body>
    <h1>Cadastro de Lugar de Estoque</h1>
    <form method="post">
        <label for="nome">Nome do Lugar:</label>
        <input type="text" name="nome" id="nome" required><br><br>

        <label for="descricao">Descrição:</label>
        <textarea name="descricao" id="descricao"></textarea><br><br>

        <input type="submit" value="Cadastrar">
    </form>

    <p><a href="../../../index.php">Voltar para a Página Inicial</a></p>
</body>
</html>
