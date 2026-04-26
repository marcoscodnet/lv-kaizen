select * from information_schema.tables where table_schema = 'lv_kaizen' and table_name = 'migrations' and table_type = 'BASE TABLE';
select * from information_schema.tables where table_schema = 'lv_kaizen' and table_name = 'migrations' and table_type = 'BASE TABLE';
select `migration` from `migrations` order by `batch` asc, `migration` asc;
select `migration` from `migrations` order by `batch` asc, `migration` asc;
select max(`batch`) as aggregate from `migrations`;
create table `unidad_movimientos` (`id` bigint unsigned not null auto_increment primary key, `unidad_id` bigint unsigned null, `movimiento_id` bigint unsigned null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
alter table `unidad_movimientos` add constraint `unidad_movimientos_unidad_id_foreign` foreign key (`unidad_id`) references `unidads` (`id`);
alter table `unidad_movimientos` add constraint `unidad_movimientos_movimiento_id_foreign` foreign key (`movimiento_id`) references `movimientos` (`id`);
insert into `migrations` (`migration`, `batch`) values ('2025_05_14_181241_create_unidad_movimientos_table', 13);
