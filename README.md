# JKA Server Status - (PHP)
This tool can send requests to a **Jedi Academy** server, to retrieve some basic server info,
as well as the player list.

![Player list screenshot (desktop)](doc/screenshots/desktop-player-list-thumbnail.jpg)

[→ Click here for more screenshots](doc/screenshots/screenshots.md)

## Features
- **Server status** info,
- **Player list**,
- List of `cvars` (**Server info** button),
- **Background images** from my [**Widescreen levelshots**](https://jkhub.org/files/file/4179-widescreen-levelshots/)
  pack,  
  → Blurred and dimmed, to improve readability,
- Optional **auto-refresh** (user-configurable),
- **Responsive** layout,
- **Server-side caching** (configurable, 10 seconds by default).

## Requirements
- A **web server** (Nginx, Apache, ...)
  - with URL rewriting capabilities
- **PHP 8.1** or newer
  - with enough permissions to send outgoing UDP packets  
    (not necessarily the case on shared hosts)

There are no other dependencies.

## Installation
1. Clone (or copy the content of) this repository onto your server,
2. Setup your web server → See: [**Web server configuration**](doc/01-web-server-configuration.md),
3. Make sure PHP has write access to the `var/cache` and `var/log` folders,
4. Create a `config.php` file → Read: [**Basic `config.php` examples**](doc/02-basic-config-php-examples.md).


## PHPUnit
There are a few automated tests for PHP developers, in the `tests` directory.
```sh
wget -O phpunit.phar https://phar.phpunit.de/phpunit-12.phar
php phpunit.phar --bootstrap src/autoload.php tests
```
It's recommended to run them on your development machine, **not** on your production server.

## License
The code in this repository is released under the terms of the [MIT License](LICENSE.txt).

## Credits
- PHP / HTML / CSS / JS / Levelshots by [**Slash**](https://github.com/slash-fr)
- `default.jpg` is based on the following photo (CC0 Public Domain): https://pxhere.com/en/photo/57901  
  → Slightly tweaked by Slash
- Some icons are based on the [**ionicons pack**](https://ionic.io/ionicons) (MIT licensed).
