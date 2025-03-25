<?php /* Copy "_about_page_content.default.php" to "_about_page_content.php" */ ?>
<?php /* Do not use <html>, <head>, nor <body> tags. (This is a partial template). */ ?>
<?php /* Do not use an <h1> tag. There can be only one (in the entire page). */ ?>

<h2>Credits</h2>
<p>
    <strong>JKA Server Status</strong> is an Open Source tool.
    Copyright&nbsp;©&nbsp;2025&nbsp;<a href="https://github.com/slash-fr" target="_blank"><strong>Slash</strong></a>
</p>
<p>
    The source code is available on <strong>GitHub</strong>, under the terms of the <strong>MIT License</strong>:<br/>
    <a href="https://github.com/slash-fr/jka-server-status" target="_blank">
        https://github.com/slash-fr/jka-server-status
    </a>
</p>

<?php return; /* REMOVE THIS LINE TO ENABLE THE CONTENT BELOW */ ?>

<?php /* Make sure to write valid HTML (no unescaped '&', '<', nor '>' in the TEXT content). */ ?>
<?php /* When in doubt, use htmlspecialchars() around the TEXT (not around the tags). */ ?>

<h2>Legal notice</h2>
<p>
    <?= htmlspecialchars('This website is hosted by [NAME OF THE HOSTING COMPANY]') ?>
</p>
<blockquote>
    <?= htmlspecialchars('[LEGAL NAME OF THE HOSTING COMPANY]') ?> <br/>
    <?= htmlspecialchars('[STREET ADDRESS]') ?> <br/>
    <?= htmlspecialchars('[POSTCODE] [CITY] [COUNTRY]') ?> <br/>
</blockquote>
