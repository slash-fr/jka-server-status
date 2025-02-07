# JKA Server Status - PHP Script
This tool can send requests to a **Jedi Academy** server, to retrieve some basic server info,
as well as the player list.

[![Screenshot thumbnail](doc/screenshot-thumbnail.jpg)](doc/screenshot.jpg)

## Features
- Retrieves the following information from the server:
    - Server name (with colors)
    - Status
    - Map name
    - Game type
    - Mod name (with colors)
    - Number of players
    - Player list
        - Name (with colors)
        - Score
        - Ping
- Can also display all `cvars` received from the server,
- Caches responses for 10 seconds (server-side)
- Uses backgrounds from my [**Widescreen levelshots**](https://jkhub.org/files/file/4179-widescreen-levelshots/) pack
    - 1920x1080 resolution, available in JPG + AVIF formats,
    - User-configurable blur and opacity, to improve the readability,
    - Uses `default.jpg` when the map name doesn't match a known levelshot
    - You can add your own backgrounds in the `levelshots` folder, and they should be automatically detected
- Responsive layout
- Optional auto-refresh (user-configurable)

[![Settings screenshot thumbnail](doc/settings-thumbnail.jpg)](doc/settings.jpg)

[![Raw cvars screenshot thumbnail](doc/raw-cvars-thumbnail.jpg)](doc/raw-cvars.jpg)

## Installation
- Clone (or copy the content of) this repository onto your server,
- Setup your web server to point to the `public` folder,
- Copy `config.sample.php` to `config.php` and edit the configuration to suit your needs,
- Make sure PHP has write access to the `cache` and `log` folders.

## Sample Nginx config
Have a look at [`nginx.sample.conf`](doc/nginx.sample.conf) for an example of:
- Conditional JPEG / AVIF serving
- HTML / CSS / JS compression
- Caching strategy
- Forwarding requests to PHP

Minimum required packages on Debian 12:
```sh
sudo apt install nginx-light
sudo apt install php-fpm
```

## License
The code in this repository is released under the [MIT License](LICENSE.txt).

## Credits
- PHP / HTML / CSS / JS / Levelshots by [**Slash**](https://github.com/slash-fr)
- `default.jpg` is based on the following photo (CC0 Public Domain): https://pxhere.com/en/photo/57901  
  → Slightly tweaked by Slash
- Some icons are based on the [**ionicons pack**](https://ionic.io/ionicons) (MIT licensed).
