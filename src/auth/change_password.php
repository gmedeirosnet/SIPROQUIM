<?php
// auth/change_password.php
require_once __DIR__ . '/../config/db.php';
include_once __DIR__ . '/auth_check.php'; // Garantir que o usuário esteja autenticado

$pageTitle = 'Alterar Senha';
$message = '';
$messageType = '';

// Processar o formulário quando enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    // Validar entradas
    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        $message = 'Todos os campos são obrigatórios';
        $messageType = 'danger';
    } elseif ($newPassword !== $confirmPassword) {
        $message = 'As novas senhas não correspondem';
        $messageType = 'danger';
    } elseif (strlen($newPassword) < 6) {
        $message = 'A nova senha deve ter pelo menos 6 caracteres';
        $messageType = 'danger';
    } else {
        // Buscar a senha atual do usuário
        $stmt = $pdo->prepare("SELECT password FROM pessoas WHERE id = :id");
        $stmt->execute(['id' => $_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verificar se a senha atual está correta (considerando também a senha padrão 123456)
        if (password_verify($currentPassword, $user['password']) || ($currentPassword === '123456' && $user['password'] === '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi')) {
            // Gerar hash da nova senha
            $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);

            // Atualizar a senha no banco de dados
            $stmt = $pdo->prepare("UPDATE pessoas SET password = :password WHERE id = :id");
            if ($stmt->execute(['password' => $passwordHash, 'id' => $_SESSION['user_id']])) {
                $message = 'Senha alterada com sucesso!';
                $messageType = 'success';
            } else {
                $message = 'Erro ao atualizar a senha';
                $messageType = 'danger';
            }
        } else {
            $message = 'Senha atual incorreta';
            $messageType = 'danger';
        }
    }
}

// Incluir o cabeçalho
include_once __DIR__ . '/../includes/header.php';
?>

<div class="content">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h2>Alterar Senha</h2>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($message)): ?>
                            <div class="alert alert-<?= $messageType ?>">
                                <?= $message ?>
                            </div>
                        <?php endif; ?>

                        <form method="post">
                            <div class="form-group">
                                <label for="current_password">Senha Atual</label>
                                <input type="password" id="current_password" name="current_password" class="form-control" required>
                                <small class="form-text text-muted">Se ainda não alterou, use a senha padrão: 123456</small>
                            </div>

                            <div class="form-group">
                                <label for="new_password">Nova Senha</label>
                                <input type="password" id="new_password" name="new_password" class="form-control" required>
                            </div>

                            <div class="form-group">
                                <label for="confirm_password">Confirmar Nova Senha</label>
                                <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                            </div>

                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">Alterar Senha</button>
                                <a href="/" class="btn btn-secondary">Cancelar</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>