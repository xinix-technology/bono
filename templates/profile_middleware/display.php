<style>

    .-profile {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 100000;
        background-color: rgba(0,0,0,.7);
        display: none;
    }

    .-profile.show {
        display: block;
    }

    .-profile .content {
        background-color: #fff;
        overflow: auto;
        position: absolute;
        padding: 20px;
        left: 20px;
        right: 20px;
        top: 20px;
        bottom: 20px;
    }

    .-profile .content table {
        width: 100%;
        border-collapse: collapse;
        border: 1px solid #0096C9;
    }

    .-profile .content table tr {
        vertical-align: top;
    }

    .-profile .content table th,
    .-profile .content table td {
        text-align: left;
        /*padding: 5px 10px;*/
        border-bottom: 1px solid #0096C9;
    }
    .-profile .content table th {
        padding: 5px 10px;
        border-right: 1px solid #0096C9;
        background-color: #0096C9;
        color: rgba(255,255,255,.9);
    }


    .-profile .content h1,
    .-profile .content h3 {
        color: #0096C9;
        font-size: .9rem;
    }

    .-profile .content h1 {
        font-size: 1.2rem;
    }

    .-profile .content h3 sup {
        font-size: .6rem;
    }

    .-profile .content .trace {
        position: relative;
    }

    .-profile .content .trace .trace-item {
        display: none;
        padding: 5px 10px;
        border-top: 1px solid #0096C9;
    }

    .-profile .content .trace .trace-item:first-child {
        display: block;
    }

    .-profile .content .trace.expand .trace-item {
        display: block;
    }

    .-profile .content pre {
        font-size: .8rem;
        /*padding: 0;*/
        padding: 5px 10px;
        margin: 0;
    }

    .-profile .content .fn {
        /*font-weight: bold;*/
        color: #666;
    }

    .-profile .content .fl {
        font-size: .8em;
        color: #333;
    }

    .-profile-command {
        position: fixed;
        bottom: 0;
    }

    .-profile-command a {
        padding: 5px 10px;
        border: 1px solid;
        background: white;
    }
</style>

<div class="-profile-command">
    <a class="-profile-btn" href="#profile">System Profile</a>
</div>

<div class="-profile">
    <div class="content">
        <a class="-profile-btn button" href="#profile" style="float: right">Close</a>
        <h1>System Profile</h1>
        <?php foreach ($_profile as $segment => $data): ?>
            <h3><?php echo $segment ?><sup>(<?php echo count($data) ?>)</sup></h3>
            <table>
                <?php foreach ($data as $k => $v): ?>
                <tr>
                    <th><?php echo $k ?></th>
                    <td>
                        <pre><?php echo json_encode($v['value'], JSON_PRETTY_PRINT) ?></pre>
                        <?php if (!empty($v['trace'])): ?>
                        <div class="trace collapse">
                            <?php foreach($v['trace'] as $t): ?>
                            <div class="trace-item">
                                <div class="fn">
                                    <?php echo (isset($t['class'])) ? $t['class'].'::' : '' ?><?php echo $t['function'] ?>
                                </div>
                                <div class="fl">
                                    <?php echo (isset($t['file'])) ? ($t['file'].':'.$t['line']) : '' ?>
                                </div>
                            </div>
                            <?php endforeach ?>
                        </div>
                        <?php endif ?>
                    </td>
                </tr>
                <?php endforeach ?>
            </table>
        <?php endforeach ?>
        <br>
        <a class="-profile-btn button" href="#profile">Close</a>
    </div>
</div>

<script type="text/javascript">
$('.-profile-btn').click(function(evt) {
    evt.preventDefault();
    evt.stopImmediatePropagation();
    if ($('.-profile').hasClass('show')) {
        $('.-profile').removeClass('show');
    } else {
        $('.-profile').addClass('show');
    }
});

$(document).on('click', '.-profile .trace', function(evt) {
    evt.preventDefault();
    evt.stopImmediatePropagation();

    $(this).toggleClass('expand');
    // if ($(this).hasClass('expand')) {
    // } else {
    //     $(this).addClass('expand');
    // }
});
</script>