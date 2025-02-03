FROM drupal:php8.3-fpm-bullseye

# Set the argument for non-interactive frontend
ARG DEBIAN_FRONTEND=noninteractive

USER root

# Update package list and install necessary packages
RUN apt-get update -yq \
    && apt-get dist-upgrade -yq \
    # Install curl for future downloads
    && apt-get install curl sudo -yq \
    # Install MariaDB server and client instead of MySQL
    && apt-get install mariadb-server mariadb-client -yq \
    # Install some common packages
    && apt-get install git imagemagick nano vim openssh-server sqlite3 wget unzip -yq \
    # Install PHP CLI with some extensions for Drupal development
    # Install NodeJS LTS
    && curl -sL https://deb.nodesource.com/setup_lts.x | bash \
    && apt-get install nodejs -yq \
    # Install Composer
    && wget https://getcomposer.org/installer \
    && php installer --install-dir=/usr/local/bin --filename=composer \
    # Install global Drush launcher
    && wget -O /usr/local/bin/drush https://github.com/drush-ops/drush-launcher/releases/latest/download/drush.phar \
    && chmod +x /usr/local/bin/drush \
    # Clean up some things
    && rm installer \
    && rm -rf /var/lib/apt/lists/* \
    # Create a user without password flag, then set the password explicitly
    && useradd -m drupal \
    # Set password for drupal user
    && echo "drupal:drupal0713" | chpasswd \
    # Give `drupal` user sudo permissions
    && usermod -aG sudo drupal \
    # Change ownership of PHP config files to `drupal` user
    && chown -R drupal:drupal /usr/local/etc/php

# Switch to non-root user for the rest of the work
USER drupal
ENV SHELL /bin/bash
