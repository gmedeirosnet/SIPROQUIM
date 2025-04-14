<?php
// Get current page for highlighting active navigation items
$current_page = basename($_SERVER['PHP_SELF']);
$current_dir = dirname($_SERVER['PHP_SELF']);

// Function to check if a navigation item should be active
function isActive($page, $current_page = null, $current_dir = null) {
    if ($current_page === null) $current_page = basename($_SERVER['PHP_SELF']);
    if ($current_dir === null) $current_dir = dirname($_SERVER['PHP_SELF']);

    if (is_array($page)) {
        foreach ($page as $p) {
            if ($current_page === $p) return true;
        }
        return false;
    }

    return $current_page === $page;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' | SIPROQUIM' : 'SIPROQUIM - Sistema de Gerenciamento'; ?></title>
    <link rel="stylesheet" href="/assets/css/main.css">
    <!-- Add favicon if available -->
    <!-- <link rel="icon" href="/assets/img/favicon.ico" type="image/x-icon"> -->

    <!-- Include any additional page-specific CSS or scripts in the head -->
    <?php if (isset($additionalHead)) echo $additionalHead; ?>
</head>
<body>
    <header class="site-header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <h1>SIPROQUIM</h1>
                    <span>Sistema de Controle de Produtos Químicos</span>
                </div>

                <nav class="main-nav">
                    <div class="nav-item <?php echo $current_page === 'index.php' ? 'active' : ''; ?>">
                        <a href="/index.php">Dashboard</a>
                    </div>

                    <div class="nav-item dropdown">
                        <a href="#">Cadastros</a>
                        <div class="dropdown-content">
                            <a href="/cadastros/produto.php">Produtos</a>
                            <a href="/cadastros/pessoa.php">Pessoas</a>
                            <a href="/cadastros/lugar.php">Almoxarifados</a>
                            <a href="/cadastros/grupo.php">Grupos</a>
                            <a href="/cadastros/fabricante.php">Fabricantes</a>
                            <a href="/cadastros/grupo_pessoa.php">Grupos de Pessoas</a>
                        </div>
                    </div>

                    <div class="nav-item dropdown">
                        <a href="#">Listas</a>
                        <div class="dropdown-content">
                            <a href="/cadastros/list_produtos.php">Lista de Produtos</a>
                            <a href="/cadastros/list_pessoas.php">Lista de Pessoas</a>
                            <a href="/cadastros/list_lugares.php">Lista de Almoxarifados</a>
                            <a href="/cadastros/list_grupos.php">Lista de Grupos</a>
                            <a href="/cadastros/list_grupos_pessoas.php">Lista de Grupos de Pessoas</a>
                            <a href="/cadastros/list_fabricantes.php">Lista de Fabricantes</a>
                        </div>
                    </div>

                    <div class="nav-item <?php echo $current_page === 'movimento.php' ? 'active' : ''; ?>">
                        <a href="/cadastros/movimento.php">Movimentação</a>
                    </div>

                    <div class="nav-item dropdown">
                        <a href="#">Relatórios</a>
                        <div class="dropdown-content">
                            <a href="/relatorios/relatorio_estoque.php">Estoque Atual</a>
                            <a href="/relatorios/produtos_por_local.php">Produtos por Local</a>
                            <a href="/relatorios/movimentacao_produtos.php">Movimentação por Produto</a>
                            <a href="/relatorios/relatorio_movimentos.php">Histórico de Movimentações</a>
                        </div>
                    </div>
                </nav>
            </div>
        </div>
    </header>

    <main class="container">
        <?php if (isset($pageTitle)): ?>
        <div class="page-header">
            <h2><?php echo $pageTitle; ?></h2>
        </div>
        <?php endif; ?>