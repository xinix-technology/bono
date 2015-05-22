<h2>Show <?php echo f('controller.name') ?></h2>

<form method="post">

    <?php foreach(f('app')->controller->schema() as $name => $field): ?>
        <?php if (!$field['hidden']): ?>
        <div>
            <?php echo $field->label() ?>

            <?php echo $field->format('readonly', @$entry[$name], @$entry) ?>

        </div>
        <?php endif ?>

    <?php endforeach ?>

    <div class="command-bar">
        <a href="<?php echo f('controller.url', '/:id/update') ?>" class="button">Update</a>
        <a href="<?php echo f('controller.url', '/:id/delete') ?>" class="button">Delete</a> |
        <a href="<?php echo f('controller.url') ?>" class="button">List</a>
    </div>

</form>