select * from information_schema.tables where table_schema = 'lv_kaizen' and table_name = 'migrations' and table_type = 'BASE TABLE';
select * from information_schema.tables where table_schema = 'lv_kaizen' and table_name = 'migrations' and table_type = 'BASE TABLE';
select `migration` from `migrations` order by `batch` asc, `migration` asc;
select `migration` from `migrations` order by `batch` asc, `migration` asc;
select max(`batch`) as aggregate from `migrations`;
create table `piezas` (`id` bigint unsigned not null auto_increment primary key, `codigo` varchar(45) null, `descripcion` varchar(50) null, `stock_minimo` int null, `costo` decimal(10, 2) null, `precio_minimo` decimal(10, 2) null, `stock_actual` int null, `observaciones` text null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
insert into `migrations` (`migration`, `batch`) values ('2025_05_14_094641_create_piezas_table', 10);
