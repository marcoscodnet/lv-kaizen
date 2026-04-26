select * from information_schema.tables where table_schema = 'lv_kaizen' and table_name = 'migrations' and table_type = 'BASE TABLE';
select * from information_schema.tables where table_schema = 'lv_kaizen' and table_name = 'migrations' and table_type = 'BASE TABLE';
select `migration` from `migrations` order by `batch` asc, `migration` asc;
select `migration` from `migrations` order by `batch` asc, `migration` asc;
select max(`batch`) as aggregate from `migrations`;
create table `ubicacions` (`id` bigint unsigned not null auto_increment primary key, `sucursal_id` bigint unsigned not null, `nombre` varchar(255) not null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
alter table `ubicacions` add constraint `ubicacions_sucursal_id_foreign` foreign key (`sucursal_id`) references `sucursals` (`id`);
