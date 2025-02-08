<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8"/>
        <title>JKA Server Status</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <link href="<?= htmlspecialchars($GLOBALS['root_url']) ?>/style.css?version=1" rel="stylesheet" />
        <?php /* The query string is used for cache busting */ ?>
        <link rel="icon" type="image/png" href="/favicon-96x96.png" sizes="96x96" />
        <link rel="icon" type="image/svg+xml" href="/favicon.svg" />
        <link rel="shortcut icon" href="/favicon.ico" />
        <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png" />
        <meta name="apple-mobile-web-app-title" content="JKA Server" />
        <link rel="manifest" href="/site.webmanifest" />
    </head>
    <body class="page-not-found">
        <h1><span class="white">404 Not Found</span></h1>
        <img src="<?= htmlspecialchars($GLOBALS['root_url']) ?>/404.jpg"
             alt="This isn't the webpage you're looking for."
             title="Move along."/>
    </body>
</html>
