/**
 * Author:  alexander
 * Created: 13.12.2017
 */

create table if not exists `links` (
    `id` int not null PRIMARY KEY,
    `path` varchar(255) not null unique
);

create table if not exists `pictures` (
    `id` int not null PRIMARY KEY,
    `path` varchar(255) not null unique,
    `alt` varchar(255),
    `result` varchar(255)
);

create table if not exists `user` (
    `name` varchar(255) not null primary key,
    `pwd` varchar(255),
    `mail` varchar(255) not null unique
);

insert into `user` (`mail`, `name`, `pwd`) 
values ('alexander.muemken@gmx.de', 'alex', '$2y$10$NABjKjxCJmcDljN7GBAwb.ijFdkAkE2eeC8DUS5WupuM2kJjzMAKq');