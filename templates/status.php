<!DOCTYPE html>
<html lang="en">
    <head>
        <?php /** @var \JkaServerStatus\JkaServer\StatusData $data */ ?>
        <?php $title = strip_colors($data->serverName); ?>
        <?php require_once __DIR__ . '/_head.php'; ?>
    </head>
    <body>
        <noscript>
            <?php /* If JavaScript is enabled, let main.js add the background image, */ ?>
            <?php /* to avoid fetching an image that might not get displayed (depending on the user's settings). */ ?>
            <div id="background-image"
                 style="<?= 'background-image: url(' . asset($data->backgroundImageUrl) . '); '
                          . 'filter: blur(' . (int)$data->backgroundImageBlurRadius . 'px); '
                          . 'opacity: ' . (int)$data->backgroundImageOpacity . '%; '
                        ?>" >
            </div>
        </noscript>
        <input type="hidden" id="map-background-image"
               value="<?= htmlspecialchars(asset($data->backgroundImageUrl)) ?>" />
        <input type="hidden" id="map-background-image-blur-radius"
               value="<?= (int)$data->backgroundImageBlurRadius ?>" />
        <input type="hidden" id="map-background-image-opacity"
               value="<?= (int)$data->backgroundImageOpacity ?>" />
        <input type="hidden" id="default-background-image"
               value="<?= htmlspecialchars(asset($data->defaultBackgroundImageUrl)) ?>" />
        <input type="hidden" id="default-background-image-blur-radius"
               value="<?= (int)$data->defaultBackgroundImageBlurRadius ?>" />
        <input type="hidden" id="default-background-image-opacity"
               value="<?= (int)$data->defaultBackgroundImageOpacity ?>" />

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

            <article id="main-content">
                <h1><?= format_name($data->serverName); ?></h1>
                
                <p class="info"> 
                    <label><strong>Address:</strong></label>
                    <span><?= htmlspecialchars($data->address); ?><br/></span>

                    <label><strong>Status:</strong></label>
                    <span class="status">
                        <?php if ($data->isUp): ?>
                            <img src="<?= asset('/checkmark-circle.svg') ?>"
                                 width="20" height="20" alt="" aria-hidden="true"/>
                        <?php else: ?>
                            <img src="<?= asset('/alert-circle.svg') ?>"
                                 width="20" height="20" alt="" aria-hidden="true"/>
                        <?php endif; ?>
                        <?= htmlspecialchars($data->status); ?>
                        <br/>
                    </span>

                    <?php if (isset($data->mapName)): ?>
                        <label><strong>Map name:</strong></label>
                        <span><?= htmlspecialchars($data->mapName); ?><br/></span>
                    <?php endif; ?>

                    <?php if (isset($data->gameType)): ?>
                        <label><strong>Game type:</strong></label>
                        <span><?= htmlspecialchars($data->gameType); ?><br/></span>
                    <?php endif; ?>

                    <?php if (isset($data->gameName)): ?>
                        <label><strong>Mod name:</strong></label>
                        <span><?= format_name($data->gameName); ?><br/></span>
                    <?php endif; ?>

                    <?php if (isset($data->nbPlayers)): ?>
                        <label><strong>Players:</strong></label>
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
                            <br/>
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
            </article>

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

                <?php if ($data->isAboutPageEnabled): ?>
                    <p class="footnote bonus-info">
                        <a href="<?= htmlspecialchars($data->aboutPageUri) ?>">
                            <?= htmlspecialchars($data->aboutPageTitle); ?>
                        </a>
                    </p>
                <?php endif; ?>
            </footer>
        </div>

        <div id="settings" style="display: none;">
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
                
                <label class="background-image-tweak">Blur and opacity:</label>
                <span class="background-image-tweak">
                    <input type="radio" name="blur-and-opacity" id="auto-blur-and-opacity" value="Auto"/>
                    <label for="auto-blur-and-opacity">Auto</label>
                    <input type="radio" name="blur-and-opacity" id="custom-blur-and-opacity" value="Custom"/>
                    <label for="custom-blur-and-opacity">Custom</label>
                </span>

                <label for="background-image-blur-slider" class="background-image-tweak background-image-custom-tweak">
                    Image blur radius:
                </label>
                <span class="slider-container background-image-tweak background-image-custom-tweak">
                    <input id="background-image-blur-slider" type="range" min="0" max="10" />
                    <span id="background-image-blur-radius"></span>
                </span>
                
                <label for="background-image-opacity-slider"
                       class="background-image-tweak background-image-custom-tweak">Image opacity:</label>
                <span class="slider-container background-image-tweak background-image-custom-tweak">
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
            <div id="cvars" style="display: none;">
                <h2>
                    <img src="<?= asset('/terminal-sharp.svg') ?>"
                         width="24" height="24" alt="" aria-hidden="true"/>
                    Server info
                </h2>
                <div id="cvar-container">
                    <table id="cvar-table">
                        <?php foreach ($data->cvars as $cvar_name => $cvar_value): ?>
                            <tr>
                                <th><?= htmlspecialchars($cvar_name) ?></th>
                                <td><?= format_name($cvar_value) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
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
