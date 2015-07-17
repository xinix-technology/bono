<h2><?php echo f('controller.name') ?> List</h2>

<div class="command-bar">
    <a href="<?php echo f('controller.url', '/null/create') ?>" class="button">Create</a>
</div>

<div class="table-placeholder">
    <?php if (count($entries)): ?>
    <?php foreach($entries as $entry): ?>
    <section>
        <div>
            <a href="<?php echo f('controller.url', '/'.$entry['$id']) ?>" class="button" style="float: right">Detail</a>
            <label>ID</label>
            <span class="field"><?php echo $entry['$id'] ?></span>
        </div>
        <?php if (f('app')->controller->schema()): ?>
        <?php foreach(f('app')->controller->schema() as $name => $field): ?>
        <div>
            <label><?php echo $field->label(true) ?></label>
            <?php echo $field->format('readonly', $entry[$name], $entry) ?>
        </div>
        <?php endforeach ?>
        <?php endif ?>
    </section>
    <?php endforeach ?>
    <?php endif ?>
</div>