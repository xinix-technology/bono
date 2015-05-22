<h2>Create <?php echo f('controller.name') ?></h2>

<form method="post">

    <?php foreach(f('app')->controller->schema() as $name => $field): ?>

    <?php if ($field['hidden']) continue ?>

    <div>

        <?php echo $field->label() ?>

        <?php echo $field->format('input', @$entry[$name], @$entry) ?>

    </div>

    <?php endforeach ?>

    <div class="command-bar">
        <input type="submit"> |
        <a href="<?php echo f('controller.url') ?>" class="button"> List </a>
    </div>

</form>