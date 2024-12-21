<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8"/>
        <title>
            JKA Server Status
            <?php if (isset($qstat->server->name)): ?>
                - <?= htmlspecialchars(strip_colors($qstat->server->name)) ?>
            <?php endif; ?>
        </title>
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
    <body>
        <noscript>
            <!-- If JavaScript is enabled, let main.js add the background image, -->
            <!-- to avoid fetching an image that might not get displayed (depending on the user's settings). -->
            <div id="background-image" style="background-image: url(<?= $background_image_url ?>?version=1)" /></div>
            <?php /* The query string ("?version=1") is used for cache busting purposes */ ?>
        </noscript>
        <input type="hidden" id="current-background-image" value="<?= $background_image_url ?>?version=1" />
        <input type="hidden" id="default-background-image" value="<?= $default_background_image_url ?>?version=1" />

        <div id="content">
            <?php if (isset($qstat->server->name)): ?>
                <h1><?= format_name(trim($qstat->server->name, '€')); ?></h1>
            <?php else: ?>
                <h1><span class="mono white">JKA Server Status</span></h1>
            <?php endif; ?>
            
            <p class="info"> 
                <strong>Address:</strong> <?= htmlspecialchars($qstat->server['address'] ?? 'ERROR'); ?><br/>

                <strong>Status:</strong>
                <?php if ($qstat->server['status'] == 'UP'): ?>
                    ✅
                <?php else: ?>
                    ❌
                <?php endif; ?>
                <?= htmlspecialchars($qstat->server['status'] ?? 'ERROR'); ?><br/>

                <?php if (isset($qstat->server->map)): ?>
                    <strong>Map:</strong> <?= htmlspecialchars($qstat->server->map); ?><br/>
                <?php endif; ?>

                <?php if (isset($qstat->server->gametype)): ?>
                    <strong>Game type:</strong> <?= htmlspecialchars($qstat->server->gametype); ?><br/>
                <?php endif; ?>

                <?php if (isset($qstat->server->numplayers) && isset($qstat->server->maxplayers)): ?>
                    <strong>Players:</strong>
                    <?= (int)$qstat->server->numplayers ?> / <?= (int)$qstat->server->maxplayers ?>
                    <?php if($nb_bots): ?>
                        <br/>
                        <span class="bonus-info">
                            (<?= (int)$nb_humans?>&nbsp;<?= ($nb_humans === 1) ? 'human' : 'humans'?>
                            +&nbsp;<?= (int)$nb_bots ?>&nbsp;<?= ($nb_bots === 1) ? 'bot' : 'bots'?>)
                        </span>
                    <?php endif; ?>
                    <br/>
                <?php endif; ?>
            </p>

            <?php if (isset($qstat->server->numplayers) && $qstat->server->numplayers > 0): ?>
                <table class="player-list">
                    <thead>
                        <tr><th>Name</th><th class="score">Score</th><th class="ping">Ping</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($players as $player): ?>
                            <tr>
                                <td><?= format_name($player->name ?? ''); ?></td>
                                <td><?= htmlspecialchars($player->score ?? '0'); ?></td>
                                <td><?= htmlspecialchars($player->ping ?? 'N/A'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
            <p id="refreshed-footer" class="bonus-info"></p>
            <p id="settings-footer">
                <button id="open-settings" class="mono bonus-info">Settings…</button>
            </p>
        </div>
        <div id="settings">
            <p class="title mono">⚙️ Settings</p>
            <p id="setting-grid">
                <label for="auto-refresh-select">Auto-refresh:</label>
                <select id="auto-refresh-select">
                    <option value="0">Disabled</option>
                    <option value="1">Every minute</option>
                    <option value="2">Every 2 minutes</option>
                    <option value="5">Every 5 minutes</option>
                    <option value="10">Every 10 minutes</option>
                    <option value="20">Every 20 minutes</option>
                    <option value="30">Every 30 minutes</option>
                    <option value="60">Every hour</option>
                </select>
                
                <label for="background-image-select">Background image:</label>
                <select id="background-image-select">
                    <option value="disabled">Disabled</option>
                    <option value="map-dependent">Map-dependent</option>
                    <option value="always-default">Always use "default.jpg"</option>
                </select>
                
                <label for="background-image-blur-slider" class="background-image-tweak">Image blur:</label>
                <span class="slider-container background-image-tweak">
                    <input id="background-image-blur-slider" type="range" min="0" max="10" />
                    <span id="background-image-blur-radius"></span>
                </span>
                
                <label for="background-image-opacity-slider" class="background-image-tweak">Image opacity:</label>
                <span class="slider-container background-image-tweak">
                    <input id="background-image-opacity-slider" type="range" min="0" max="100" />
                    <span id="background-image-opacity-percentage"></span>
                </span>
                
                <label for="background-color-input">Background color:</label>
                <input id="background-color-input" type="color" />

                <span></span>
                <span class="form-button-span"><button id="close-settings">Close settings</button></span>
            </p>
        </div>
        <script src="<?= ROOT_URL ?>main.js?version=1"></script>
        <?php /* The query string ("?version=1") is used for cache busting purposes */ ?>
    </body>
</html>
