<!DOCTYPE html>
<html lang="en">
    <head>
        <?php /** @var StatusData $data */ ?>
        <?php $title = strip_colors($data->serverName); ?>
        <?php require_once __DIR__ . '/_head.php'; ?>
    </head>
    <body>
        <noscript>
            <!-- If JavaScript is enabled, let main.js add the background image, -->
            <!-- to avoid fetching an image that might not get displayed (depending on the user's settings). -->
            <div id="background-image"
                 style="background-image: url(<?= asset($data->backgroundImageUrl) ?>)" />
            </div>
        </noscript>
        <input type="hidden" id="current-background-image"
               value="<?= htmlspecialchars(asset($data->backgroundImageUrl)) ?>" />
        <input type="hidden" id="default-background-image"
               value="<?= htmlspecialchars(asset($data->defaultBackgroundImageUrl)) ?>" />

        <div id="content">
            <header>
                <?php if (!empty($data->isLandingPageEnabled)): ?>
                    <a href="<?= htmlspecialchars($data->landingPageUri); ?>"
                       id="home-button" class="button" title="Go back to the server list">

                        <img src="<?= asset('/favicon.svg') ?>"
                             width="16" height="16" alt="" aria-hidden="true" />
                        Server list
                    </a>
                <?php endif; ?>
            </header>

            <div id="main-content">
                <h1><?= format_name($data->serverName); ?></h1>
                
                <p class="info"> 
                    <label>Address:</label> <span><?= htmlspecialchars($data->address); ?></span>

                    <label>Status:</label>
                    <span class="status">
                        <?php if ($data->isUp): ?>
                            <img src="<?= asset('/checkmark-circle.svg') ?>"
                                 width="20" height="20" alt="" aria-hidden="true"/>
                        <?php else: ?>
                            <img src="<?= asset('/alert-circle.svg') ?>"
                                 width="20" height="20" alt="" aria-hidden="true"/>
                        <?php endif; ?>
                        <?= htmlspecialchars($data->status); ?>
                    </span>

                    <?php if (isset($data->mapName)): ?>
                        <label>Map name:</label> <span><?= htmlspecialchars($data->mapName); ?></span>
                    <?php endif; ?>

                    <?php if (isset($data->gameType)): ?>
                        <label>Game type:</label> <span><?= htmlspecialchars($data->gameType); ?></span>
                    <?php endif; ?>

                    <?php if (isset($data->gameName)): ?>
                        <label>Mod name:</label> <span><?= format_name($data->gameName); ?></span>
                    <?php endif; ?>

                    <?php if (isset($data->nbPlayers)): ?>
                        <label>Players:</label>
                        <span>
                            <?= (int)$data->nbPlayers ?>
                            <?php if (isset($data->maxPlayers)): ?>
                                / <?= (int)$data->maxPlayers ?>
                            <?php endif; ?>
                            <?php if (isset($data->nbHumans)): ?>
                                <span class="bonus-info">
                                    (<?= $data->nbHumans . ' ' . ($data->nbHumans === 1  ? 'human' : 'humans') ?>)
                                </span>
                            <?php endif; ?>
                        </span>
                    <?php endif; ?>
                </p>

                <?php if (isset($data->nbPlayers) && $data->nbPlayers > 0): ?>
                    <table class="player-list">
                        <thead>
                            <tr><th>Name</th><th class="score">Score</th><th class="ping">Ping</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data->players as $player): ?>
                                <tr>
                                    <td><?= format_name($player->name); ?></td>
                                    <td><?= htmlspecialchars($player->score); ?></td>
                                    <td><?= htmlspecialchars($player->ping); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            <footer>
                <p id="refreshed-footer" class="bonus-info"></p>
                <p id="settings-footer">
                    <button id="refresh-button" onclick="location.reload()">
                        <img src="<?= asset('/refresh.svg') ?>"
                             width="20" height="20" alt="" aria-hidden="true"/>
                        Refresh
                    </button>
                    <?php if ($data->cvars): ?>
                        <button id="open-cvars">
                            <img src="<?= asset('/terminal-20x20.png') ?>"
                                 srcset="<?= asset('/terminal-40x40.png') ?> 2x"
                                 width="20" height="20" alt="" aria-hidden="true"/>
                            Server info
                        </button>
                    <?php endif; ?>
                    <button id="open-settings">
                        <img src="<?= asset('/settings-sharp.svg') ?>"
                             width="20" height="20" alt="" aria-hidden="true"/>
                        Settings
                    </button>
                </p>
            </footer>
        </div>
        <div id="settings">
            <h2>
                <img src="<?= asset('/settings-sharp.svg') ?>"
                     width="24" height="24" alt="" aria-hidden="true"/>
                Settings
            </h2>
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
                <img src="<?= asset('/close-circle.svg') ?>"
                     width="20" height="20" alt="" aria-hidden="true"/>
                Close settings
            </button>
        </div>
        <?php if ($data->cvars): ?>
            <div id="cvars">
                <h2>
                    <img src="<?= asset('/terminal-sharp.svg') ?>"
                         width="24" height="24" alt="" aria-hidden="true"/>
                    Server info
                </h2>
                <div id="cvar-grid">
                    <?php foreach ($data->cvars as $cvar_name => $cvar_value): ?>
                        <label><?= htmlspecialchars($cvar_name) ?></label>
                        <span><?= format_name($cvar_value) ?></span>
                    <?php endforeach; ?>
                </div>
                <button id="close-cvars">
                    <img src="<?= asset('/close-circle.svg') ?>"
                         width="20" height="20" alt="" aria-hidden="true"/>
                    Close server info
                </button>
            </div>
        <?php endif; ?>
        <script src="<?= asset('/main.js') ?>"></script>
    </body>
</html>
