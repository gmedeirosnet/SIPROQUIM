<?php
// relatorios/produtos_por_local.php
require_once __DIR__ . '/../config/db.php';

// Verificação de permissões
require_once __DIR__ . '/../auth/auth_check.php';
requirePermission(PERMISSION_READ, $current_user_grupo);

// Set page title for the header
// $pageTitle = 'Produtos Disponíveis por Almoxarifado';

// Inicializar variáveis de filtro
$filter_produto = isset($_GET['produto']) ? trim($_GET['produto']) : '';
$filter_grupo = isset($_GET['grupo']) ? (int)$_GET['grupo'] : 0;
$filter_fabricante = isset($_GET['fabricante']) ? (int)$_GET['fabricante'] : 0;

// Obter grupos e fabricantes para os filtros
$grupos = $pdo->query("SELECT id, nome FROM grupos ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);
$fabricantes = $pdo->query("SELECT id, nome FROM fabricantes ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);

// Construir cláusula WHERE para filtros
$where_clause = '';
$having_clause = 'HAVING COALESCE(SUM(CASE WHEN m.tipo = \'entrada\' THEN m.quantidade ELSE -m.quantidade END), 0) > 0';
$params = [];

if (!empty($filter_produto)) {
    $where_clause .= " AND p.nome LIKE :produto";
    $params[':produto'] = "%{$filter_produto}%";
}

if ($filter_grupo > 0) {
    $where_clause .= " AND p.id_grupo = :grupo";
    $params[':grupo'] = $filter_grupo;
}

if ($filter_fabricante > 0) {
    $where_clause .= " AND p.id_fabricante = :fabricante";
    $params[':fabricante'] = $filter_fabricante;
}

// Get inventory by location with filters
$sql = "
    SELECT
        l.id as lugar_id,
        l.nome as lugar,
        p.id as produto_id,
        p.nome as produto,
        g.nome as grupo,
        f.nome as fabricante,
        COALESCE(SUM(CASE WHEN m.tipo = 'entrada' THEN m.quantidade ELSE -m.quantidade END), 0) as saldo
    FROM lugares l
    LEFT JOIN movimentos m ON l.id = m.id_lugar
    LEFT JOIN produtos p ON m.id_produto = p.id
    LEFT JOIN grupos g ON p.id_grupo = g.id
    LEFT JOIN fabricantes f ON p.id_fabricante = f.id
    WHERE 1=1 {$where_clause}
    GROUP BY l.id, l.nome, p.id, p.nome, g.nome, f.nome
    {$having_clause}
    ORDER BY l.nome, p.nome
";

$stmt = $pdo->prepare($sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();

$produtos_por_lugar = [];
$total_lugares = 0;
$total_produtos = 0;
$total_itens = 0;
$produtos_unicos = [];

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $lugar_id = $row['lugar_id'];
    if (!isset($produtos_por_lugar[$lugar_id])) {
        $produtos_por_lugar[$lugar_id] = [
            'nome' => $row['lugar'],
            'produtos' => []
        ];
        $total_lugares++;
    }

    $produtos_por_lugar[$lugar_id]['produtos'][] = $row;
    $total_itens += $row['saldo'];

    if (!isset($produtos_unicos[$row['produto_id']])) {
        $produtos_unicos[$row['produto_id']] = true;
        $total_produtos++;
    }
}

// Include header
include_once __DIR__ . '/../includes/header.php';
?>

<div class="content">
    <h2 class="section-title">Produtos Disponíveis por Almoxarifado</h2>

    <!-- Form para filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="get" action="" class="mb-2">
                <div class="form-row">
                    <div class="form-col">
                        <label for="produto">Nome do Produto:</label>
                        <input type="text" id="produto" name="produto" class="form-control"
                               value="<?= htmlspecialchars($filter_produto) ?>" placeholder="Filtrar por nome do produto">
                    </div>
                    <div class="form-col">
                        <label for="grupo">Grupo:</label>
                        <select id="grupo" name="grupo" class="form-select">
                            <option value="0">Todos os Grupos</option>
                            <?php foreach ($grupos as $grupo): ?>
                            <option value="<?= $grupo['id'] ?>" <?= $filter_grupo == $grupo['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($grupo['nome']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-col">
                        <label for="fabricante">Fabricante:</label>
                        <select id="fabricante" name="fabricante" class="form-select">
                            <option value="0">Todos os Fabricantes</option>
                            <?php foreach ($fabricantes as $fab): ?>
                            <option value="<?= $fab['id'] ?>" <?= $filter_fabricante == $fab['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($fab['nome']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-row mt-3">
                    <button type="submit" class="btn btn-primary">Filtrar</button>
                    <?php if (!empty($filter_produto) || $filter_grupo > 0 || $filter_fabricante > 0): ?>
                        <a href="produtos_por_local.php" class="btn btn-outline-secondary ml-2">Limpar Filtros</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <?php if (!empty($filter_produto) || $filter_grupo > 0 || $filter_fabricante > 0): ?>
        <div class="alert alert-info">
            <strong>Filtros aplicados:</strong>
            <?php
            $filtros = [];
            if (!empty($filter_produto)) $filtros[] = "Produto contendo \"" . htmlspecialchars($filter_produto) . "\"";
            if ($filter_grupo > 0) {
                foreach ($grupos as $g) {
                    if ($g['id'] == $filter_grupo) $filtros[] = "Grupo: " . htmlspecialchars($g['nome']);
                }
            }
            if ($filter_fabricante > 0) {
                foreach ($fabricantes as $f) {
                    if ($f['id'] == $filter_fabricante) $filtros[] = "Fabricante: " . htmlspecialchars($f['nome']);
                }
            }
            echo implode(", ", $filtros);
            ?>
        </div>
    <?php endif; ?>

    <br>
    <div class="dashboard-cards">
        <div class="dashboard-card">
            <div>Total de Locais: <strong><?= $total_lugares ?></div></strong>
        </div>
        <div class="dashboard-card">
            <div>Total de Produtos: <strong><?= $total_produtos ?></div></strong>
        </div>
        <div class="dashboard-card">
            <div>Total de Itens em Estoque: <strong><?= $total_itens ?></div></strong>
        </div>
    </div>

    <br>
    <?php if (!empty($produtos_por_lugar)): ?>
        <div class="accordion mt-4">
            <?php foreach ($produtos_por_lugar as $lugar): ?>
            <div class="accordion-item">
                <div class="accordion-header">
                    <h3><?= htmlspecialchars($lugar['nome']) ?></h3>
                    <span class="badge badge-primary"><?= count($lugar['produtos']) ?></span>
                </div>
                <div class="accordion-content">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Produto</th>
                                    <th>Grupo</th>
                                    <th>Fabricante</th>
                                    <th class="text-right">Quantidade</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($lugar['produtos'] as $item): ?>
                                <tr>
                                    <td><?= htmlspecialchars($item['produto']) ?></td>
                                    <td><?= htmlspecialchars($item['grupo'] ?? 'Sem grupo') ?></td>
                                    <td><?= htmlspecialchars($item['fabricante'] ?? 'Não especificado') ?></td>
                                    <td class="text-right"><?= $item['saldo'] ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info mt-4">
            <?php if (!empty($filter_produto) || $filter_grupo > 0 || $filter_fabricante > 0): ?>
                <p>Não foram encontrados produtos correspondentes aos filtros aplicados.</p>
                <p><a href="produtos_por_local.php" class="btn btn-outline-primary mt-2">Limpar Filtros</a></p>
            <?php else: ?>
                <p>Não há produtos em estoque no momento.</p>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="btn-group mt-4">
        <a href="relatorio_estoque.php" class="btn btn-outline-primary">Ver Estoque</a>
        <a href="relatorio_movimentos.php" class="btn btn-outline-primary">Ver Movimentações</a>
        <a href="../index.php" class="btn btn-secondary">Voltar para a Página Inicial</a>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle accordion functionality
        const accordionHeaders = document.querySelectorAll('.accordion-header');

        accordionHeaders.forEach(header => {
            header.addEventListener('click', function() {
                // Get the parent accordion item
                const accordionItem = this.parentNode;

                // Toggle active class
                const wasActive = accordionItem.classList.contains('active');

                // Close all accordion items
                document.querySelectorAll('.accordion-item').forEach(item => {
                    item.classList.remove('active');
                });

                // If it wasn't active before, make it active now
                if (!wasActive) {
                    accordionItem.classList.add('active');
                }
            });
        });

        // Open the first accordion item by default
        const firstAccordionItem = document.querySelector('.accordion-item');
        if (firstAccordionItem) {
            firstAccordionItem.classList.add('active');
        }
    });
</script>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>