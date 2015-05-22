<h2><?php echo f('controller.name') ?> List</h2>

<div class="command-bar">
    <a href="<?php echo f('controller.url', '/null/create') ?>" class="button">Create</a>
</div>

<div class="table-placeholder">

    <table>
        <thead>
            <tr>
                <?php if (f('app')->controller->schema()): ?>
                <?php foreach(f('app')->controller->schema() as $name => $field): ?>

                    <th><?php echo $field->label(true) ?></th>

                <?php endforeach ?>
                <?php else: ?>
                    <th>Data</th>
                <?php endif ?>

            </tr>
        </thead>
        <tbody>

            <?php if (count($entries)): ?>
            <?php foreach($entries as $entry): ?>

            <tr>
                <?php if (f('app')->controller->schema()): ?>
                <?php foreach(f('app')->controller->schema() as $name => $field): ?>

                <td>
                    <a href="<?php echo f('controller.url', '/'.$entry['$id']) ?>">
                    <?php echo $field->format('readonly', $entry[$name], $entry) ?>
                    </a>
                </td>

                <?php endforeach ?>
                <?php else: ?>
                <td><?php echo reset($entry) ?></td>
                <?php endif ?>

            </tr>

            <?php endforeach ?>
            <?php else: ?>

            <tr>
                <td colspan="100">no record!</td>
            </tr>

            <?php endif ?>

        </tbody>
    </table>
</div>