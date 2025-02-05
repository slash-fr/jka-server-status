<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8"/>
        <title>
            JKA Server Status
            <?php if (isset($data['cvars']['sv_hostname'])): ?>
                - <?= htmlspecialchars(strip_colors($data['cvars']['sv_hostname'])) ?>
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
            <div id="background-image" style="background-image: url(<?= $data['background_image_url'] ?>?version=1)" /></div>
            <?php /* The query string ("?version=1") is used for cache busting purposes */ ?>
        </noscript>
        <input type="hidden" id="current-background-image" value="<?= $data['background_image_url'] ?>?version=1" />
        <input type="hidden" id="default-background-image" value="<?= $data['default_background_image_url'] ?>?version=1" />

        <div id="content">
            <?php if (isset($data['cvars']['sv_hostname'])): ?>
                <h1><?= format_name($data['cvars']['sv_hostname']); ?></h1>
            <?php else: ?>
                <h1><span class="mono white">JKA Server Status</span></h1>
            <?php endif; ?>
            
            <p class="info"> 
                <label>Address:</label> <span><?= htmlspecialchars($data['address']); ?></span>

                <label>Status:</label>
                <span class="status">
                    <?php if ($data['is_up']): ?>
                        <img src="<?= ROOT_URL ?>checkmark-circle.svg" width="20" height="20" alt="" aria-hidden="true"/>
                    <?php else: ?>
                        <img src="<?= ROOT_URL ?>alert-circle.svg" width="20" height="20" alt="" aria-hidden="true"/>
                    <?php endif; ?>
                    <?= htmlspecialchars($data['status']); ?>
                </span>

                <?php if (isset($data['cvars']['mapname'])): ?>
                    <label>Map:</label> <span><?= htmlspecialchars($data['cvars']['mapname']); ?></span>
                <?php endif; ?>

                <?php if (isset($data['game_type'])): ?>
                    <label>Game type:</label> <span><?= htmlspecialchars($data['game_type']); ?></span>
                <?php endif; ?>

                <?php if (isset($data['cvars']['gamename'])): ?>
                    <label>Mod:</label> <span><?= format_name($data['cvars']['gamename'], false); ?></span>
                <?php endif; ?>

                <?php if (isset($data['nb_players'])): ?>
                    <label>Players:</label>
                    <span>
                        <?= (int)$data['nb_players'] ?>
                        <?php if (isset($data['cvars']['sv_maxclients'])): ?>
                            / <?= (int)$data['cvars']['sv_maxclients'] ?>
                        <?php endif; ?>
                        <?php if (isset($data['nb_humans'])): ?>
                            <span class="bonus-info">
                                (<?= $data['nb_humans'] . ' ' . ($data['nb_humans'] == 1  ? 'human' : 'humans') ?>)
                            </span>
                        <?php endif; ?>
                    </span>
                <?php endif; ?>
            </p>

            <?php if (isset($data['nb_players']) && $data['nb_players'] > 0): ?>
                <table class="player-list">
                    <thead>
                        <tr><th>Name</th><th class="score">Score</th><th class="ping">Ping</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['players'] as $player): ?>
                            <tr>
                                <td><?= format_name($player['name']); ?></td>
                                <td><?= htmlspecialchars($player['score']); ?></td>
                                <td><?= htmlspecialchars($player['ping']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
            <p id="refreshed-footer" class="bonus-info"></p>
            <p id="settings-footer">
                <button id="refresh-button" onclick="location.reload()">
                    <img src="<?= ROOT_URL ?>refresh.svg" width="20" height="20" alt="" aria-hidden="true"/> Refresh
                </button>
                <button id="open-settings">
                    <img src="<?= ROOT_URL ?>settings-sharp.svg" width="20" height="20" alt="" aria-hidden="true"/> Settings
                </button>
                <?php if (isset($data['cvars'])): ?>
                    <button id="open-cvars">
                        <img src="<?= ROOT_URL ?>terminal-20x20.png" srcset="<?= ROOT_URL ?>terminal-40x40.png 2x" width="20" height="20" alt="" aria-hidden="true"/>
                        Show raw cvars
                    </button>
                <?php endif; ?>
            </p>
        </div>
        <div id="settings">
            <p class="title mono">
                <img src="<?= ROOT_URL ?>settings-sharp.svg" width="24" height="24" alt="" aria-hidden="true"/>
                Settings
            </p>
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
            </p>
            <button id="close-settings">
                <img src="<?= ROOT_URL ?>close-circle.svg" width="20" height="20" alt="" aria-hidden="true"/>
                Close settings
            </button>
        </div>
        <?php if (isset($data['cvars'])): ?>
            <div id="cvars">
                <p class="title mono">
                    <img src="<?= ROOT_URL ?>terminal-sharp.svg" width="24" height="24" alt="" aria-hidden="true"/>
                    Raw cvars
                </p>
                <div id="cvar-grid">
                    <?php foreach ($data['cvars'] as $cvar_name => $cvar_value): ?>
                        <label><?= htmlspecialchars($cvar_name) ?></label>
                        <span><?= htmlspecialchars($cvar_value) ?></span>
                    <?php endforeach; ?>
                </div>
                <button id="close-cvars">
                    <img src="<?= ROOT_URL ?>close-circle.svg" width="20" height="20" alt="" aria-hidden="true"/>
                    Close raw cvars
                </button>
            </div>
        <?php endif; ?>
        <script src="<?= ROOT_URL ?>main.js?version=1"></script>
        <?php /* The query string ("?version=1") is used for cache busting purposes */ ?>
    </body>
</html>
