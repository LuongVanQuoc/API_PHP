create database knotreaStore;

use knotreaStore;

create table roles(
	id int AUTO_INCREMENT primary key,
    name varchar(150)
);

insert into roles (name)
values ('Khách hàng');
insert into roles (name)
values ('Nhân viên');
insert into roles (name)
values ('Shipper');
insert into roles (name)
values ('Quản trị viên');

create table users(
	username varchar(20) primary key,
    name varchar(150),
    phone varchar(10),
    email varchar(150),
    pass varchar(16),
    image varchar(500),
    role int,
    active bit,
    soft_delete bit default b'0',
    
    FOREIGN KEY (role) REFERENCES roles(id)
);

insert into users (username, name, phone, email, pass, image, role, active)
values ('teonv', 'Nguyễn Văn Tèo', '0344436724', 'teonv@gmail.com', '123456', 'default_image.jpg', 1, 1);
insert into users (username, name, phone, email, pass, image, role, active)
values ('nont', 'Nguyễn Thị Nở', '0898913110', 'nont@gmail.com', '123456', 'default_image.jpg', 1, 1);
insert into users (username, name, phone, email, pass, image, role, active)
values ('staff', 'Nhân viên', '0323573492', 'staff@gmail.com', '181203', 'default_image.jpg', 2, 1);
insert into users (username, name, phone, email, pass, image, role, active)
values ('admin', 'Quản trị viên', '0843745238', 'admin@gmail.com', '111222', 'default_image.jpg', 4, 1);

select * from roles;
select * from users;

drop table users;