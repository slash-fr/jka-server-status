<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8"/>
        <title>JKA Server Status</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <link href="<?= ROOT_URL ?>style.css?version=1" rel="stylesheet" />
        <?php /* The query string ("?version=1") is used for cache busting purposes */ ?>
        <link rel="icon" type="image/png" href="/favicon-96x96.png" sizes="96x96" />
        <link rel="icon" type="image/svg+xml" href="/favicon.svg" />
        <link rel="shortcut icon" href="/favicon.ico" />
        <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png" />
        <meta name="apple-mobile-web-app-title" content="JKA Server" />
        <link rel="manifest" href="/site.webmanifest" />
    </head>
    <body class="landing-page">
        <h1><span class="mono white">JKA Server Status</span></h1>

        <a class="button" href="/main-server">
            <span class="cyan">M</span>ain <span class="cyan">S</span>erver
        </a>
        <a class="button" href="/secondary-server">
            <span class="yellow">Secondary</span> Server
        </a>
    </body>
</html>
