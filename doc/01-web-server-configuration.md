# Web server configuration - JKA Server Status

## Table of contents
- [**Introduction**](#introduction)
- [Option 1 - **Nginx + PHP-FPM** (dedicated server or VPS)](#nginx)
- [Option 2 - **Apache + PHP-FPM** (dedicated server or VPS)](#apache-php-fpm)
- [Option 3 - **Apache `.htaccess`** (shared host ⚠️)](#htaccess)

## <a name="introduction"></a>Introduction
This document provides configuration instructions/files demonstrating:
- How to forward requests to PHP,
- A suggested caching strategy,
- HTML / CSS / JS compression,
- Conditional JPEG / AVIF serving,

for **Apache** and **Nginx**.

### General requirements
- Your web server must have **URL-rewriting** capabilities,
- PHP must be allowed to send/receive packets to/from remote **UDP sockets**,
- PHP must be compiled with `iconv` and `PCRE` (which is normally the case),
- Make sure PHP has **write access** to the `var/cache` and `var/log` directories,
- Don't forget to also create a `config.php` file
  (see: [**Basic `config.php` examples**](02-basic-config-php-examples.md)).


## <a name="nginx"></a>Option 1 - Nginx + PHP-FPM (dedicated server or VPS)
1. Packages to install on Debian 12:
   ```sh
   sudo apt install nginx-light
   sudo apt install php-fpm
   ```
  
2. Copy the content of [`nginx.sample.conf`](web-server-sample-config/nginx.sample.conf) to (for instance):
   ```
   /etc/nginx/sites-available/jka-server-status.conf
   ```
   and adjust it to your needs.

3. Enable your new Nginx config:
   ```sh
   sudo ln -s /etc/nginx/sites-available/jka-server-status.conf /etc/nginx/sites-enabled/
   sudo systemctl reload nginx
   ```


## <a name="apache-php-fpm"></a>Option 2 - Apache + PHP-FPM (dedicated server or VPS)
For performance reasons, you should use **PHP-FPM** (rather than `mod_php`).

1. Packages to install (and modules to enable) on Debian 12:
   ```sh
   sudo apt install apache2
   sudo apt install php-fpm
   sudo a2enmod rewrite dir filter deflate headers proxy proxy_fcgi
   sudo a2enconf php8.2-fpm
   sudo systemctl restart apache2
   ```

2. Copy the content of [`apache-virtualhost.sample.conf`](web-server-sample-config/apache-virtualhost.sample.conf) to
   (for example):
   ```
   /etc/apache2/sites-available/jka-server-status.conf
   ```
   and edit it to suit your needs.

3. Enable it:
   ```sh
   sudo a2ensite jka-server-status.conf
   sudo systemctl reload apache2
   ```

## <a name="htaccess"></a>Option 3 - Apache `.htaccess` (shared host ⚠️)
> ⚠️ **Warning:** shared hosts often block outgoing UDP packets,
> which makes **JKA Server Status** unlikely to work on a shared host.

Additionally, relying on `.htaccess` files is **not recommended**, for performance reasons.  
If you can, use **Nginx** + **PHP-FPM**, or **Apache** with a proper `VirtualHost` + **PHP-FPM**.

If you're on a shared host, though, you won't have access to the `VirtualHost` configuration.  
Which is why there's a [`.htaccess`](../.htaccess) file at the root of the project.
Feel free to modify it to suit your needs.

**Required Apache version:** 2.4 or newer

**Required** modules:
- `mod_rewrite`
- `mod_dir`

**Strongly recommended** modules:
- `mod_filter`
- `mod_deflate`
- `mod_headers`
