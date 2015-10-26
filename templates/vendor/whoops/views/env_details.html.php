<?php /* List data-table values, i.e: $_SERVER, $_GET, .... */ ?>
<div class="details">
  <div class="data-table-container" id="data-tables">
    <?php foreach ($tables as $label => $data): ?>
      <?php if ($label === 'Output Buffers') continue ?>
      <div class="data-table" id="sg-<?php echo $tpl->escape($tpl->slug($label)) ?>">
        <?php if (!empty($data)): ?>
            <label><?php echo $tpl->escape($label) ?></label>
            <table class="data-table">
              <thead>
                <tr>
                  <td class="data-table-k">Key</td>
                  <td class="data-table-v">Value</td>
                </tr>
              </thead>
            <?php foreach ($data as $k => $value): ?>
              <tr>
                <td><?php echo $tpl->escape($k) ?></td>
                <td><?php echo $tpl->escape(print_r($value, true)) ?></td>
              </tr>
            <?php endforeach ?>
            </table>
        <?php else: ?>
            <label class="empty"><?php echo $tpl->escape($label) ?></label>
            <span class="empty">empty</span>
        <?php endif ?>
      </div>
    <?php endforeach ?>
  </div>

  <div class="data-table-container" id="handlers">
    <label>Output Buffers</label>
    <?php foreach ($tables['Output Buffers'] as $i => $ob): ?>
      <div style="overflow: auto; border-radius: 6px; background: #C6C6C6; text-shadow: 0 1px 0 #E7E7E7;">
        <div style="padding: 20px">
          <?php echo htmlentities($ob) ?>
        </div>
      </div>
    <?php endforeach ?>
  </div>

  <?php /* List registered handlers, in order of first to last registered */ ?>
  <div class="data-table-container" id="handlers">
    <label>Registered Handlers</label>
    <?php foreach ($handlers as $i => $handler): ?>
      <div class="handler <?php echo ($handler === $handler) ? 'active' : ''?>">
        <?php echo $i ?>. <?php echo $tpl->escape(get_class($handler)) ?>
      </div>
    <?php endforeach ?>
  </div>

</div>
