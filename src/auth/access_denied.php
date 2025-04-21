<?php
// auth/access_denied.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = 'Acesso Negado';
include_once __DIR__ . '/../includes/header.php';
?>

<div class="content">
    <div class="container access-denied-container">
        <div class="alert alert-danger text-center">
            <h2><i class="fas fa-exclamation-triangle"></i> Acesso Negado</h2>
            <p>Você não tem permissão para acessar este recurso ou realizar esta operação.</p>
            <p>Se você acha que isso é um erro, entre em contato com o administrador do sistema.</p>
        </div>

        <div class="text-center mt-4">
            <a href="../index.php" class="btn btn-primary">Voltar para a Página Inicial</a>
        </div>
    </div>
</div>

<style>
.access-denied-container {
    max-width: 600px;
    margin: 100px auto;
}
</style>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>