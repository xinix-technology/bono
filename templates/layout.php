<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo f('page.title', 'Bono') ?> <?php echo f('controller.name') ? '| '.f('controller.name') : '' ?></title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black" />

    <style>
        html,
        body { height: 100%; }
        body { font-family: "Helvetica Neue",Helvetica,Arial,sans-serif; font-size: 14px; line-height: 1.5; color: #333; padding: 0; margin: 0; background-color: #fefeff; display: flex; flex-direction: column; }

        h1,
        h2 { padding: 0; margin: 0; margin-bottom: 1rem; color: #444; font-weight: normal; }

        h2 { font-size: 1.3rem; }

        a { text-decoration: none; }

        label { font-weight: normal; width: 6rem; display: block; }

        table { width: 100%; }

        table,
        td,
        th { border-collapse: collapse; border: none; font-weight: normal; border: 1px solid #444;}

        table th { background-color: #444; color: #e5e5e5; }

        table td,
        table th { padding: 0 .8rem; }

        table a { color: #444; }

        table .field { border: none; background: none; padding: 0; margin: 0; height: auto; line-height: normal; }

        header { background-color: #f1f1f1; border-bottom: 1px solid #666; border-color: #e5e5e5; height: 59px; }

        header h1 { text-align: center; font-size: 1.5rem; margin: 0; padding-top: 10px; color: #444; }

        header .home-icon { position: absolute; top: 10px; left: 10px; }

        main { padding: .8rem; width: 100%; max-width: 640px; margin: 0 auto; flex: 1; box-sizing: border-box;}

        .alert { margin: 1rem 0; padding: .3rem; padding-bottom: 10px; box-shadow: 0px 0px 10px 0px rgba(0,0,0,0.75); position: relative;}

        .alert.error { color: #633; background-color: #f99; }

        .alert.info { color: #336; background-color: #99f; }

        .alert p { padding: 0; margin: 0 }

        .alert span { display: block; text-align: center; }

        .alert .close { position: absolute; top: 5px; right: 5px; font-size: .7rem; padding: 2px; height: auto; }

        input,
        select,
        textarea,
        .field,
        .button { margin-bottom: 10px; width: 100%; min-height: 2rem; padding: 5px 5px; line-height: normal; box-sizing: border-box; font-size: 1rem; border: 1px solid #d3d3d3; background-color: #f8f8f8; color: #000; display: inline-block; }

        textarea { height: auto; }

        input[type=button],
        input[type=submit],
        .button { cursor: pointer; padding: 5px 1rem; margin: 0; margin-bottom: 10px; width: auto; font-size: 1em; line-height: 18px; height: 30px }

        code { font-size: .8rem; border: 1px solid #999; padding: 1px 5px; background-color: #ffa; border-radius: 5px; }

        section {
            border: 1px solid #d3d3d3;
            padding: 10px;
            margin: 10px 0;
        }

        section a,
        section a:hover,
        section a:active,
        section a:visited {
            color: #336;
        }

        .float-menu {
            border: 1px solid #d3d3d3;
            background-color: #ffa;
            float: left;
            padding: 10px;
            margin: 10px 10px 0  0;
            min-width: 150px;
        }

        .float-menu h2 {
            margin: 0;
            font-size: 1rem;
        }

        .float-menu a {
            display: block;
            border-top: 1px solid #d3d3d3;
            padding: 5px;
        }
    </style>
</head>
<body class="request-<?php echo strtolower($_SERVER['REQUEST_METHOD']) ?>">
    <header>
        <a href="<?php echo URL::base() ?>" class="home-icon">
            <svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                 width="35px" height="35px" viewBox="0 0 512 512" style="enable-background:new 0 0 512 512;" xml:space="preserve">
            <g>
                <polygon points="448,288 256,64 64,288 112,288 112,448 208,448 208,320 304,320 304,448 400,448 400,288  "/>
            </g>
            </svg>

        </a>
        <h1><?php echo f('page.title', 'Bono') ?></h1>
    </header>

    <main>
        <?php echo f('notification.show') ?>

        <?php echo $body ?>

    </main>
</body>
</html>