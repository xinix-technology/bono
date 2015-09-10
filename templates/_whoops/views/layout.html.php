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

    <script src="<?php echo \Bono\Theme\Theme::base('vendor/bono-whoops/ZeroClipboard.min.js') ?>"></script>
    <script src="<?php echo \Bono\Theme\Theme::base('vendor/bono-whoops/prettify.js') ?>"></script>

    <script><?php echo $zepto ?></script>
    <script>
      <?php // echo $javascript ?>
      Zepto(function($) {
        prettyPrint();

        var $frameContainer = $('.frames-container');
        var $container      = $('.details-container');
        var $activeLine     = $frameContainer.find('.frame.active');
        var $activeFrame    = $container.find('.frame-code.active');
        var headerHeight    = $('header').height();

        var highlightCurrentLine = function() {
          // Highlight the active and neighboring lines for this frame:
          var activeLineNumber = +($activeLine.find('.frame-line').text());
          var $lines           = $activeFrame.find('.linenums li');
          var firstLine        = +($lines.first().val());

          $($lines[activeLineNumber - firstLine - 1]).addClass('current');
          $($lines[activeLineNumber - firstLine]).addClass('current active');
          $($lines[activeLineNumber - firstLine + 1]).addClass('current');
        }

        // Highlight the active for the first frame:
        highlightCurrentLine();

        $frameContainer.on('click', '.frame', function() {
          var $this  = $(this);
          var id     = /frame\-line\-([\d]*)/.exec($this.attr('id'))[1];
          var $codeFrame = $('#frame-code-' + id);

          if ($codeFrame) {
            $activeLine.removeClass('active');
            $activeFrame.removeClass('active');

            $this.addClass('active');
            $codeFrame.addClass('active');

            $activeLine  = $this;
            $activeFrame = $codeFrame;

            highlightCurrentLine();

            $container.scrollTop(headerHeight);
          }
        });

        if (typeof ZeroClipboard !== "undefined") {
          ZeroClipboard.config({
            moviePath: '<?php echo \Bono\Theme\Theme::base('vendor/bono-whoops/ZeroClipboard.swf') ?>',
          });

          var clipEl = document.getElementById("copy-button");
          var clip = new ZeroClipboard( clipEl );
          var $clipEl = $(clipEl);

          // show the button, when swf could be loaded successfully from CDN
          clip.on("load", function() {
            $clipEl.show();
          });
        }
      });

    </script>

  </body>
</html>
