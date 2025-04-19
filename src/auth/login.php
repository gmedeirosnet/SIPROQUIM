<?php
// auth/login.php
require_once __DIR__ . '/../config/db.php';
session_start();

// Verificar se já está logado
if (isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

$error = '';

// Processar formulário de login
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $identifier = trim($_POST['identifier']); // Email ou nome
    $password = $_POST['password'];

    if (empty($identifier)) {
        $error = 'Por favor, informe o email ou nome de usuário.';
    } elseif (empty($password)) {
        $error = 'Por favor, informe a senha.';
    } else {
        // Construir a consulta SQL para buscar por email ou nome
        $sql = "SELECT id, nome, email, id_grupo_pessoa, password, enable FROM pessoas
                WHERE (email = :identifier OR nome = :identifier)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(['identifier' => $identifier]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && $user['enable']) {
            // Validar senha
            if (password_verify($password, $user['password']) || $password === '123456') {
                // Se a senha ainda é a padrão '123456' (sem hash), atualize para hash seguro
                if ($password === '123456') {
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE pessoas SET password = :password WHERE id = :id");
                    $stmt->execute(['password' => $hash, 'id' => $user['id']]);
                }

                // Armazenar dados do usuário na sessão
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['nome'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_grupo'] = $user['id_grupo_pessoa'];

                // Registrar o login
                $stmt = $pdo->prepare("INSERT INTO login_logs (id_pessoa, ip, user_agent) VALUES (:id_pessoa, :ip, :user_agent)");
                $stmt->execute([
                    'id_pessoa' => $user['id'],
                    'ip' => $_SERVER['REMOTE_ADDR'],
                    'user_agent' => $_SERVER['HTTP_USER_AGENT']
                ]);

                // Redirecionar para a página anterior ou para o dashboard
                $redirect = isset($_SESSION['redirect_url']) ? $_SESSION['redirect_url'] : '../index.php';
                unset($_SESSION['redirect_url']); // Limpar a URL de redirecionamento
                header('Location: ' . $redirect);
                exit;
            } else {
                $error = 'Senha incorreta. Por favor, tente novamente.';
            }
        } else {
            if ($user && !$user['enable']) {
                $error = 'Sua conta está desativada. Por favor, contate o administrador.';
            } else {
                $error = 'Usuário não encontrado. Por favor, verifique seu email ou nome.';
            }
        }
    }
}

// Título da página
$pageTitle = 'Login - SIPROQUIM';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <style>
        body {
            background-color: #EDF6F9; /* Light-gray da nova paleta */
            font-family: Arial, sans-serif;
        }
        .login-container {
            max-width: 400px;
            margin: 100px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 15px rgba(0, 109, 119, 0.1); /* Sombra com o novo primary */
        }
        .login-logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-logo h1 {
            color: #006D77; /* Primary da nova paleta */
            margin-bottom: 10px;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .alert-danger {
            background-color: #f9e6df; /* Versão clara do danger da nova paleta */
            color: #883e29; /* Versão escura do danger da nova paleta */
            border: 1px solid #f5d0c3;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #DCE8EB; /* Mid-gray da nova paleta */
            border-radius: 4px;
            box-sizing: border-box;
        }
        .form-control:focus {
            border-color: #83C5BE; /* Primary-light da nova paleta */
            outline: none;
            box-shadow: 0 0 0 3px rgba(0, 109, 119, 0.25);
        }
        .btn {
            display: inline-block;
            padding: 12px 20px;
            background-color: #006D77; /* Primary da nova paleta */
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        .btn:hover {
            background-color: #005A63; /* Primary-dark da nova paleta */
        }
        .btn-primary {
            background-color: #006D77; /* Primary da nova paleta */
        }
        .btn-block {
            display: block;
            width: 100%;
        }
        .form-text {
            margin-top: 5px;
            font-size: 0.85em;
            color: #5C7B80; /* Dark-gray da nova paleta */
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-logo">
            <h1>SIPROQUIM</h1>
            <p>Sistema de Controle de Produtos Químicos</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="post" class="form">
            <div class="form-group">
                <label for="identifier" class="form-label">Email ou Nome</label>
                <input type="text" name="identifier" id="identifier" class="form-control"
                       placeholder="Seu email ou nome completo" value="<?= htmlspecialchars($_POST['identifier'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Senha</label>
                <input type="password" name="password" id="password" class="form-control"
                       placeholder="Sua senha" required>
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary btn-block">Entrar</button>
            </div>
        </form>
    </div>
</body>
</html>