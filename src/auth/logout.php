<?php
// auth/logout.php
session_start();

// Registrar o logout antes de destruir a sessão
if (isset($_SESSION['user_id'])) {
    require_once __DIR__ . '/../config/db.php';

    // Opcional: registrar o logout em uma tabela de log
    // $stmt = $pdo->prepare("UPDATE login_logs SET data_logout = NOW() WHERE id_pessoa = :id_pessoa AND data_logout IS NULL ORDER BY id DESC LIMIT 1");
    // $stmt->execute(['id_pessoa' => $_SESSION['user_id']]);
}

// Limpar todas as variáveis de sessão
$_SESSION = array();

// Destruir o cookie de sessão se existir
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destruir a sessão
session_destroy();

// Redirecionar para a página de login
header("Location: /auth/login.php");
exit;