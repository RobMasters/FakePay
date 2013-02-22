FakePay - The simple way to fake your payment process in a test environment
===========================================================================

FakePay currently requires PHP >= 5.4

## Installing

Clone this application and set up your local webserver so that ``http://fakepay.local`` (or whatever you'd like to use)
points to the ``web/index.php`` file. Also make sure to add the address to ``/etc/hosts``!

### Apache

Simply set your document root to the web directory, and the ``.htaccess`` will handle rewriting URLs to the ``index.php``
front controller.

e.g.

```
<VirtualHost *:80>
	ServerName fakepay.local
	ServerAlias www.fakepay.local
	DocumentRoot /path/to/FakePay/web
</VirtualHost>
```

### Nginx

The following basic configuration should do the trick:

```
server {
    server_name fakepay.local;
    root /path/to/FakePay/web;

        # site root is redirected to the app boot script
        location = / {
                try_files @site @site;
        }

        # all other locations try other files first and go to our front controller if none of them exists
        location / {
                try_files $uri $uri/ @site;
        }

        # return 404 for all php files as we do have a front controller
        location ~ \.php$ {
                return 404;
        }

        location @site {
                fastcgi_pass   unix:/var/run/php5-fpm.sock;
                include fastcgi_params;
                fastcgi_param SCRIPT_FILENAME $document_root/index.php;

                # un-comment the following line to use https
                # fastcgi_param HTTPS on;
        }
}
```
