<!DOCTYPE html>
<html lang="en">
    <head>
        <?php /** @var \JkaServerStatus\Config\ConfigData $config */ ?>
        <?php $title = $config->aboutPageTitle; ?>
        <?php require_once __DIR__ . '/_head.php'; ?>
    </head>
    <body class="about-page">
        <div id="content">
            <header>
                <?php if ($config->isLandingPageEnabled): ?>
                    <a href="<?= htmlspecialchars($config->landingPageUri); ?>"
                       id="home-button" class="button" title="Go back to the server list">

                        <img src="<?= asset('/favicon.svg') ?>"
                             width="16" height="16" alt="" aria-hidden="true" />
                        Server list
                    </a>
                <?php endif; ?>
            </header>

            <article id="main-content">
                <h1>
                    <img src="<?= htmlspecialchars(asset('/favicon.svg')) ?>"
                        width="16" height="16" alt="" aria-hidden="true" />
                    <span class="white"><?= $title ?></span>
                </h1>

                <?php if (file_exists(__DIR__ . '/_about_page_content.php')): ?>
                    <?php include __DIR__ . '/_about_page_content.php'; ?>
                <?php else: ?>
                    <?php include __DIR__ . '/_about_page_content.default.php'; ?>
                <?php endif; ?>
            </article>

            <footer></footer>
        </div>
    </body>
</html>
