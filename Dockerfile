FROM debian:12

# Install php and required extensions
RUN apt update -y && apt upgrade -y && apt install php8.2 php8.2-cli php8.2-mbstring php8.2-xml php8.2-curl php8.2-mysql php8.2-pdo php8.2-intl php8.2-zip php8.2-sqlite3 php8.2-bcmath php8.2-xdebug php8.2-fpm php8.2-gd -y

# Install Sqlite3
RUN apt install sqlite3 -y

# Install composer
RUN apt install curl git unzip -y
WORKDIR /var/www/application
RUN curl -sS https://getcomposer.org/installer -o composer-setup.php && \
    php composer-setup.php --install-dir=/usr/local/bin --filename=composer && \
    rm composer-setup.php
RUN curl -1sLf 'https://dl.cloudsmith.io/public/symfony/stable/setup.deb.sh' | bash && apt install symfony-cli

# Install various utilities
RUN apt install -y vim nano htop

# Add user
USER 1000:1000

# Volume creation (will be where source code is) : not necessary as defined at run time
VOLUME ["/var/www/application"]

EXPOSE 443
EXPOSE 80

CMD ["php", "-S", "0.0.0.0:8000", "web/front.php"]
