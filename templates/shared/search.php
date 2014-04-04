<?php var_dump(f('controller.name')) ?>

<div>
    <a href="<?php echo f('controller.url', '/null/create') ?>">Create</a>
</div>

<div class="table-placeholder">

    <table>
        <tbody>

            <?php if ($entries->count()): ?>
            <?php foreach($entries as $entry): ?>

            <tr>
                <td><?php echo $entry['$id'] ?></td>
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