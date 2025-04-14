<?php
// relatorios/produtos_por_local.php
require_once __DIR__ . '/../config/db.php';

// Set page title for the header
// $pageTitle = 'Produtos Disponíveis por Almoxarifado';

// Get inventory by location
$stmt = $pdo->query("
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
    GROUP BY l.id, l.nome, p.id, p.nome, g.nome, f.nome
    HAVING COALESCE(SUM(CASE WHEN m.tipo = 'entrada' THEN m.quantidade ELSE -m.quantidade END), 0) > 0
    ORDER BY l.nome, p.nome
");

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

    <br></br>
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
            <p>Não há produtos em estoque no momento.</p>
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