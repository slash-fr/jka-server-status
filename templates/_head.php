<?php // Required variable: ?>
<?php /** @var \JkaServerStatus\Helper\TemplateHelper $templateHelper */ ?>

<?php // Optional variable: ?>
<?php /** @var string $title */ ?>

<meta charset="utf-8"/>
<title>JKA Server Status<?php if (!empty($title)) { echo ' - ' . htmlspecialchars($title); } ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<link href="<?= htmlspecialchars($templateHelper->asset('/style.css')) ?>" rel="stylesheet" />
<link rel="icon" type="image/png" href="<?= htmlspecialchars($templateHelper->asset('/favicon-96x96.png')) ?>" sizes="96x96" />
<link rel="icon" type="image/svg+xml" href="<?= htmlspecialchars($templateHelper->asset('/favicon.svg')) ?>" />
<link rel="shortcut icon" href="<?= htmlspecialchars($templateHelper->asset('/favicon.ico')) ?>" />
<link rel="apple-touch-icon" sizes="180x180" href="<?= htmlspecialchars($templateHelper->asset('/apple-touch-icon.png')) ?>" />
<meta name="apple-mobile-web-app-title" content="JKA Server" />
<link rel="manifest" href="<?= htmlspecialchars($templateHelper->asset('/site.webmanifest')) ?>" />
<?php if ($templateHelper->isOpenGraphEnabled()): ?>
    <meta property="og:title"
          content="JKA Server Status<?php if (!empty($title)) { echo ' - ' . htmlspecialchars($title); } ?>" />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="<?= htmlspecialchars($templateHelper->getOgUrl()) ?>" />
    <meta property="og:image" content="<?= htmlspecialchars($templateHelper->getOgImageUrl()) ?>" />
    <meta property="og:image:width" content="1200" />
    <meta property="og:image:height" content="630" />
    <meta property="og:description"
          content="Status page for a Jedi Academy server – basic status data, online players, …" />
    <meta name="twitter:card" content="summary_large_image" />
<?php endif; ?>
