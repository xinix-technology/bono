<?php //var_dump(get_defined_constants(true)); exit; ?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title><?php echo $tpl->escape($page_title) ?></title>

    <style><?php echo $stylesheet ?></style>
  </head>
  <body>

    <div class="Whoops container">

      <div class="stack-container">
        <div class="frames-container cf <?php echo (!$has_frames ? 'empty' : '') ?>">
          <?php $tpl->render($frame_list) ?>
        </div>
        <div class="details-container cf">
          <header>
            <?php $tpl->render($header) ?>
          </header>
          <?php $tpl->render($frame_code) ?>
          <?php $tpl->render($env_details) ?>
        </div>
      </div>
    </div>

    <script src="<?php echo Theme::base('vendor/bono-whoops/ZeroClipboard.min.js') ?>"></script>
    <script src="<?php echo Theme::base('vendor/bono-whoops/prettify.js') ?>"></script>

    <script><?php echo $zepto ?></script>
    <script><?php echo $javascript ?></script>

  </body>
</html>
