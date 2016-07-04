#!/bin/sh

docker run -v /Volumes/DATA/quan_ly_cung_duong/sqls:/docker-entrypoint-initdb.d -v /Volumes/DATA/quan_ly_cung_duong/sqls:/etc/mysql/conf.d --name qlcd-mysql -e MYSQL_ROOT_PASSWORD=root -d mysql:latest
docker run -v /Volumes/DATA/quan_ly_cung_duong:/src/quan_ly_cung_duong --link qlcd-mysql:mysql -p 7070:80 --name qlcd-apache -d quan-na/qlcd apachectl -e info -DFOREGROUND
