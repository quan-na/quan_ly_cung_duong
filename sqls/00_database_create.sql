/*
	Database for quan ly cung duong application
 */

create database quan_ly_cd;

use quan_ly_cd;

create table user_account (
    username varchar(50) not null primary key,
    display_name varchar(255) not null default 'User',
    password_sha1 char(40),

    -- privilege for this user
    group_level smallint not null default 999,
    enabled bit not null default true,

    -- access right into this object
    ar_owner varchar(50) not null,
    ar_user smallint not null default 3,
    ar_group smallint not null default 1,
    ar_other smallint not null default 0
);

INSERT INTO `quan_ly_cd`.`user_account`
   (`username`,
    `display_name`,
    `password_sha1`,
    `group_level`,
    `enabled`,
    `ar_owner`,
    `ar_user`,
    `ar_group`,
    `ar_other`)
VALUES
   ('admin',
    'Administrator',
    'c6895fd58197f28396ce4d1f0c47e72bb7bb49e1', -- Namu Amida Buddha
    0,
    1,
    'admin',
    2, -- no, admin, you can not op out yourself
    0,
    0);

create table menu_item (
    
);