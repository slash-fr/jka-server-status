# Basic `config.php` examples - JKA Server Status

When installing **JKA Server Status**, you need to create a `config.php` file at the root of the project
(next to `config.sample.php`).

## Minimal config - 1 server
If you only want to track **one** server, the absolute minimum you need is:
```php
<?php // config.php

$jka_servers = [
    [
        'address' => '192.0.2.1'
    ]
];
```
- `config.php` **must** start with `<?php`
- You don't need to add a PHP closing tag (`?>`). In fact, **you shouldn't**.
- There are **two** opening and closing square brackets (`[` and `]`),
  because `$jka_servers` allows you to declare multiple servers.
- PHP supports both single quotes (`'`) and double quotes (`"`) as string delimiters.
- `address` can be:
   - an IP address (e.g. `'192.0.2.1'`)
   - IP + port (e.g. `'192.0.2.1:29071'`)
   - a domain name (e.g. `'jka.example.com'`)
   - domain name + port (e.g. `'jka.example.com:29071'`)
- If the port is omitted, it defaults to **29070**.
- Your server will be listed on the root URL (`/`).

## Minimal config - 2 servers
```php
<?php // config.php

$jka_servers = [
    [
        'uri' => '/main-server',
        'address' => '192.0.2.1',
    ],
    [
        'uri' => '/secondary-server',
        'address' => 'jka.example.com:29071',
    ],
];
```
- You will get a "landing page" (server list) on the root URL (`/`), and the two servers on the specified URIs.
- PHP allows trailing commas (`,`) even on the last element of each array.

## Specifying a server name
Specifying a server name is recommended.
```php
<?php // config.php

$jka_servers = [
    [
        'uri' => '/main-server',
        'address' => '192.0.2.1',
        'name' => '^5M^7ain ^5S^7erver',
    ],
    [
        'uri' => '/secondary-server',
        'address' => 'jka.example.com:29071',
        'name' => '^3Secondary ^7Server',
    ],
];
```
- The server name supports **color codes**.
- It's used:
  - On the **landing page** (server list),
  - As a **page title** for the status page, **if the request fails** (timeout, server down, ...).
- If you don't specify a name, the `address` will be used instead.

## More settings
For a full list of options, please have a look at [`config.sample.php`](../config.sample.php).

## Notes
- `config.php` is listed in `.gitignore`, so you won't commit it accidentally.