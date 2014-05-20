<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo f('about', 'title') ?: 'Application Title' ?></title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black" />

    <style>
        body {
            font-family: "Helvetica Neue",Helvetica,Arial,sans-serif;
            font-size: 14px;
            line-height: 1.42857143;
            color: #333;
            padding: 0;
            margin: 0;
            background-color: #fefeff;
        }

        h1, h2 {
            padding: 0;
            margin: 0;
            border-bottom: 1px solid #0096C9;
            margin-bottom: 1rem;
            color: #0096C9;
        }

        h2 {
            font-size: 1.3rem;
        }

        a {
            text-decoration: none;
        }

        label {
            font-weight: bold;
            width: 6rem;
            display: inline-block;
        }

        table {
            width: 100%;
        }

        table, td, th {
            border-collapse: collapse;
            border: 1px solid #0096C9;
        }

        table th {
            background-color: #0096C9;
            color: #fff;
        }

        table td,
        table th {
            padding: 0 .8rem;
        }

        header {
            background-color: #0096C9;
            color: #fff;
        }

        header h1 {
            text-align: center;
            font-size: 1.5rem;
            margin: 0;
            color: #fff;

        }

        main {
            padding: .8rem;
        }

        .alert {
            margin: 1rem 0;
            padding: .3rem;
        }

        .alert.error {
            color: #633;
            border: 1px solid #633;
            background-color: #f99;
        }

        .alert.info {
            color: #336;
            border: 1px solid #336;
            background-color: #0096C9;
        }

        .alert p {
            padding: 0;
            margin: 0
        }

        .command-bar {
            margin: 1rem 0;
        }

        .command-bar a,
        .command-bar input {
            cursor: pointer;
            padding: .3rem 1rem;
            display: inline-block;
            border: 1px solid #0096C9;
            background-color: transparent;
            line-height: 1rem;
            font-size: 1em;
            color: #000;
        }

        div.row {
            display: inline-block;
        }

        code {
            font-size: .8rem;
            border: 1px solid #999;
            padding: 1px 5px;
            background-color: #ffa;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <header>
        <a href="<?php echo URL::base() ?>" style="position: absolute">
            <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                width="35px" height="35px" viewBox="0 0 512 512" enable-background="new 0 0 512 512" xml:space="preserve" style="fill:white">
                <path id="home-3-icon" d="M118.032,279.715l30.494,161.153h217.371l30.494-162.113L257.212,158.18L118.032,279.715z
                    M256.218,401.649c-10.157,0-18.392-8.234-18.392-18.392c0-10.159,8.234-18.394,18.392-18.394c10.159,0,18.394,8.234,18.394,18.394
                    C274.611,393.415,266.377,401.649,256.218,401.649z M304.502,292.675c0,26.667-21.617,48.284-48.284,48.284
                    c-26.666,0-48.283-21.617-48.283-48.284c0-26.666,21.617-48.283,48.283-48.283C282.885,244.392,304.502,266.009,304.502,292.675z
                    M462,256.001l-27.148,27.149L257.18,125.366L77.084,283.213L50,256L257.244,71.132L462,256.001z"/>
            </svg>
        </a>
        <h1><?php echo f('about', 'title') ?: 'Application Title' ?></h1>
    </header>

    <main>
        <?php echo f('notification.show') ?>

        <?php echo $body ?>

    </main>
</body>
</html>