<!DOCTYPE html>
<html lang="en">
    <head>
        <?php require_once __DIR__ . '/_head.php'; ?>
    </head>
    <body class="landing-page">
        <div id="background-image"
             style="background-image: url(<?= htmlspecialchars(asset('/levelshots/default.jpg')) ?>)" >
        </div>
        <div id="content">
            <h1>
                <img src="<?= htmlspecialchars(asset('/favicon.svg')) ?>"
                     width="16" height="16" alt="" aria-hidden="true" />
                <span class="white">JKA Server Status</span>
            </h1>

            <?php /** @var Config $config */ ?>
            <?php foreach ($config->jkaServers as $jkaServer): ?>
                <a class="button" href="<?= htmlspecialchars($jkaServer->uri); ?>">
                    <?= format_name($jkaServer->name); ?>
                </a>
            <?php endforeach; ?>
        </div>
    </body>
</html>
