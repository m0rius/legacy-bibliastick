
-- create database bibliastick;
SET NAMES UTF8;

-- Users infos
-- Prevoir : anonyme, profile supprimé
create table users(
    id int(10) primary key auto_increment,
    pseudo varchar(255) not null,
    mail varchar(255) not null,
    type int(10) not null default 1, -- 1 user, 2 admin
    pass varchar(255) not null,
    validation int(10) not null default 3, -- 1 validé, 2 refusé, 3 en attente de modération
    id_photo int(10) not null
) DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;


-- Stickers entities
create table stickers(
    id int(10) primary key auto_increment,
    title varchar(255) not null,
    creation datetime not null,
    validation int(10) not null default 3, -- 1 validé, 2 refusé, 3 en attente de modération
    id_author int(10) not null
) DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;


-- Pictures infos
create table pictures(
    id int(10) primary key auto_increment,
    name varchar(255) not null,
    type int(10) not null default 2, -- 1 main, 2 common, 3 printable
    color varchar(255) default null,
    validation int(10) not null default 3, -- 1 validé, 2 refusé, 3 en attente de modération
    creation datetime not null,
    id_author int(10) not null,
    id_sticker int(10) not null
) DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;


-- Stickers texts
create table infos(
    id int(10) primary key auto_increment,
    content text not null,
    type int(10) not null, -- 1 page d'information sur le stickers, 2 description de photos
    creation datetime not null,
    modification datetime not null,
    id_author int(10) not null,
    id_sticker int(10),
    id_picture int(10)
) DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;


-- User contributions (moderated)
create table contributions(
    id int(10) primary key auto_increment,
    content text not null,
    validation int(10) not null default 3, -- 1 validé, 2 refusé, 3 en attente de modération
    creation datetime not null,
    id_author int(10) not null,
    id_info int(10) not null
) DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;


-- Stickers categories
create table available_categories( 
    id int(10) primary key auto_increment,
    name varchar(255) not null,
    parent int(10) default null, 
    creation datetime not null,
    validation int(10) not null default 3, -- 1 validé, 2 refusé, 3 en attente de modération
    id_author int(10) not null
) DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;


create table effective_categories(
    id int(10) primary key auto_increment,
    id_category int(10) not null,
    id_sticker int(10) not null,
    validation int(10) not null default 3, -- 1 validé, 2 refusé, 3 en attente de modération
    creation datetime not null,
    id_author int(10) not null
) DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;


create table liste_stickers(
    id int(10) primary key auto_increment,
    id_user int(10) not null,
    id_sticker int(10) not null
) DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;


create table messages(
    id int(10) primary key auto_increment,
    content text not null,
    id_sender int(10) not null, 
    id_recipient int(10) not null
) DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;


alter table stickers 
      add foreign key (id_author) references users(id) on delete cascade;

alter table pictures
      add foreign key (id_sticker) references stickers(id) on delete cascade,
      add foreign key (id_author) references users(id) on delete cascade;

alter table infos
      add foreign key (id_sticker) references stickers(id) on delete cascade,
      add foreign key (id_author) references users(id) on delete cascade,
      add foreign key (id_picture) references pictures(id) on delete cascade;


alter table contributions
      add foreign key (id_author) references users(id) on delete cascade,
      add foreign key (id_info) references infos(id) on delete cascade;


alter table available_categories
      add foreign key (parent) references available_categories(id) on delete cascade,
      add foreign key (id_author) references users(id) on delete cascade;


alter table effective_categories
      add foreign key (id_category) references available_categories(id) on delete cascade,
      add foreign key (id_sticker) references stickers(id) on delete cascade,
      add foreign key (id_author) references users(id) on delete cascade;

alter table liste_stickers
      add foreign key (id_user) references users(id) on delete cascade,
      add foreign key (id_sticker) references stickers(id) on delete cascade;

alter table messages
      add foreign key (id_sender) references users(id) on delete cascade,
      add foreign key (id_sender) references users(id) on delete cascade;


