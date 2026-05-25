<?php
// Verificar autenticação em todas as páginas, exceto a de login
$current_script = basename($_SERVER['PHP_SELF']);
if ($current_script !== 'login.php') {
    // Incluir verificação de autenticação
    include_once __DIR__ . '/../auth/auth_check.php';
}

// Get current page for highlighting active navigation items
$current_page = basename($_SERVER['PHP_SELF']);
$current_dir = dirname($_SERVER['PHP_SELF']);

// Function to check if a navigation item should be active
function isActive($page, $current_page = null, $current_dir = null) {
    if ($current_page === null) { $current_page = basename($_SERVER['PHP_SELF']); }
    if ($current_dir === null) { $current_dir = dirname($_SERVER['PHP_SELF']); }

    if (is_array($page)) {
        foreach ($page as $p) {
            if ($current_page === $p) { return true; }
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

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" crossorigin="anonymous"> <!-- NOSONAR -->

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Google tag (gtag.js) — SRI not supported for dynamic gtag scripts -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-534LVNS137" crossorigin="anonymous"></script> <!-- NOSONAR -->
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());

      gtag('config', 'G-534LVNS137');
    </script>

    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="icon" href="/assets/img/favicon.ico" type="image/x-icon">

    <!-- Include any additional page-specific CSS or scripts in the head -->
    <?php if (isset($additionalHead)) { echo $additionalHead; } ?>

    <style>
        /* Estilos adaptados para o dropdown de usuário seguindo o padrão existente */
        .user-dropdown {
            margin-left: 10px;
        }

        .user-dropdown > a {
            display: flex;
            align-items: center;
        }

        .user-dropdown > a::after {
            content: "▼";
            font-size: 0.7em;
            margin-left: 5px;
            color: var(--primary-light);
        }

        .main-nav, .header-actions {
            display: flex;
            align-items: center;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* Estilos para indicar o nível de permissão do usuário */
        .permission-badge {
            font-size: 0.7em;
            padding: 2px 5px;
            border-radius: 3px;
            margin-left: 5px;
            color: white;
        }
        .permission-admin {
            background-color: #006D77; /* Primary color */
        }
        .permission-technical {
            background-color: #E29578; /* Secondary color */
        }
        .permission-readonly {
            background-color: #83C5BE; /* Tertiary color */
        }
    </style>
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
                        <a href="/index.php">
                            <i class="fas fa-th-large"></i> Dashboard
                        </a>
                    </div>

                    <?php if (isset($current_user_permissions) && ($current_user_permissions['create'] || $current_user_permissions['update'])): ?>
                    <div class="nav-item dropdown">
                        <a href="#">
                            <i class="fas fa-plus-circle"></i> Cadastros
                        </a>
                        <div class="dropdown-content">
                            <a href="/cadastros/lugar.php"><i class="fas fa-warehouse"></i> Almoxarifados</a>
                            <a href="/cadastros/fabricante.php"><i class="fas fa-industry"></i> Fabricantes</a>
                            <a href="/cadastros/pessoa.php"><i class="fas fa-users"></i> Pessoas</a>
                            <a href="/cadastros/produto.php"><i class="fas fa-flask"></i> Produtos</a>
                            <a href="/cadastros/grupo.php"><i class="fas fa-layer-group"></i> Grupos de produtos</a>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="nav-item dropdown">
                        <a href="#">
                            <i class="fas fa-list"></i> Listas
                        </a>
                        <div class="dropdown-content">
                            <a href="/cadastros/list_lugares.php"><i class="fas fa-warehouse"></i> Almoxarifados</a>
                            <a href="/cadastros/list_fabricantes.php"><i class="fas fa-industry"></i> Fabricantes</a>
                            <a href="/cadastros/list_pessoas.php"><i class="fas fa-users"></i> Pessoas</a>
                            <a href="/cadastros/list_grupos_pessoas.php"><i class="fas fa-user-tag"></i> Grupos de Pessoas</a>
                            <a href="/cadastros/list_produtos.php"><i class="fas fa-flask"></i> Produtos</a>
                            <a href="/cadastros/list_grupos.php"><i class="fas fa-layer-group"></i> Grupos de produtos</a>
                        </div>
                    </div>

                    <?php if (isset($current_user_permissions) && $current_user_permissions['create']): ?>
                    <div class="nav-item <?php echo $current_page === 'movimento.php' ? 'active' : ''; ?>">
                        <a href="/cadastros/movimento.php">
                            <i class="fas fa-exchange-alt"></i> Movimentação
                        </a>
                    </div>
                    <?php endif; ?>

                    <div class="nav-item dropdown">
                        <a href="#">
                            <i class="fas fa-chart-line"></i> Relatórios
                        </a>
                        <div class="dropdown-content">
                            <a href="/relatorios/relatorio_estoque.php"><i class="fas fa-boxes"></i> Estoque Atual</a>
                            <a href="/relatorios/produtos_por_local.php"><i class="fas fa-map-marker-alt"></i> Produtos por Local</a>
                            <a href="/relatorios/relatorio_movimentos.php"><i class="fas fa-history"></i> Movimentações</a>
                            <a href="/relatorios/movimentacao_produtos.php"><i class="fas fa-flask"></i> Movimentação por Produto</a>
                            <a href="/relatorios/movimentacao_por_pessoa.php"><i class="fas fa-user"></i> Movimentação por Pessoa</a>
                            <a href="/relatorios/movimentacao_por_almoxarifado.php"><i class="fas fa-warehouse"></i> Movimentação por Almoxarifado</a>
                        </div>
                    </div>
                </nav>

                <?php if (isset($_SESSION['user_id'])): ?>
                <div class="nav-item dropdown user-dropdown">
                    <a href="#">
                        <i class="fas fa-user-circle"></i>
                        <?= htmlspecialchars($_SESSION['user_name']) ?>
                        <?php if (isset($current_user_grupo)): ?>
                            <?php if ($current_user_grupo == GROUP_ADMINISTRADORES): ?>
                                <span class="permission-badge permission-admin" title="Acesso total - CRUD">Admin</span>
                            <?php elseif ($current_user_grupo == GROUP_TECNICOS): ?>
                                <span class="permission-badge permission-technical" title="Acesso parcial - CRU">Técnico</span>
                            <?php else: ?>
                                <span class="permission-badge permission-readonly" title="Acesso somente leitura - R">Leitura</span>
                            <?php endif; ?>
                        <?php endif; ?>
                    </a>
                    <div class="dropdown-content">
                        <a href="/auth/change_password.php"><i class="fas fa-key"></i> Alterar Senha</a>
                        <a href="/auth/logout.php"><i class="fas fa-sign-out-alt"></i> Sair</a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <main class="container">
        <?php if (isset($pageTitle)): ?>
        <div class="page-header">
            <h2><?php echo $pageTitle; ?></h2>
        </div>
        <?php endif; ?>
