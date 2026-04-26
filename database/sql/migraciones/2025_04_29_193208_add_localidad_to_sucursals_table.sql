select * from information_schema.tables where table_schema = 'lv_kaizen' and table_name = 'migrations' and table_type = 'BASE TABLE';
select * from information_schema.tables where table_schema = 'lv_kaizen' and table_name = 'migrations' and table_type = 'BASE TABLE';
select `migration` from `migrations` order by `batch` asc, `migration` asc;
select `migration` from `migrations` order by `batch` asc, `migration` asc;
select max(`batch`) as aggregate from `migrations`;
alter table `sucursals` add `localidad_id` bigint unsigned null;
alter table `sucursals` add constraint `sucursals_localidad_id_foreign` foreign key (`localidad_id`) references `localidads` (`id`);
alter table `sucursals` drop `localidad`;
insert into `migrations` (`migration`, `batch`) values ('2025_04_29_193208_add_localidad_to_sucursals_table', 6);
