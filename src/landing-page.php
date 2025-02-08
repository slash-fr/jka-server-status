<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8"/>
        <title>JKA Server Status</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <link href="<?= htmlspecialchars($GLOBALS['root_url']) ?>/style.css?version=1" rel="stylesheet" />
        <?php /* The query string ("?version=1") is used for cache busting purposes */ ?>
        <link rel="icon" type="image/png" href="/favicon-96x96.png" sizes="96x96" />
        <link rel="icon" type="image/svg+xml" href="/favicon.svg" />
        <link rel="shortcut icon" href="/favicon.ico" />
        <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png" />
        <meta name="apple-mobile-web-app-title" content="JKA Server" />
        <link rel="manifest" href="/site.webmanifest" />
    </head>
    <body class="landing-page">
        <div id="background-image"
             style="background-image: url(<?= htmlspecialchars($GLOBALS['root_url']) ?>/levelshots/default.jpg)" />
        </div>
        <div id="content">
            <h1>
                <img src="<?= htmlspecialchars($GLOBALS['root_url']) ?>/favicon.svg"
                     width="16" height="16" alt="" aria-hidden="true" />
                <span class="white">JKA Server Status</span>
            </h1>

            <?php foreach ($jka_servers as $jka_server): ?>
                <a class="button" href="<?= htmlspecialchars($jka_server['uri']); ?>">
                    <?= format_name($jka_server['name']); ?>
                </a>
            <?php endforeach; ?>
        </div>
    </body>
</html>
