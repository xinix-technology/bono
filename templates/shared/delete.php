<h2>Delete <?php echo f('controller.name') ?></h2>

<form method="post">

    <p>Are you sure?</p>

    <div class="command-bar">
        <input type="submit" value="Yes">
        <a href="javascript:history.back()">No</a>
        <a href="<?php echo f('controller.url') ?>">List</a>
    </div>

</form>