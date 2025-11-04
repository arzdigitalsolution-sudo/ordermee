#!/usr/bin/env bash
set -euo pipefail

sudo apt update
sudo apt install -y apache2 php php-mysql php-xml php-mbstring php-curl php-zip mysql-server unzip git curl

sudo a2enmod rewrite ssl headers

sudo systemctl enable apache2
sudo systemctl restart apache2

sudo mysql -e "CREATE DATABASE IF NOT EXISTS clickcart CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

if [ ! -f /etc/apache2/sites-available/clickcart.conf ]; then
cat <<'EOF' | sudo tee /etc/apache2/sites-available/clickcart.conf
<VirtualHost *:80>
    ServerName clickcart.pk
    DocumentRoot /var/www/clickcart/public

    <Directory /var/www/clickcart/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/clickcart-error.log
    CustomLog ${APACHE_LOG_DIR}/clickcart-access.log combined
</VirtualHost>
EOF

    sudo a2ensite clickcart.conf
    sudo a2dissite 000-default.conf || true
fi

sudo systemctl reload apache2

echo "LAMP stack ready. Deploy project under /var/www/clickcart." 
