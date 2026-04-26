select * from information_schema.tables where table_schema = 'lv_kaizen' and table_name = 'migrations' and table_type = 'BASE TABLE';
select * from information_schema.tables where table_schema = 'lv_kaizen' and table_name = 'migrations' and table_type = 'BASE TABLE';
select `migration` from `migrations` order by `batch` asc, `migration` asc;
select `migration` from `migrations` order by `batch` asc, `migration` asc;
select max(`batch`) as aggregate from `migrations`;
create table `provincias` (`id` bigint unsigned not null auto_increment primary key, `nombre` varchar(255) null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
insert into `migrations` (`migration`, `batch`) values ('2025_04_29_190944_create_provincias_table', 3);
