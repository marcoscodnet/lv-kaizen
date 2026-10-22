-- Permiso operacion-ver-todas (equivalente a la migración 2026_10_22_190000).
-- Para correr en producción desde phpMyAdmin.
INSERT INTO `permissions` (`name`, `guard_name`, `created_at`, `updated_at`)
SELECT 'operacion-ver-todas', 'web', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE `name` = 'operacion-ver-todas' AND `guard_name` = 'web');

INSERT INTO `role_has_permissions` (`permission_id`, `role_id`)
SELECT p.id, r.id FROM `permissions` p, `roles` r
WHERE p.name = 'operacion-ver-todas' AND p.guard_name = 'web' AND r.name = 'Administrador'
  AND NOT EXISTS (SELECT 1 FROM `role_has_permissions` x WHERE x.permission_id = p.id AND x.role_id = r.id);

INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_10_22_190000_add_operacion_ver_todas_permission', (SELECT IFNULL(MAX(batch), 0) + 1 FROM (SELECT batch FROM `migrations`) m)
WHERE NOT EXISTS (SELECT 1 FROM `migrations` WHERE `migration` = '2026_10_22_190000_add_operacion_ver_todas_permission');
