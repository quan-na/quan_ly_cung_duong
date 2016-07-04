FROM debian:latest

MAINTAINER https://github.com/quan-na

RUN apt-get update && apt-get upgrade -y
RUN apt-get install -y apache2 php5 php5-mysql
RUN apt-get install -y zip unzip
RUN apt-get install -y curl git

RUN mkdir -p /src
RUN mkdir -p /src/quan_ly_cung_duong && mkdir -p /src/quan_ly_cung_duong/public
RUN mkdir -p /var/log/qlcd && chmod 755 /var/log/qlcd && touch /var/log/qlcd/app.log && chmod 777 /var/log/qlcd/app.log
RUN chmod -R 755 /src
WORKDIR /src

RUN echo "<VirtualHost *:80>"                                        > /etc/apache2/sites-available/001-qlcd.conf
RUN echo "    ServerName local-qlcd"                                >> /etc/apache2/sites-available/001-qlcd.conf
RUN echo "    ServerAdmin naquan2112@gmail.com"                     >> /etc/apache2/sites-available/001-qlcd.conf
RUN echo "    DocumentRoot /src/quan_ly_cung_duong/public"          >> /etc/apache2/sites-available/001-qlcd.conf
RUN echo '    ErrorLog ${APACHE_LOG_DIR}/error.qlcd.log'            >> /etc/apache2/sites-available/001-qlcd.conf
RUN echo '    CustomLog ${APACHE_LOG_DIR}/access.qlcd.log combined' >> /etc/apache2/sites-available/001-qlcd.conf
RUN echo "    <Directory \"/src/quan_ly_cung_duong/public/\">"      >> /etc/apache2/sites-available/001-qlcd.conf
RUN echo "        Order allow,deny"                                 >> /etc/apache2/sites-available/001-qlcd.conf
RUN echo "        AllowOverride All"                                >> /etc/apache2/sites-available/001-qlcd.conf
RUN echo "        Allow from all"                                   >> /etc/apache2/sites-available/001-qlcd.conf
RUN echo "        Require all granted"                              >> /etc/apache2/sites-available/001-qlcd.conf
RUN echo "    </Directory>"                                         >> /etc/apache2/sites-available/001-qlcd.conf
RUN echo "</VirtualHost>"                                           >> /etc/apache2/sites-available/001-qlcd.conf

RUN a2enmod rewrite
RUN a2dissite 000-default
RUN a2ensite 001-qlcd

RUN sed -i -e "s/variables_order = \"GPCS\"/variables_order = \"EGPCS\"/g" /etc/php5/cli/php.ini
RUN sed -i -e "s/variables_order = \"GPCS\"/variables_order = \"EGPCS\"/g" /etc/php5/apache2/php.ini

EXPOSE 80
