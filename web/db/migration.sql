create table city
(
    id   int auto_increment
        primary key,
    name varchar(100) not null
);

create table user
(
    id         int auto_increment
        primary key,
    first_name varchar(100)      null,
    last_name  varchar(100)      null,
    age        tinyint unsigned  null,
    sex        tinyint           null,
    interests  text              null,
    city_id    smallint unsigned null,
    login      varchar(100)      null,
    password   varchar(100)      null
);

create table users_friends
(
    user_one_id int not null,
    user_two_id int not null
);

