<?php
// relatorios/produtos_por_local.php
require_once __DIR__ . '/../config/db.php';

// Set page title for header
$pageTitle = "Produtos por Almoxarifado";

// Get all lugares (warehouses/storage locations)
try {
    $stmt = $pdo->query("SELECT id, nome FROM lugares ORDER BY nome");
    $lugares = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Count total produtos
    $stmt = $pdo->query("SELECT COUNT(DISTINCT id) as total FROM produtos");
    $total_produtos = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Count total lugares
    $total_lugares = count($lugares);

    // Get stock counts by lugar
    $produtos_por_lugar = [];
    foreach ($lugares as $lugar) {
        $stmt = $pdo->prepare("
            SELECT
                COUNT(DISTINCT p.id) as total_produtos,
                COALESCE(SUM(CASE WHEN m.tipo = 'entrada' THEN m.quantidade ELSE -m.quantidade END), 0) as total_itens
            FROM produtos p
            LEFT JOIN movimentos m ON p.id = m.id_produto AND m.id_lugar = ?
            WHERE m.id IS NOT NULL
        ");
        $stmt->execute([$lugar['id']]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

        $produtos_por_lugar[$lugar['id']] = [
            'nome' => $lugar['nome'],
            'total_produtos' => $resultado['total_produtos'],
            'total_itens' => $resultado['total_itens']
        ];

        // Get products in this location
        $stmt = $pdo->prepare("
            SELECT
                p.id,
                p.nome,
                p.codigo,
                g.nome as grupo,
                COALESCE(SUM(CASE WHEN m.tipo = 'entrada' THEN m.quantidade ELSE -m.quantidade END), 0) as saldo
            FROM produtos p
            LEFT JOIN grupos g ON p.id_grupo = g.id
            LEFT JOIN movimentos m ON p.id = m.id_produto AND m.id_lugar = ?
            WHERE m.id IS NOT NULL
            GROUP BY p.id, p.nome, p.codigo, g.nome
            HAVING saldo != 0
            ORDER BY p.nome
        ");
        $stmt->execute([$lugar['id']]);
        $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $produtos_por_lugar[$lugar['id']]['produtos'] = $produtos;
    }
} catch (PDOException $e) {
    echo "Erro ao gerar relatório: " . $e->getMessage();
    exit;
}

// Include the header
include_once __DIR__ . '/../includes/header.php';
?>

<div class="content">
    <div class="widget">
        <div class="widget-header">
            <h3 class="widget-title">Produtos por Almoxarifado</h3>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?= $total_produtos ?></div>
                <div class="stat-label">Produtos Cadastrados</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= $total_lugares ?></div>
                <div class="stat-label">Almoxarifados</div>
            </div>
        </div>

        <?php if (count($produtos_por_lugar) > 0): ?>
            <div class="accordion-container">
                <?php foreach ($produtos_por_lugar as $lugar_id => $lugar): ?>
                    <?php if (!empty($lugar['produtos'])): ?>
                        <div class="accordion-item">
                            <div class="accordion-header" onclick="toggleAccordion(<?= $lugar_id ?>)">
                                <h3><?= htmlspecialchars($lugar['nome']) ?></h3>
                                <div class="accordion-meta">
                                    <span class="badge badge-primary"><?= count($lugar['produtos']) ?> produtos</span>
                                    <span class="accordion-toggle-icon">
                                        <i class="fa fa-chevron-down"></i>
                                    </span>
                                </div>
                            </div>
                            <div class="accordion-content" id="accordion-<?= $lugar_id ?>">
                                <div class="table-container">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Código</th>
                                                <th>Produto</th>
                                                <th>Grupo</th>
                                                <th class="text-right">Quantidade</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($lugar['produtos'] as $produto): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($produto['codigo'] ?? '-') ?></td>
                                                    <td><?= htmlspecialchars($produto['nome']) ?></td>
                                                    <td><?= htmlspecialchars($produto['grupo'] ?? 'Não definido') ?></td>
                                                    <td class="text-right"><?= $produto['saldo'] ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <p>Nenhum produto está atualmente alocado em almoxarifados.</p>
            </div>
        <?php endif; ?>

        <div class="btn-group mt-4">
            <a href="../index.php" class="btn btn-secondary">Voltar para a Página Inicial</a>
            <a href="relatorio_estoque.php" class="btn btn-outline-primary">Ver Estoque Total</a>
            <a href="relatorio_movimentos.php" class="btn btn-outline-primary">Ver Movimentações</a>
        </div>
    </div>
</div>

<script>
function toggleAccordion(id) {
    const content = document.getElementById('accordion-' + id);
    content.classList.toggle('active');

    // Close other accordion items (optional)
    /*
    const allContents = document.querySelectorAll('.accordion-content');
    allContents.forEach(item => {
        if (item.id !== 'accordion-' + id && item.classList.contains('active')) {
            item.classList.remove('active');
        }
    });
    */
}

// Open the first accordion by default
document.addEventListener('DOMContentLoaded', function() {
    const firstAccordion = document.querySelector('.accordion-item');
    if (firstAccordion) {
        const firstContent = firstAccordion.querySelector('.accordion-content');
        firstContent.classList.add('active');
    }
});
</script>

<style>
/* Additional styles specific to this page */
.accordion-container {
    margin-top: 20px;
}
.accordion-item {
    border: 1px solid var(--mid-gray);
    margin-bottom: 15px;
    border-radius: var(--radius);
    overflow: hidden;
}
.accordion-header {
    background-color: var(--light-gray);
    padding: 15px;
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.accordion-header h3 {
    margin: 0;
    font-size: 1.1rem;
}
.accordion-meta {
    display: flex;
    align-items: center;
    gap: 10px;
}
.accordion-content {
    display: none;
    padding: 0;
}
.accordion-content.active {
    display: block;
}
.text-center {
    text-align: center;
}
.text-right {
    text-align: right;
}
</style>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>