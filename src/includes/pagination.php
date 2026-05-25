<?php
// Renders a pagination nav.
// Requires: $page (int), $total_pages (int)
// Optional: $pagination_extra (string) — extra query params, e.g. '&search=foo&grupo=2'
$pagination_extra = $pagination_extra ?? '';
?>
<?php if ($total_pages > 1): ?>
    <ul class="pagination">
        <?php if ($page > 1): ?>
            <li><a href="?page=1<?= $pagination_extra ?>" aria-label="Primeira página">Primeira</a></li>
            <li><a href="?page=<?= $page - 1 ?><?= $pagination_extra ?>" aria-label="Página anterior">Anterior</a></li>
        <?php else: ?>
            <li class="disabled"><span>Primeira</span></li>
            <li class="disabled"><span>Anterior</span></li>
        <?php endif; ?>

        <?php
        $start_page = max(1, $page - 2);
        $end_page   = min($start_page + 4, $total_pages);
        for ($i = $start_page; $i <= $end_page; $i++): ?>
            <?php if ($i === $page): ?>
                <li class="active"><span><?= $i ?></span></li>
            <?php else: ?>
                <li><a href="?page=<?= $i ?><?= $pagination_extra ?>" aria-label="Página <?= $i ?>"><?= $i ?></a></li>
            <?php endif; ?>
        <?php endfor; ?>

        <?php if ($page < $total_pages): ?>
            <li><a href="?page=<?= $page + 1 ?><?= $pagination_extra ?>">Próxima</a></li>
            <li><a href="?page=<?= $total_pages ?><?= $pagination_extra ?>">Última</a></li>
        <?php else: ?>
            <li class="disabled"><span>Próxima</span></li>
            <li class="disabled"><span>Última</span></li>
        <?php endif; ?>
    </ul>
<?php endif; ?>
