<?php
// Renders movement summary dashboard cards.
// Requires: $total_movements (int), $total_entrada (int), $total_saida (int), $produtos_movimentados (array)
?>
<div class="dashboard-cards">
    <div class="dashboard-card">
        <div>Total de Movimentações: <strong><?= $total_movements ?></strong></div>
    </div>
    <div class="dashboard-card">
        <div>Total de Entradas: <strong><?= $total_entrada ?></strong></div>
    </div>
    <div class="dashboard-card">
        <div>Total de Saídas: <strong><?= $total_saida ?></strong></div>
    </div>
    <div class="dashboard-card">
        <div>Produtos Movimentados: <strong><?= count($produtos_movimentados) ?></strong></div>
    </div>
</div>
