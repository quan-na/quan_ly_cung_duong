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
    ar_group_level smallint not null default 999,
    ar_user smallint not null default 3,
    ar_group smallint not null default 1,
    ar_other smallint not null default 0
);

INSERT INTO `user_account`
   (`username`,
    `display_name`,
    `password_sha1`,
    `group_level`,
    `enabled`,
    `ar_owner`,
    `ar_group_level`,
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
    0,
    2, -- no, admin, you can not op out yourself
    0,
    0),
   ('user1',
    'User One',
    'c6895fd58197f28396ce4d1f0c47e72bb7bb49e1', -- Namu Amida Buddha
    10,
    1,
    'admin',
    0,
    2, -- no, admin, you can not op out yourself
    3,
    0);

create table menu_item (
    menu_id varchar(25) not null primary key,
    priority smallint not null default 999,
    menu_text varchar(255),
    parent_menu varchar(25),

    -- access right to this menu item
    ar_owner varchar(50) not null,
    ar_group_level smallint not null default 10,
    ar_user smallint not null default 3,
    ar_group smallint not null default 1,
    ar_other smallint not null default 0
);

INSERT INTO `menu_item` (`menu_id`, `priority`, `menu_text`, `ar_owner`, `ar_group_level`, `ar_user`, `ar_group`, `ar_other`) VALUES ('cung_duong', '1', 'Cung duong', 'admin', '10', '3', '1', '0');
INSERT INTO `menu_item` (`menu_id`, `priority`, `menu_text`, `parent_menu`, `ar_owner`, `ar_group_level`, `ar_user`, `ar_group`, `ar_other`) VALUES ('create_cung_duong', '11', 'Create cung duong', 'cung_duong', 'admin', '10', '3', '1', '0');
INSERT INTO `menu_item` (`menu_id`, `priority`, `menu_text`, `parent_menu`, `ar_owner`, `ar_group_level`, `ar_user`, `ar_group`, `ar_other`) VALUES ('list_cung_duong', '12', 'List cung duong', 'cung_duong', 'admin', '10', '3', '1', '0');
INSERT INTO `menu_item` (`menu_id`, `priority`, `menu_text`, `parent_menu`, `ar_owner`, `ar_group_level`, `ar_user`, `ar_group`, `ar_other`) VALUES ('report_cung_duong', '13', 'Report cung duong', 'cung_duong', 'admin', '10', '3', '1', '0');
INSERT INTO `menu_item` (`menu_id`, `priority`, `menu_text`, `ar_owner`, `ar_group_level`, `ar_user`, `ar_group`, `ar_other`) VALUES ('user', '2', 'User', 'admin', '100', '3', '1', '0');
INSERT INTO `menu_item` (`menu_id`, `priority`, `menu_text`, `parent_menu`, `ar_owner`, `ar_group_level`, `ar_user`, `ar_group`, `ar_other`) VALUES ('user_info', '21', 'User info', 'user', 'admin', '100', '3', '1', '0');
INSERT INTO `menu_item` (`menu_id`, `priority`, `menu_text`, `parent_menu`, `ar_owner`, `ar_group_level`, `ar_user`, `ar_group`, `ar_other`) VALUES ('list_user', '22', 'List user', 'user', 'admin', '0', '3', '1', '0');
INSERT INTO `menu_item` (`menu_id`, `priority`, `menu_text`, `parent_menu`, `ar_owner`, `ar_group_level`, `ar_user`, `ar_group`, `ar_other`) VALUES ('logout', '23', 'Logout', 'user', 'admin', '100', '3', '1', '0');
