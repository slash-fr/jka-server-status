<!DOCTYPE html>
<html lang="en">
    <head>
        <?php $title = '404 Not Found'; ?>
        <?php require_once __DIR__ . '/_head.php'; ?>
    </head>
    <body class="page-not-found">
        <h1><span class="white">404 Not Found</span></h1>
        <img src="<?= htmlspecialchars(asset('/404.jpg')) ?>"
             alt="This isn't the webpage you're looking for."
             title="Move along."/>
    </body>
</html>
