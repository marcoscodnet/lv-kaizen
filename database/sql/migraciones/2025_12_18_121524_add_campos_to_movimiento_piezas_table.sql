select * from information_schema.tables where table_schema = 'lv_kaizen' and table_name = 'migrations' and table_type = 'BASE TABLE';
select * from information_schema.tables where table_schema = 'lv_kaizen' and table_name = 'migrations' and table_type = 'BASE TABLE';
select `migration` from `migrations` order by `batch` asc, `migration` asc;
select `migration` from `migrations` order by `batch` asc, `migration` asc;
select max(`batch`) as aggregate from `migrations`;
alter table `venta_piezas` add `cliente_id` bigint unsigned null;
alter table `venta_piezas` add constraint `venta_piezas_cliente_id_foreign` foreign key (`cliente_id`) references `clientes` (`id`);
alter table `movimiento_piezas` add `estado` varchar(255) not null default 'Pendiente', add `aceptado` timestamp null, add `user_acepta_id` bigint unsigned null;
alter table `movimiento_piezas` add constraint `movimiento_piezas_user_acepta_id_foreign` foreign key (`user_acepta_id`) references `users` (`id`);
