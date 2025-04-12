<?php
// cadastros/pessoa.php
require_once __DIR__ . '/../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome'];
    $email = $_POST['email'];

    $sql = "INSERT INTO pessoas (nome, email) VALUES (:nome, :email)";
    $stmt = $pdo->prepare($sql);
    if ($stmt->execute(['nome' => $nome, 'email' => $email])) {
        echo "Pessoa cadastrada com sucesso!";
    } else {
        echo "Erro ao cadastrar pessoa.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Cadastro de Pessoa</title>
</head>
<body>
    <h1>Cadastro de Pessoa</h1>
    <form method="post">
        <label for="nome">Nome:</label>
        <input type="text" name="nome" id="nome" required><br><br>

        <label for="email">Email:</label>
        <input type="email" name="email" id="email"><br><br>

        <input type="submit" value="Cadastrar">
    </form>

    <p><a href="../../../index.php">Voltar para a Página Inicial</a></p>
</body>
</html>
