<h2>Delete <?php echo f('controller.name') ?></h2>

<form method="post">
    <?php if ($_SERVER['REQUEST_METHOD'] === 'GET'): ?>
    <p>Are you sure?</p>
    <?php endif ?>
    <div class="command-bar">
        <?php if ($_SERVER['REQUEST_METHOD'] === 'GET'): ?>
        <input type="submit" value="Yes">
        <a href="javascript:history.back()" class="button">No</a> |
        <a href="<?php echo f('controller.url', '/:id/read') ?>" class="button">Show</a>
        <?php endif ?>
        <a href="<?php echo f('controller.url') ?>" class="button">List</a>
    </div>

</form>