select * from information_schema.tables where table_schema = 'lv_kaizen' and table_name = 'migrations' and table_type = 'BASE TABLE';
select * from information_schema.tables where table_schema = 'lv_kaizen' and table_name = 'migrations' and table_type = 'BASE TABLE';
select `migration` from `migrations` order by `batch` asc, `migration` asc;
select `migration` from `migrations` order by `batch` asc, `migration` asc;
select max(`batch`) as aggregate from `migrations`;
create table `unidads` (`id` bigint unsigned not null auto_increment primary key, `producto_id` bigint unsigned null, `sucursal_id` bigint unsigned null, `motor` varchar(255) null, `cuadro` varchar(255) null, `patente` varchar(255) null, `remito` varchar(255) null, `year` varchar(255) null, `envio` varchar(255) null, `ingreso` datetime null, `observaciones` text null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
alter table `unidads` add constraint `unidads_producto_id_foreign` foreign key (`producto_id`) references `productos` (`id`);
alter table `unidads` add constraint `unidads_sucursal_id_foreign` foreign key (`sucursal_id`) references `sucursals` (`id`);
insert into `migrations` (`migration`, `batch`) values ('2025_05_02_091454_create_unidads_table', 9);
