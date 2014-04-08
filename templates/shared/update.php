<h2>Update <?php echo f('controller.name') ?></h2>

<form method="post">

    <?php foreach(f('app')->controller->schema() as $name => $field): ?>

    <div>

        <?php echo $field->label() ?>

        <?php echo $field->input(@$entry[$name]) ?>

    </div>

    <?php endforeach ?>

    <div class="command-bar">
        <input type="submit">
        <a href="<?php echo f('controller.url') ?>">List</a>
    </div>

</form>