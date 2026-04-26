select * from information_schema.tables where table_schema = 'lv_kaizen' and table_name = 'migrations' and table_type = 'BASE TABLE';
select * from information_schema.tables where table_schema = 'lv_kaizen' and table_name = 'migrations' and table_type = 'BASE TABLE';
select `migration` from `migrations` order by `batch` asc, `migration` asc;
select `migration` from `migrations` order by `batch` asc, `migration` asc;
select max(`batch`) as aggregate from `migrations`;
create table `stock_piezas` (`id` bigint unsigned not null auto_increment primary key, `pieza_id` bigint unsigned null, `sucursal_id` bigint unsigned null, `remito` varchar(255) null, `cantidad` int null, `costo` decimal(10, 2) null, `precio_minimo` decimal(10, 2) null, `proveedor` enum('Honda') null, `ingreso` datetime null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
alter table `stock_piezas` add constraint `stock_piezas_pieza_id_foreign` foreign key (`pieza_id`) references `piezas` (`id`);
alter table `stock_piezas` add constraint `stock_piezas_sucursal_id_foreign` foreign key (`sucursal_id`) references `sucursals` (`id`);
insert into `migrations` (`migration`, `batch`) values ('2025_05_14_105736_create_stock_piezas_table', 11);
