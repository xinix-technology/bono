<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo f('about', 'title') ?: 'Application Title' ?></title>

    <style>
        body {
            font-family: "Helvetica Neue",Helvetica,Arial,sans-serif;
            font-size: 14px;
            line-height: 1.42857143;
            color: #333;
            padding: 0;
            margin: 0;
            background-color: #eef;
        }

        h1, h2 {
            padding: 0;
            margin: 0;
            border-bottom: 1px solid #99f;
            margin-bottom: 1rem;
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
            border: 1px solid #99f;
        }

        table th {
            background-color: #99f;
            color: #fff;
        }

        table td,
        table th {
            padding: 0 .8rem;
        }

        header {
            background-color: #99f;
            color: #fff;
        }

        header h1 {
            text-align: center;
            font-size: 1.5rem;
            margin: 0;

        }

        main {
            padding: .8rem;
        }

        .alert {
            margin: 1rem 0;
            padding: .3rem;
        }

        .alert.success {
            color: #363;
            border: 1px solid #363;
            background-color: #9f9;
        }

        .alert.info {
            color: #336;
            border: 1px solid #336;
            background-color: #99f;
        }

        .command-bar {
            margin: 1rem 0;
        }

        .command-bar a,
        .command-bar input {
            cursor: pointer;
            padding: .3rem 1rem;
            display: inline-block;
            border: 1px solid #99f;
            background-color: transparent;
            line-height: 1rem;
            font-size: 1em;
            color: #000;
        }
    </style>
</head>
<body>
    <header>
        <h1><?php echo f('about', 'title') ?: 'Application Title' ?></h1>
    </header>

    <main>
        <?php if (isset($flash[ 'error']) || isset($flash[ 'info'])): ?>
        <div class="row alert-row">
            <?php if (isset($flash[ 'error'])): ?>
            <div class="alert error">
                <?php echo $flash[ 'error']; ?>
            </div>
            <?php endif ?>
            <?php if (isset($flash[ 'info'])): ?>
            <div class="alert success">
                <?php echo $flash[ 'info']; ?>
            </div>
            <?php endif ?>
        </div>
        <?php endif ?>

        <?php echo $body ?>

    </main>
</body>
</html>