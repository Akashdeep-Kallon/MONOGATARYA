<!-- Navegación -->
<div class="pagination">
    <?php if ($prevId) { ?>
        <a href="anime-read.php?type=<?php echo $type; ?>&id=<?php echo $id; ?>&idChapter=<?php echo $prevId; ?>&numberChapter=<?php echo $prevChapter; ?>">
            &laquo;
        </a>
    <?php } else { ?>
        <span class="pagination-disabled">&laquo;</span>
    <?php } ?>
 
    <a href="../work-detail.php?type=<?php echo $type; ?>&id=<?php echo $id; ?>">
       
    </a>
 
    <?php if ($nextId) { ?>
        <a href="anime-read.php?type=<?php echo $type; ?>&id=<?php echo $id; ?>&idChapter=<?php echo $nextId; ?>&numberChapter=<?php echo $nextChapter; ?>">
            &raquo;
        </a>
    <?php } else { ?>
        <span class="pagination-disabled">&raquo;</span>
    <?php } ?>
</div>