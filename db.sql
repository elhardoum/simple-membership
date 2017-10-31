create database simple_membership;

create table if not exists simple_membership.users (
    `id` bigint(20) not null auto_increment,
    `email` varchar(100) not null,
    `name` varchar(250),
    `password` varchar(250) not null,
    `registered` bigint(20),
    primary key(id)
);

create table if not exists simple_membership.meta (
    `object_id` bigint(20) default 0,
    `meta_key` varchar(255) not null,
    `meta_value` LONGTEXT not null,
    primary key(meta_key, object_id)
);
