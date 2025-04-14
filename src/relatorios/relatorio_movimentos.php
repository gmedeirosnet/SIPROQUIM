<?php
// relatorios/relatorio_movimentos.php
require_once __DIR__ . '/../config/db.php';

// Set page title for the header
$pageTitle = 'Relatório de Movimentações';

// Consulta que junta movimentos com produtos e pessoas
$sql = "SELECT m.id,
               p.nome AS produto,
               pe.nome AS pessoa,
               l.nome AS lugar,
               m.tipo,
               m.quantidade,
               m.observacao,
               m.data_movimento
        FROM movimentos m
        JOIN produtos p ON p.id = m.id_produto
        JOIN pessoas pe ON pe.id = m.id_pessoa
        LEFT JOIN lugares l ON l.id = m.id_lugar
        ORDER BY m.data_movimento DESC";

try {
    $stmt = $pdo->query($sql);
    $movimentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Erro ao gerar relatório: " . $e->getMessage();
    exit;
}

// Calcular totais e estatísticas
$total_movimentos = count($movimentos);
$total_entradas = 0;
$total_saidas = 0;
$quantidade_entrada = 0;
$quantidade_saida = 0;

foreach ($movimentos as $mov) {
    if ($mov['tipo'] == 'entrada') {
        $total_entradas++;
        $quantidade_entrada += $mov['quantidade'];
    } else {
        $total_saidas++;
        $quantidade_saida += $mov['quantidade'];
    }
}

// Include header
include_once __DIR__ . '/../includes/header.php';
?>

<div class="content">
    <h2 class="section-title">Relatório de Movimentações</h2>

    <div class="dashboard-cards">
        <div class="dashboard-card">
            <div><strong>Total de Movimentações: </strong><?= $total_movimentos ?></div>
        </div>
        <div class="dashboard-card">
            <div><strong>Entradas: </strong><?= $total_entradas ?></div>
            <!-- <div><strong>Quantidade: </strong> <?= $quantidade_entrada ?> itens</div> -->
        </div>
        <div class="dashboard-card">
            <div><strong>Saídas: </strong> <?= $total_saidas ?></div>
            <!-- <div><strong>Quantidade:</strong> <?= $quantidade_saida ?> itens</div> -->
        </div>
    </div>

    <h3 class="mt-4">Lista de Movimentações</h3>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Produto</th>
                    <th>Pessoa</th>
                    <th>Local</th>
                    <th>Tipo</th>
                    <th>Quantidade</th>
                    <th>Data do Movimento</th>
                    <th>Observação</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($movimentos as $mov): ?>
                <tr>
                    <td><?= $mov['id'] ?></td>
                    <td><?= htmlspecialchars($mov['produto']) ?></td>
                    <td><?= htmlspecialchars($mov['pessoa']) ?></td>
                    <td><?= htmlspecialchars($mov['lugar'] ?: 'Não especificado') ?></td>
                    <td>
                        <?php if ($mov['tipo'] == 'entrada'): ?>
                            <span class="text-success"><strong>Entrada</strong></span>
                        <?php else: ?>
                            <span class="text-danger"><strong>Saída</strong></span>
                        <?php endif; ?>
                    </td>
                    <td class="text-right"><?= $mov['quantidade'] ?></td>
                    <td><?= date("d/m/Y H:i", strtotime($mov['data_movimento'])) ?></td>
                    <td><?= htmlspecialchars($mov['observacao'] ?: '') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="btn-group mt-4">
        <a href="relatorio_estoque.php" class="btn btn-outline-primary">Ver Estoque</a>
        <a href="produtos_por_local.php" class="btn btn-outline-primary">Produtos por Almoxarifado</a>
        <a href="../index.php" class="btn btn-secondary">Voltar para a Página Inicial</a>
    </div>
</div>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
