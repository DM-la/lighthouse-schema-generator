FROM php:8-cli

WORKDIR /workdir

RUN apt-get update && apt-get install -y \
        git \
        libzip-dev \
        zip \
        libicu-dev \
    && docker-php-ext-install \
        zip \
        mysqli \
        pdo_mysql \
        intl \
    && rm -rf /var/lib/apt/lists/*

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer

RUN pecl install \
        xdebug \
    && docker-php-ext-enable \
        xdebug

RUN echo 'memory_limit=-1' > /usr/local/etc/php/conf.d/lighthouse-schema-generator.ini
#
#ARG USER
#ARG USER_ID
#ARG GROUP_ID
#
#RUN if [ ${USER_ID:-0} -ne 0 ] && [ ${GROUP_ID:-0} -ne 0 ]; then \
#    groupadd --force --gid ${GROUP_ID} ${USER} &&\
#    useradd --no-log-init --uid ${USER_ID} --gid ${GROUP_ID} ${USER} &&\
#    install --directory --mode 0755 --owner ${USER} --group ${GROUP_ID} /home/${USER} &&\
#    chown --changes --silent --no-dereference --recursive ${USER_ID}:${GROUP_ID} /home/${USER} \
#;fi
#
#USER ${USER}