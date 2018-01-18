    <div class="container">

        <footer>

            <hr>

            <div class="row mt-2">
                <div class="col-md-12 text-center"><?php echo @module('mod_banner', array(1)); ?></div>
            </div>

            <hr>

            <?php echo @module('mod_cross_blocks', array('copy')); ?>

        </footer>

    </div>
	<?php echo @$page_foot; ?>
</body>
</html>