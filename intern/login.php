<!DOCTYPE html>
<html lang="de">
    <head>
        <?php 
            $ver="2.0.0";
            $developer_mode=true;
            $cache=$ver;
            if($developer_mode)
                $cache=time();
        ?>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
        <title>Login - Vertretungsplan Intern</title>
        <script src="../js/jquery.min.js" type="text/javascript"></script>
        <script async src="../js/amp.js"></script>
        <script src="js/login.js?_=<?php echo $cache ?>" type="text/javascript"></script>
        <link rel="stylesheet" type="text/css" href="css/login.css?_=<?php echo $cache ?>">
        <link rel="stylesheet" type="text/css" href="../css/tabler-icons.css">
    </head>
    <body>
        <amp-img id="bg" src="https://www.rheingau-gymnasium.de/plan_mbs_12191806072021/apis/school_image/index.php?s=1280" alt="School Image" layout="responsive"></amp-img>
        <form id="login-flow" method="post">
            <input type="text" name="username" required placeholder="Benutzername">
            <input type="password" name="password" required placeholder="Passwort">
            <input type="submit" value="Anmelden">
        </form>
        <footer id="bottom">
            <b class="vpl">Vertretungsplan <b>Intern</b></b>
            <div class="from"><a>von</a> <b class="mbs">MBS</b></div>
        </footer>
    </body>
</html>