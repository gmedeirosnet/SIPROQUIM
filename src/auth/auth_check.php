<?php
// auth/auth_check.php
session_start();

// Verificar se o usuário está autenticado
if (!isset($_SESSION['user_id'])) {
    // Salvar URL atual para redirecionar após o login
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];

    // Redirecionar para a página de login
    header('Location: /auth/login.php');
    exit;
}

// Verificar se o usuário ainda está ativo
require_once __DIR__ . '/../config/db.php';
$stmt = $pdo->prepare("SELECT enable FROM pessoas WHERE id = :id");
$stmt->execute(['id' => $_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || !$user['enable']) {
    // Destruir a sessão se o usuário foi desativado
    session_destroy();

    // Redirecionar para a página de login com mensagem de erro
    header('Location: /auth/login.php?error=account_disabled');
    exit;
}

// Usuário válido, continue carregando a página
// Variáveis globais úteis para controle de acesso
$current_user_id = $_SESSION['user_id'];
$current_user_name = $_SESSION['user_name'];
$current_user_email = $_SESSION['user_email'];
$current_user_grupo = $_SESSION['user_grupo'];