select * from information_schema.tables where table_schema = 'lv_kaizen' and table_name = 'migrations' and table_type = 'BASE TABLE';
select * from information_schema.tables where table_schema = 'lv_kaizen' and table_name = 'migrations' and table_type = 'BASE TABLE';
select `migration` from `migrations` order by `batch` asc, `migration` asc;
select `migration` from `migrations` order by `batch` asc, `migration` asc;
select max(`batch`) as aggregate from `migrations`;
create table `conceptos` (`id` bigint unsigned not null auto_increment primary key, `nombre` varchar(255) not null, `tipo` enum('suma', 'resta') not null, `ticket` tinyint(1) not null default '0', `referencia` tinyint(1) not null default '0', `credito` tinyint(1) not null default '0', `activo` tinyint(1) not null default '1', `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
insert into `migrations` (`migration`, `batch`) values ('2025_09_12_102434_create_conceptos_table', 21);
