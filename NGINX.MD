# Ubuntu Server 20.04

## Install nginx
sudo apt-get install nginx

## Install php7.4-fpm
sudo apt-get install php7.4-fpm


## Created config
server {
    listen <listen port>;
    server_name <server name>;
    root <path>; #example /var/www/backend/public

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-XSS-Protection "1; mode=block";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}

## Setting /etc/php/7.4/fpm/php.ini
post_max_size = 50M
upload_max_filesize = 50M

## Setting NGINX upload max /etc/nginx/nginx.conf
http {
    client_max_body_size 50M;
}

## Restart and reload config
sudo service php7.4-fpm restart
sudo service nginx restart

