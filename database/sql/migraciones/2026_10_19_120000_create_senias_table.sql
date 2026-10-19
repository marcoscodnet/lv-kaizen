-- Señas (equivale a la migración 2026_10_19_120000_create_senias_table).
-- La seña tiene sus propios pagos (pagos.senia_id): efectivo entra a la caja del día;
-- transferencia/tarjeta/etc. van a Auditoría y la seña recién se puede aplicar cuando
-- está autorizada. Al aplicarla se crea un pago "Seña" en la operación (pagos.senia_aplicada_id).
-- Correr UNA sola vez. Al final se registra en `migrations` para que artisan no la vuelva a correr.

create table `senias` (
  `id` bigint unsigned not null auto_increment primary key,
  `cliente_id` bigint unsigned not null,
  `sucursal_id` bigint unsigned not null,
  `fecha` datetime not null,
  `monto` decimal(12, 2) not null,
  `detalle` varchar(255) null,
  `estado` enum('Vigente', 'Aplicada', 'Devuelta', 'Retenida') not null default 'Vigente',
  `venta_id` bigint unsigned null,
  `venta_pieza_id` bigint unsigned null,
  `servicio_id` bigint unsigned null,
  `pago_aplicado_id` bigint unsigned null,
  `fecha_baja` datetime null,
  `devolucion_entidad_id` bigint unsigned null,
  `devolucion_monto` decimal(12, 2) null,
  `observacion` text null,
  `user_id` bigint unsigned null,
  `created_at` timestamp null,
  `updated_at` timestamp null
) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
alter table `senias` add constraint `senias_cliente_id_foreign` foreign key (`cliente_id`) references `clientes` (`id`);
alter table `senias` add constraint `senias_sucursal_id_foreign` foreign key (`sucursal_id`) references `sucursals` (`id`);
alter table `senias` add index `senias_cliente_id_estado_index`(`cliente_id`, `estado`);
alter table `senias` add index `senias_venta_id_index`(`venta_id`);
alter table `senias` add index `senias_venta_pieza_id_index`(`venta_pieza_id`);
alter table `senias` add index `senias_servicio_id_index`(`servicio_id`);

alter table `pagos` add `senia_id` bigint unsigned null, add `senia_aplicada_id` bigint unsigned null;
alter table `pagos` add index `pagos_senia_id_index`(`senia_id`);
alter table `pagos` add index `pagos_senia_aplicada_id_index`(`senia_aplicada_id`);

alter table `movimiento_cajas` add `senia_id` bigint unsigned null;
alter table `movimiento_cajas` add index `movimiento_cajas_senia_id_index`(`senia_id`);

alter table `movimiento_cuentas` add `senia_id` bigint unsigned null;
alter table `movimiento_cuentas` add index `movimiento_cuentas_senia_id_index`(`senia_id`);

-- Conceptos de caja (inactivos: los usa el sistema, no se eligen a mano)
insert into `conceptos` (`nombre`, `tipo`, `activo`, `created_at`, `updated_at`)
select 'Seña', 'Suma', 0, now(), now() from dual
where not exists (select 1 from `conceptos` where `nombre` = 'Seña');
insert into `conceptos` (`nombre`, `tipo`, `activo`, `created_at`, `updated_at`)
select 'Devolución de seña', 'Resta', 0, now(), now() from dual
where not exists (select 1 from `conceptos` where `nombre` = 'Devolución de seña');

-- Forma de pago interna con la que la seña entra a la operación (inactiva)
insert into `entidads` (`nombre`, `forma`, `autorizacion`, `tangible`, `cuenta`, `activa`, `created_at`, `updated_at`)
select 'Seña', 'Contado', 0, 0, 0, 0, now(), now() from dual
where not exists (select 1 from `entidads` where `nombre` = 'Seña');

-- Permisos
insert into `permissions` (`name`, `guard_name`, `created_at`, `updated_at`)
select p.name, 'web', now(), now()
from (select 'senia-listar' as name union all select 'senia-crear' union all select 'senia-baja' union all select 'senia-otras-sucursales') p
where not exists (select 1 from `permissions` x where x.name = p.name and x.guard_name = 'web');

-- Administrador: los cuatro
insert ignore into `role_has_permissions` (`permission_id`, `role_id`)
select p.id, r.id from `permissions` p, `roles` r
where r.name = 'Administrador'
  and p.name in ('senia-listar', 'senia-crear', 'senia-baja', 'senia-otras-sucursales');

-- Quien hoy registra movimientos de caja: listar, crear y dar de baja
insert ignore into `role_has_permissions` (`permission_id`, `role_id`)
select p.id, rhp.role_id
from `permissions` p
join `role_has_permissions` rhp
join `permissions` pc on pc.id = rhp.permission_id and pc.name = 'caja-movimiento-registrar'
where p.name in ('senia-listar', 'senia-crear', 'senia-baja');

insert into `migrations` (`migration`, `batch`)
select '2026_10_19_120000_create_senias_table', ifnull(max(`batch`), 0) + 1 from `migrations`;
