<!DOCTYPE html>
<html lang="en">
    <head>
        <?php // Required variables: ?>
        <?php /** @var \JkaServerStatus\Config\ConfigData $config */ ?>
        <?php /** @var \JkaServerStatus\Helper\TemplateHelper $templateHelper */ ?>

        <?php require __DIR__ . '/_head.php'; ?>
    </head>
    <body class="landing-page">
        
        <div id="background-image"
             style="<?= 'background-image: url(' . $templateHelper->asset('/levelshots/default.jpg') . '); '
                      . 'opacity: ' . (int)$config->getBackgroundOpacity('default') . '%; '
                    ?>" >
        </div>
        
        <div id="content">
            <header></header>

            <article id="main-content">
                <h1>
                    <img src="<?= htmlspecialchars($templateHelper->asset('/favicon.svg')) ?>"
                        width="16" height="16" alt="" aria-hidden="true" />
                    <span class="white">JKA Server Status</span>
                </h1>

                <?php foreach ($config->jkaServers as $jkaServer): ?>
                    <?php /** @var \JkaServerStatus\Config\JkaServerConfigData $jkaServer */  ?>
                    <a class="button" href="<?= htmlspecialchars($jkaServer->uri); ?>">
                        <?= $templateHelper->formatName($jkaServer->name); ?>
                        <?php if($jkaServer->subtitle): ?>
                            <br/><span class="subtitle"><?= htmlspecialchars($jkaServer->subtitle) ?></span>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </article>
        

            <footer>
                <?php if ($config->isAboutPageEnabled): ?>
                    <p class="footnote bonus-info">
                        <a href="<?= htmlspecialchars($config->aboutPageUri) ?>">
                            <?= htmlspecialchars($config->aboutPageTitle); ?>
                        </a>
                    </p>
                <?php endif; ?>
            </footer>
        </div>
    </body>
</html>
