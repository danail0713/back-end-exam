FROM drupal:php8.3-fpm-bullseye

# Set the argument for non-interactive frontend
ARG DEBIAN_FRONTEND=noninteractive

USER root

# Update package list and install necessary packages
RUN apt-get update -yq \
    && apt-get dist-upgrade -yq \
    # Install necessary dependencies
    && apt-get install -yq curl sudo mariadb-server mariadb-client gnupg \
    && apt-get install -yq git imagemagick nano vim openssh-server sqlite3 wget unzip lsb-release \
    # Install NodeJS LTS
    && curl -sL https://deb.nodesource.com/setup_lts.x | bash \
    && apt-get install -yq nodejs \
    # Install Composer
    && wget https://getcomposer.org/installer \
    && php installer --install-dir=/usr/local/bin --filename=composer \
    # Install Drush
    && wget -O /usr/local/bin/drush https://github.com/drush-ops/drush-launcher/releases/latest/download/drush.phar \
    && chmod +x /usr/local/bin/drush \
    # Add MongoDB repository and install MongoDB
    && curl -fsSL https://www.mongodb.org/static/pgp/server-6.0.asc | gpg --dearmour -o /usr/share/keyrings/mongodb-keyring.gpg \
    && echo "deb [signed-by=/usr/share/keyrings/mongodb-keyring.gpg] https://repo.mongodb.org/apt/debian bullseye/mongodb-org/6.0 main" | tee /etc/apt/sources.list.d/mongodb-org-6.0.list \
    && apt-get update -yq \
    && apt-get install -yq mongodb-org \
    # Clean up unnecessary files
    && rm installer \
    && rm -rf /var/lib/apt/lists/* \
    # Create a new Drupal user
    && useradd -m drupal \
    # Set password for drupal user
    && echo "drupal:drupal0713" | chpasswd \
    # Give `drupal` user sudo permissions
    && usermod -aG sudo drupal \
    # Change ownership of PHP config files to `drupal` user
    && chown -R drupal:drupal /usr/local/etc/php

# ✅ Copy the entrypoint script & set permissions
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# Expose necessary ports
EXPOSE 3306 27017

# ✅ Ensure correct ENTRYPOINT
RUN /entrypoint.sh
CMD ["php-fpm"]
