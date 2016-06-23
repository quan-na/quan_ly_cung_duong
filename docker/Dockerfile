FROM debian:latest

MAINTAINER Nguyen Anh Quan

# Install php5 apache2 sshd
RUN apt-get update && apt-get upgrade -y && apt-get install -y php5 apache2 php5-mysql git openssh-server

# Create src folder and clone source code
RUN mkdir -p /src && cd /src && git clone https://github.com/quan-na/quan_ly_cung_duong.git
WORKDIR /src

# Install composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && php composer-setup.php && php -r "unlink('composer-setup.php');"

# call composer install in project directory
RUN cd quan_ly_cung_duong && php ../composer.phar install

# create web context pointing to public directory
RUN a2enmod rewrite
RUN touch /etc/apache2/sites-available/001-quanlycd.conf
RUN echo '<VirtualHost *:8080>' > /etc/apache2/sites-available/001-quanlycd.conf
RUN echo 'ServerAdmin quan-na@github.com' >> /etc/apache2/sites-available/001-quanlycd.conf
RUN echo 'DocumentRoot /src/quan_ly_cung_duong/public' >> /etc/apache2/sites-available/001-quanlycd.conf
RUN echo 'AddType image/x-icon .ico' >> /etc/apache2/sites-available/001-quanlycd.conf
RUN echo 'ErrorLog ${APACHE_LOG_DIR}/error.qlcd.log' >> /etc/apache2/sites-available/001-quanlycd.conf
RUN echo 'CustomLog ${APACHE_LOG_DIR}/access.qlcd.log combined' >> /etc/apache2/sites-available/001-quanlycd.conf
RUN echo '</VirtualHost>' >> /etc/apache2/sites-available/001-quanlycd.conf
RUN ln -s /etc/apache2/sites-available/001-quanlycd.conf /etc/apache2/sites-enabled/001-quanlycd.conf

# enable root ssh login
RUN sed -i 's/PermitRootLogin without-password/PermitRootLogin yes/' /etc/ssh/sshd_config

# start services
ENTRYPOINT service apache2 start && service ssh start && bash

# expose ports
EXPOSE 22
EXPOSE 8080
