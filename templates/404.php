<!DOCTYPE html>
<html lang="en">
    <head>
        <?php // Required variable: ?>
        <?php /** @var \JkaServerStatus\Helper\TemplateHelper $templateHelper */ ?>

        <?php $title = '404 Not Found'; ?>
        <?php require_once __DIR__ . '/_head.php'; ?>
    </head>
    <body class="page-not-found">
        <h1><span class="white">404 Not Found</span></h1>
        <img src="<?= htmlspecialchars($templateHelper->asset('/404.jpg')) ?>"
             alt="This isn't the webpage you're looking for."
             title="Move along."/>
    </body>
</html>
