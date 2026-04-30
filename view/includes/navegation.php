<!-- Navegación -->
<div class="pagination">
    <?php $readPage = strtolower($type) . '-read.php'; ?>

    <?php if ($prevId) { ?>
        <a
            href="<?php echo $readPage; ?>?type=<?php echo $type; ?>&id=<?php echo $id; ?>&idChapter=<?php echo $prevId; ?>&numberChapter=<?php echo $prevChapter; ?>">
            &laquo;
        </a>
    <?php } else { ?>
        <span class="pagination-disabled">&laquo;</span>
    <?php } ?>

    <a href="../work-detail.php?type=<?php echo $type; ?>&id=<?php echo $id; ?>">
        <svg class="icon icon-home" aria-label="Inicio" role="img">
            <use href="<?php echo ASSETS_URL; ?>/img/icon-sprites.svg#home"></use>
        </svg>
    </a>

    <?php if ($nextId) { ?>
        <a
            href="<?php echo $readPage; ?>?type=<?php echo $type; ?>&id=<?php echo $id; ?>&idChapter=<?php echo $nextId; ?>&numberChapter=<?php echo $nextChapter; ?>">
            &raquo;
        </a>
    <?php } else { ?>
        <span class="pagination-disabled">&raquo;</span>
    <?php } ?>
</div>