<h2>List <?php echo f('controller.name') ?></h2>

<div class="command-bar">
    <a href="<?php echo f('controller.url', '/null/create') ?>">Create</a>
</div>

<div class="table-placeholder">

    <table>
        <thead>
            <tr>

                <?php foreach(f('app')->controller->schema() as $name => $field): ?>

                    <th><?php echo $field->label(true) ?></th>

                <?php endforeach ?>

            </tr>
        </thead>
        <tbody>

            <?php if ($entries->count()): ?>
            <?php foreach($entries as $entry): ?>

            <tr>

                <?php foreach(f('app')->controller->schema() as $name => $field): ?>

                <td>
                    <a href="<?php echo f('controller.url', '/'.$entry['$id']) ?>">
                    <?php echo $field->format('readonly', $entry[$name]) ?>
                    </a>
                </td>

                <?php endforeach ?>

            </tr>

            <?php endforeach ?>
            <?php else: ?>

            <tr>
                <td>no record!</td>
            </tr>

            <?php endif ?>

        </tbody>
    </table>
</div>