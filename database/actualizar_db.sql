-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Versión del servidor:         10.3.16-MariaDB - mariadb.org binary distribution
-- SO del servidor:              Win64
-- HeidiSQL Versión:             12.10.0.7000
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- Volcando datos para la tabla kaizen.color: ~40 rows (aproximadamente)
INSERT INTO `colors` (`id`, `nombre`) VALUES
                                                 (4, 'ROJO'),
                                                 (5, 'BLANCO'),
                                                 (6, 'NEGRO'),
                                                 (7, 'AZUL'),
                                                 (8, 'NARANJA'),
                                                 (9, 'GRIS '),
                                                 (10, 'VERDE'),
                                                 (11, 'NEGRO MATE'),
                                                 (12, 'AMARILLO'),
                                                 (13, 'SIN ESPECIFICAR'),
                                                 (14, 'NEGRO/ROJO'),
                                                 (15, 'NEGRO/GRIS'),
                                                 (16, 'NEGRO/AZUL'),
                                                 (17, 'VIOLETA'),
                                                 (18, 'BLANCO/NEGRO'),
                                                 (19, 'GRIS/BLANCO'),
                                                 (20, 'GRIS/NEGRO'),
                                                 (21, 'PLATA'),
                                                 (22, 'ROJO/BLANCO'),
                                                 (23, 'BLANCO/ROJO'),
                                                 (24, 'AZUL/BLANCO'),
                                                 (25, 'AZUL/DORADO'),
                                                 (26, 'TRICOLOR'),
                                                 (27, 'GRIS OSCURO'),
                                                 (28, 'BORDO / GRIS'),
                                                 (29, 'AZUL/GRIS'),
                                                 (30, 'AZUL/NEGRO'),
                                                 (31, 'TRICOLOR'),
                                                 (32, 'MARRON'),
                                                 (33, 'CELESTE/BLANCO'),
                                                 (34, 'BORDO'),
                                                 (35, 'ROJO/NEGRO'),
                                                 (36, 'ROSA'),
                                                 (37, 'AZUL COBALTO '),
                                                 (38, 'ROJO CORAL'),
                                                 (39, 'GRIS/ROJO'),
                                                 (40, 'GRIS MATE '),
                                                 (41, 'GRIS NARDO'),
                                                 (42, 'NEGRO MATE '),
                                                 (43, 'BEIGE');

-- Volcando datos para la tabla kaizen.entidad: ~44 rows (aproximadamente)
INSERT INTO `entidads` (`id`, `nombre`, `activa`) VALUES
                                                                    (3, 'FINANCIA KAIZEN', 1),
                                                                    (5, 'DYA', 0),
                                                                    (6, 'VISA - MASTER- TARJETA CREDITO', 0),
                                                                    (7, 'CRED NOW', 0),
                                                                    (9, 'SEÑA', 1),
                                                                    (11, 'CASCO', 0),
                                                                    (15, 'PATENTAMIENTO', 1),
                                                                    (16, 'ALTA S', 0),
                                                                    (17, 'DESCUENTO', 1),
                                                                    (19, 'DEPOSITO BNA', 0),
                                                                    (20, 'DEPOSITO BAPRO', 1),
                                                                    (21, 'CREDILOGROS', 1),
                                                                    (22, 'CHEQUE', 1),
                                                                    (23, 'MOTO USADA', 1),
                                                                    (24, 'ADJUDICACION BAPRO', 1),
                                                                    (26, 'CETELEM', 0),
                                                                    (27, 'ALARMA', 0),
                                                                    (29, 'CUOTAS', 0),
                                                                    (30, 'SEGURO ATM', 0),
                                                                    (31, 'LINGA', 0),
                                                                    (34, 'FORMULARIOS', 1),
                                                                    (35, 'AHORA 12', 1),
                                                                    (36, 'DEPOSITO HAR GALICIA', 0),
                                                                    (37, 'CRISTALCASH', 0),
                                                                    (38, 'MERCADO PAGO', 1),
                                                                    (39, 'AHORA 18', 1),
                                                                    (43, 'BAPRO TU MOTO', 0),
                                                                    (44, 'CREDITO ARGENTINO', 0),
                                                                    (45, 'DEBITO', 1),
                                                                    (46, 'OTOM', 0),
                                                                    (47, 'CREDITO DIRECTO', 0),
                                                                    (48, 'DINERO EN EFECTIVO', 1),
                                                                    (49, 'CREDICUOTAS', 1),
                                                                    (50, 'AHORA 12/18 YAMAHA', 1),
                                                                    (51, 'CONTADO ANR 10 %', 0),
                                                                    (52, 'CEIBO CREDITOS', 0),
                                                                    (53, 'REPOSICION SEGURO', 0),
                                                                    (54, 'BNA mi moto ', 1),
                                                                    (55, 'DEPOSITO EN YAMAHA', 1),
                                                                    (56, 'WENANCE SA', 1),
                                                                    (57, 'DEPO GALICIA', 1),
                                                                    (58, 'AHORA 3', 1),
                                                                    (59, 'AHORA 6', 1),
                                                                    (60, 'SANTANDER CONSUMER ', 1);

-- Volcando datos para la tabla kaizen.marca: ~12 rows (aproximadamente)
INSERT INTO `marcas` (`id`, `nombre`) VALUES
                                                 (13, 'HONDA'),
                                                 (14, 'MONDIAL'),
                                                 (15, 'GUERRERO'),
                                                 (16, 'BAJAJ'),
                                                 (17, 'ZANELLA'),
                                                 (18, 'YAMAHA'),
                                                 (19, 'BETA'),
                                                 (20, 'PANTHER'),
                                                 (21, 'GILERA'),
                                                 (23, 'SUZUKI'),
                                                 (24, 'STIHL'),
                                                 (25, 'ELPRA');

-- Volcando datos para la tabla kaizen.modelo: ~0 rows (aproximadamente)
INSERT INTO `modelos` (`id`, `nombre`, `marca_id`) VALUES
                                                                (4, 'NF 100 WAVE ', 13),
                                                                (5, 'NF 100 WAVE C/DISCO', 13),
                                                                (6, 'NF 100 WAVE SERIE II', 13),
                                                                (7, 'C 105 BIZ', 13),
                                                                (8, 'CG 150  TITAN (DESCONTINUADO)', 13),
                                                                (9, 'CG 125 FAN', 13),
                                                                (10, 'STORM C/D', 13),
                                                                (11, ' SDH 125 46 STORM', 13),
                                                                (12, 'DESCONTINUADO XR 125L', 13),
                                                                (13, 'XR 250 ', 13),
                                                                (14, 'FALCON 400', 13),
                                                                (15, '(DESCONTINUADO) ELITE SDH 125 T22', 13),
                                                                (16, 'CBX 250 ', 13),
                                                                (17, 'CBR 600 RR', 13),
                                                                (18, 'CBR 1000 RR', 13),
                                                                (19, 'TRX 250 TM', 13),
                                                                (20, 'TRX 420 TM 4X2', 13),
                                                                (22, 'TRX 420 FM 4X4', 13),
                                                                (23, 'TRX 500 FM 4X4 ', 13),
                                                                (24, 'ZB 110 S/A', 17),
                                                                (25, 'TRIP S/A', 15),
                                                                (26, 'G 90 ', 15),
                                                                (27, 'DAY 70', 15),
                                                                (28, 'DAX 70', 14),
                                                                (29, 'ROUSER 220', 16),
                                                                (30, 'HD 254 ', 14),
                                                                (31, 'DESCONTINUADO V-MEN SDH 125 42', 13),
                                                                (32, 'WB 20XT', 13),
                                                                (33, 'WB 30XT', 13),
                                                                (34, 'WH 20X', 13),
                                                                (35, 'WT 30X', 13),
                                                                (36, 'EP 2500 CX ', 13),
                                                                (37, 'EP 6500', 13),
                                                                (38, 'ET 12000', 13),
                                                                (39, 'EU 10', 13),
                                                                (40, 'EU 20', 13),
                                                                (41, 'EU 30', 13),
                                                                (42, 'UMK 435', 13),
                                                                (43, 'BF 2.3 EJE CORTO', 13),
                                                                (44, 'ZB 110 A/E', 17),
                                                                (45, 'TRIP A/E', 15),
                                                                (46, 'GMX 150', 15),
                                                                (47, 'QUEEN 125 GC', 15),
                                                                (48, 'TRX 250 SPORTRAX', 13),
                                                                (50, 'EZ 4500', 13),
                                                                (51, 'EG 6500', 13),
                                                                (52, 'RD 200K', 14),
                                                                (53, 'EB 1000', 13),
                                                                (54, 'SAPUCAI 125', 17),
                                                                (55, 'GX 160 EJE RECTO', 15),
                                                                (57, 'GX 160 EJE RECTO', 13),
                                                                (58, 'NF 100 WAVE C/LLANTAS ', 13),
                                                                (59, 'BIZ 105 NEGRA ', 13),
                                                                (60, 'FZ 16 150 CC', 18),
                                                                (61, 'GX 100 EJE CONICO', 13),
                                                                (62, 'HD 250 USADA ', 14),
                                                                (63, 'ROUSER 135', 16),
                                                                (64, 'YBR 125', 18),
                                                                (65, 'POP 100', 13),
                                                                (66, 'PANDA 50 ', 19),
                                                                (67, 'EG 5000 CX', 13),
                                                                (68, 'TRX 680', 13),
                                                                (69, 'BIZ 125 (DESCONTINUADO)', 13),
                                                                (70, 'WR 250', 20),
                                                                (71, 'GX 690R EJE RECTO A/Elec', 13),
                                                                (72, 'GX 390 MANUAL ', 13),
                                                                (73, 'HRR2168 SMART DRIVE', 13),
                                                                (74, 'BF20DK2 EJE CORTO', 13),
                                                                (75, 'ROUSER 180', 16),
                                                                (76, 'G110 DL', 15),
                                                                (77, 'NXR 125 BROS', 13),
                                                                (78, 'EU 65 IS1', 13),
                                                                (79, 'EP 2500C  ESTANDAR', 13),
                                                                (81, 'GFT250 MAPUCHE ', 15),
                                                                (82, 'HOT 90', 17),
                                                                (83, 'DESCONTINUADO XL 700V  3', 13),
                                                                (84, 'CB1 CGX 125 ', 13),
                                                                (85, 'WT40X', 13),
                                                                (86, 'WT40X', 13),
                                                                (87, 'GC 150 Q', 15),
                                                                (88, 'BF20DK2 EJE LARGO ARR-ELECTRICO', 13),
                                                                (89, ' XRE 300 ', 13),
                                                                (90, 'BF 10 EJE CORTO', 13),
                                                                (91, 'DESCONTINUADO VC 150', 21),
                                                                (92, 'SMASH', 21),
                                                                (93, 'GG2,5 KVA', 15),
                                                                (94, 'GG6KVA', 15),
                                                                (95, 'GG85KVA', 15),
                                                                (96, 'GLMS46', 15),
                                                                (97, 'DESCONTINUADO VFR800X CROSSRUNNER', 13),
                                                                (98, 'CRF450RD', 13),
                                                                (99, 'BF 50 EJE LARGO', 13),
                                                                (100, ' XRE 300 RALLY', 13),
                                                                (101, 'TRX 500 FE 4X4 ELECTRICO', 13),
                                                                (102, 'DESCONTINUADO WAVE 110', 13),
                                                                (103, 'CB1 CGX 125 T (TUF)', 13),
                                                                (104, 'SMASH C/D', 21),
                                                                (105, 'HRR2169 SMART DRIVE AUTO. PRO.', 13),
                                                                (106, 'DESCONTINUADO VC 200 R', 21),
                                                                (107, 'CRF 250 L', 13),
                                                                (108, 'ELITE 125', 13),
                                                                (109, 'EP 5000', 13),
                                                                (110, 'NC 750 X', 13),
                                                                (111, 'BF 5 EJE CORTO', 13),
                                                                (112, 'FR 250 HOT BEAR', 21),
                                                                (113, 'INVICTA', 13),
                                                                (114, 'FZ 16 AR', 18),
                                                                (115, 'XTZ 125 AR', 18),
                                                                (116, 'YBR 125 ED', 18),
                                                                (117, ' DESCONTINUADO YBR 125 E ', 18),
                                                                (118, 'DESCONTINUADO XTZ 250 ', 18),
                                                                (119, 'T110 DISK', 18),
                                                                (120, 'T110 DRUM', 18),
                                                                (121, 'DESCONTINUADO YBR 250 ', 18),
                                                                (122, 'YFM 250 R', 18),
                                                                (123, 'YFM 350 R RAPTOR', 18),
                                                                (124, 'DESCONTINUADO YFM 300 R ', 18),
                                                                (125, 'YFM 125A', 18),
                                                                (126, 'YFZ 450R SE', 18),
                                                                (127, 'YS 250', 18),
                                                                (128, 'EG 1000', 13),
                                                                (129, 'WL 20X', 13),
                                                                (130, 'CRF70 F', 13),
                                                                (131, 'YFM 700 R', 18),
                                                                (132, 'DESCONTINUADO ', 18),
                                                                (133, 'YFM 550 FWAD', 18),
                                                                (134, 'CBX 250 ES', 13),
                                                                (135, 'GN 125', 23),
                                                                (136, 'SMASH AUTOMATICA', 21),
                                                                (137, 'DESCONTINUADO YBR 125 R ', 18),
                                                                (138, 'GC150 TITAN  QUEEN', 15),
                                                                (139, 'SMASH FULL', 21),
                                                                (140, 'SMASH TUNING ', 21),
                                                                (141, 'XR 150 L', 13),
                                                                (142, 'DESCONTINUADO YBR 250 AR ', 18),
                                                                (143, 'XTZ 250 AR', 18),
                                                                (144, 'FZ FI', 18),
                                                                (145, 'FZ - S - FI', 18),
                                                                (146, 'EU 70 INYECCION', 13),
                                                                (147, 'CG 150 TITAN NEW', 13),
                                                                (148, 'FZ N1', 18),
                                                                (149, 'VC 70', 21),
                                                                (150, 'BIZ 125 NEW ', 13),
                                                                (151, 'XTZ 250 ABS ', 18),
                                                                (152, 'DESCONTINUADO  XTZ 125', 18),
                                                                (153, 'EF 3000iSE', 18),
                                                                (154, 'EF 6300iSDE', 18),
                                                                (155, 'EF 13000TE', 18),
                                                                (156, 'MZ 125A2B', 18),
                                                                (157, 'EF 2000iS', 18),
                                                                (158, 'MZ 360A2B', 18),
                                                                (159, 'EF 1000iS', 18),
                                                                (160, 'MZ 175A1', 18),
                                                                (161, 'MZ 250AAGA0', 18),
                                                                (162, 'YP 20C', 18),
                                                                (163, 'YP 30C', 18),
                                                                (164, 'MZ 300A2B', 18),
                                                                (165, 'VZ 20 D', 18),
                                                                (166, 'FAZER FI', 18),
                                                                (167, 'BIZ 125 GP ', 13),
                                                                (168, 'CB 190', 13),
                                                                (169, 'SZ-RR', 18),
                                                                (170, 'CB 190 REPSOL ', 13),
                                                                (171, 'YZF R6', 18),
                                                                (172, 'PCX150', 13),
                                                                (173, 'MT 03 (descontinuado)', 18),
                                                                (174, 'DESCONTINUADO YFM 350 FWA 4x4 ', 18),
                                                                (175, 'TRX 420 FE', 13),
                                                                (176, 'XT 1200 SUPERTENERE ', 18),
                                                                (178, 'YZF R3', 18),
                                                                (179, 'XR 150 L RALLY', 13),
                                                                (180, 'YFM 350 FWA 4X4', 18),
                                                                (181, 'SMASH R TUNNING ', 21),
                                                                (182, 'SMASH R 110 FULL', 21),
                                                                (184, 'YZF-R1', 18),
                                                                (185, 'F 2.5 BMHS', 18),
                                                                (186, 'F 2.5 BMHL', 18),
                                                                (187, 'F 4 BMHS', 18),
                                                                (188, 'F 4 BMHL', 18),
                                                                (189, 'F 6 CMHS', 18),
                                                                (190, 'F 9.9 JMHL', 18),
                                                                (191, '40 XWL', 18),
                                                                (192, 'E25 BMHS', 18),
                                                                (193, '2 CMHS', 18),
                                                                (194, '3 AMHS', 18),
                                                                (195, 'E 8 DMHS', 18),
                                                                (196, '15 FMHS', 18),
                                                                (197, 'FGG6KVAT', 15),
                                                                (198, 'FGG8000T', 15),
                                                                (199, 'YFZ 50', 18),
                                                                (200, 'AX 100', 23),
                                                                (202, 'MT-07 ABS', 18),
                                                                (203, 'TTR230', 18),
                                                                (204, 'YZ250 FX', 18),
                                                                (205, 'YZ450 FX', 18),
                                                                (206, 'CRF150 F', 13),
                                                                (207, 'CRF230 F', 13),
                                                                (208, 'MT09 TRA', 18),
                                                                (209, 'WAVE 110 S', 13),
                                                                (210, 'CRF 1000 L', 13),
                                                                (211, 'MT 03 ABS', 18),
                                                                (212, 'MT 09 ABS', 18),
                                                                (213, 'YBR 125 Z ', 18),
                                                                (214, 'TMAX DX', 18),
                                                                (215, 'T MAX DX', 18),
                                                                (216, 'BF 10 EJE LARGO', 13),
                                                                (217, 'GX 390 A/E ', 13),
                                                                (218, 'CB TWISTER 250', 13),
                                                                (220, 'CB 500 F', 13),
                                                                (221, 'XTZ 250 Z TENERE ', 18),
                                                                (222, 'DESCONTINUADO XTZ 250 Z TENERE  ', 18),
                                                                (223, 'VIKING EPS', 18),
                                                                (225, 'NM-X', 18),
                                                                (226, '40 XMHS', 18),
                                                                (227, 'F 20 SMHA / BMHS', 18),
                                                                (228, 'F 20 BMHL', 18),
                                                                (229, 'F 9.9 JMHS', 18),
                                                                (231, 'YZ450 F', 18),
                                                                (232, 'EX 1050 CT EX ', 18),
                                                                (233, 'SJ 700 BT SUPER JET ', 18),
                                                                (234, 'EX 1050 BT EX SPORT', 18),
                                                                (235, 'XC115B', 18),
                                                                (236, 'YFM 90R', 18),
                                                                (237, 'VC 1800 T VX CRUISER HO', 18),
                                                                (238, 'NC750X', 13),
                                                                (239, 'MT 07 TRA ', 18),
                                                                (240, 'XSR 700', 18),
                                                                (241, 'TRX 420 FA 4X4', 13),
                                                                (242, 'FZ25 ', 18),
                                                                (243, 'CBR300R', 13),
                                                                (244, 'PW 3028 (HIDROLAVADORA)', 18),
                                                                (245, 'YZ 250 F', 18),
                                                                (246, 'CB 125F TWISTER ', 13),
                                                                (247, 'EF 5500 FW', 18),
                                                                (248, 'EF 2600 FW', 18),
                                                                (249, 'EF 7200 E', 18),
                                                                (250, 'XR 190 L NEW', 13),
                                                                (252, 'WAVE 110 S CAST DISK ', 13),
                                                                (253, 'CB 300 F', 13),
                                                                (254, 'BF 5 EJE LARGO ', 13),
                                                                (255, 'BIZ 125 FI', 13),
                                                                (256, 'FGG2KVA', 15),
                                                                (257, 'MSE 141 MOTOSIERRA ELECTRICA ', 24),
                                                                (258, 'MSE 170 MOTOSIERRA ELECTRICA ', 24),
                                                                (259, 'MS  170  MOTOSIERRA NAFTA  ', 24),
                                                                (260, 'MS  180  MOTOSIERRA NAFTA  ', 24),
                                                                (261, 'MS  193 T   MOTOSIERRA NAFTA  ', 24),
                                                                (262, 'MS  210  MOTOSIERRA NAFTA  ', 24),
                                                                (263, 'MS  250  MOTOSIERRA NAFTA  ', 24),
                                                                (264, 'MS 361 MOTOSIERRA NAFTA  ', 24),
                                                                (268, 'FS 38 MOTOGUADANA NAFTA', 24),
                                                                (269, 'FS 160 MOTOGUADANA NAFTA', 24),
                                                                (270, 'FS 55  MOTOGUADANA NAFTA', 24),
                                                                (271, 'FS 120 MOTOGUADANA NAFTA', 24),
                                                                (272, 'FS 450 MOTOGUADANA NAFTA', 24),
                                                                (273, 'FS 280 MOTOGUADANA NAFTA', 24),
                                                                (274, 'FS 220 MOTOGUADANA  NAFTA', 24),
                                                                (275, 'FSE 52 BORDEADORA ELEC', 24),
                                                                (277, 'FSE 60 BORDEADORA ELEC', 24),
                                                                (278, 'HSE 61 CORTACERCO ELECT', 24),
                                                                (279, 'HSE 42 CORTACERCO ELECT', 24),
                                                                (280, 'SH 86 ASPIRADORA/TRITURADORA', 24),
                                                                (281, 'BG 50 SOPLADOR ', 24),
                                                                (282, 'BR 420 SOPLADOR ', 24),
                                                                (283, 'SR 450 ATOMIZADOR ', 24),
                                                                (284, 'HT 56 MOTOSIERRA ALTURA ', 24),
                                                                (285, 'HT 103 MOTOSIERRA ALTURA ', 24),
                                                                (287, 'BT 45 TALADRO NAFTA ', 24),
                                                                (288, 'FSA 45 BORDEADORA BATERIA', 24),
                                                                (289, 'HSA 45 CORTACERCO BATERIA', 24),
                                                                (290, 'BGA 45 SOPLADOR BATERIA', 24),
                                                                (291, 'MSA 120 MOTOSIERRA BATERIA ', 24),
                                                                (292, 'HSA 56 CORTACERCO BATERIA', 24),
                                                                (293, 'BGA 56  SOPLADOR BATERIA', 24),
                                                                (294, 'FSA  56 BORDEADORA BATERIA', 24),
                                                                (295, 'RMA 460  COTACESPED CARRO BATERIA ', 24),
                                                                (296, 'HS 45 CORTACERCO NAFTA ', 24),
                                                                (297, 'RE 109/110 HIDROLAVADORA ELECTRICA', 24),
                                                                (298, 'RE 119 HIDROLAVADORA ELECTRICA', 24),
                                                                (299, 'RE 143 HIDROLAVADORA ELECTRICA', 24),
                                                                (300, 'RB 200  HIDROLAVADORA NAFTA ', 24),
                                                                (301, 'RE 272 PLUS  HIDROLAVADORA ELECTRICA', 24),
                                                                (302, 'BGE 71 SOPLADOR ELECTRICO', 24),
                                                                (303, 'SE 62 ASPIRADORA ELECTRICA', 24),
                                                                (304, 'FS 94 MOTOGUADANA  NAFTA ', 24),
                                                                (305, 'MS 382  MOTOSIERRA NAFTA', 24),
                                                                (306, 'FZ - S - FI D', 18),
                                                                (307, 'FS 235 MOTOGUADANA NAFTA', 24),
                                                                (308, 'FASCINO 125 FI ', 18),
                                                                (309, 'GLH 150 ', 13),
                                                                (310, 'UMK 425', 13),
                                                                (311, 'GX1', 21),
                                                                (312, 'GX1 125 SPORT', 21),
                                                                (313, 'SG 150 PICCOLA', 21),
                                                                (314, 'FUMIGADOR HONDA WJR 4025', 13),
                                                                (315, 'UMK 450 T', 13),
                                                                (316, 'EZ 3000', 13),
                                                                (317, 'EZ 6500', 13),
                                                                (318, 'FOLK ELECTRIC ', 25),
                                                                (319, 'POOGY ', 25),
                                                                (320, 'URBAN II', 25),
                                                                (321, 'E-CADDIE ', 25),
                                                                (322, 'SMASH X 125', 21),
                                                                (323, 'SMX 200', 21),
                                                                (324, 'CITY 20 ', 25),
                                                                (325, 'SAHEL 150 ', 21),
                                                                (326, 'SMX 200 ADVENTURE ', 21),
                                                                (327, 'SMASH NEW', 21),
                                                                (328, 'BIZ C105 ES', 13),
                                                                (329, 'VC 150', 21),
                                                                (330, 'AC4 250 ', 21),
                                                                (331, 'BR 600 SOPLADOR MOCHILA ', 24),
                                                                (332, 'CB 125 TWISTER DLX', 13),
                                                                (333, 'RAY ZR 125 FI', 18),
                                                                (334, 'HT 105 NEW MOTOSIERRA ALTURA', 24),
                                                                (335, 'NM-X connected', 18),
                                                                (336, 'RAY ZR 125 FI ', 18),
                                                                (337, 'GTA 26', 24),
                                                                (338, 'CB 750', 13),
                                                                (339, 'PCX 160', 13),
                                                                (340, 'NAVI ', 13),
                                                                (341, 'VC 150 FULL CRM', 21),
                                                                (342, 'XR 300 L', 13),
                                                                (343, 'FZ-S FI V3.0', 18),
                                                                (344, 'FZ-X ', 18),
                                                                (345, 'CB 125F TWISTER DLX ', 13),
                                                                (346, 'SCV 125 S', 13),
                                                                (347, 'SMASH VS CBS ', 21),
                                                                (348, 'GR 60 ', 24),
                                                                (349, 'SMASH R TUNNING CBS ', 21);

-- Volcando datos para la tabla kaizen.sucursal: ~9 rows (aproximadamente)
INSERT INTO `sucursals` (`id`, `nombre`, `direccion`, `telefono`, `email`, `comentario`, `localidad_id`) VALUES
                                                                                                                                    (4, 'CALLE 7', 'calle 7 n 130 e/ 34 y 35            R270 REV 1', '0221-4831746      ', 'calle7@kaizenhonda.com.ar', '', 62),
                                                                                                                                    (5, 'CALLE 2', 'calle 2 esquina 42 n 506             R 270 REV 1', '0221-4276101              ', 'calle2@kaizenhonda.com.ar', '', 62),
                                                                                                                                    (6, 'ENSENADA', 'cestino n 557             R 270  REV 1', '0221-4692220', 'Ensenada@kaizenhonda.com.ar', 'DEPOSITO', 48),
                                                                                                                                    (7, 'DEPOSITO CALLE 7', '  RGC 31 REV 0', '555', '', '', 62),
                                                                                                                                    (8, 'DEPOSITO ALEMANIA', 'ALEMANIA N 73                                      RGC 31 REV 0', 'S/N', '', '', 48),
                                                                                                                                    (9, 'AVENIDA 44', '44 ESQ 142', '0221-481354782', '', '', 62),
                                                                                                                                    (10, 'CRED NOW CITY BELL', 'PZA BELGRANO N° 111 E/ 473 Y 14B', '0221-4800487', '', '', 38),
                                                                                                                                    (11, 'CRED NOW BERISSO', 'MONTEVIDEO 16 Y 17', '0221-4647852', '', '', 368),
                                                                                                                                    (12, 'MARKETPLACE BAPRO ', ' 2 esquina 42', '0221-4276101', 'info@kaizenhonda.com.ar ', '', 62);

-- Volcando datos para la tabla kaizen.tipo_servicio: ~16 rows (aproximadamente)
INSERT INTO `tipo_servicios` (`id`, `nombre`) VALUES
                                                                         (4, '1 SERVICIO / GARANTIA'),
                                                                         (5, '2 SERVICIO / GARANTIA'),
                                                                         (6, '3 SERVICIO / GARANTIA'),
                                                                         (7, '4 SERVICIO / GARANTIA'),
                                                                         (8, '5 SERVICIO / GARANTIA'),
                                                                         (9, '6 SERVICIO / GARANTIA'),
                                                                         (10, '7 SERVICIO / GARANTIA'),
                                                                         (11, 'SERVICIO GENERAL'),
                                                                         (12, 'REPARACION'),
                                                                         (13, '1 REINCIDENCIA '),
                                                                         (14, '2 REINCIDENCIA '),
                                                                         (15, '3 REINCIDENCIA '),
                                                                         (16, 'ALARMAS '),
                                                                         (17, 'REPARACION EN GARANTIA'),
                                                                         (18, 'PRE-ENTREGA 0 KM'),
                                                                         (19, 'CHECK IN 100 KM');

-- Volcando datos para la tabla kaizen.tipo_unidad: ~18 rows (aproximadamente)
INSERT INTO `tipo_unidads` (`id`, `nombre`) VALUES
                                                                   (9, 'MOTOCICLETA'),
                                                                   (10, 'CICLOMOTOR'),
                                                                   (11, 'CUATRICICLO'),
                                                                   (12, 'GENERADOR'),
                                                                   (13, 'MOTOBOMBA'),
                                                                   (14, 'CORTADORA'),
                                                                   (15, 'FUERA DE BORDA'),
                                                                   (16, 'SCOOTER'),
                                                                   (17, 'MOTOR EST'),
                                                                   (18, 'UTV '),
                                                                   (19, 'MOTO DE AGUA'),
                                                                   (20, 'HIDROLAVADORA '),
                                                                   (21, 'SOPLADORA / ASPIRADORA'),
                                                                   (22, 'ATOMIZADOR / PULVERIZADOR '),
                                                                   (23, 'BICICLETA '),
                                                                   (24, 'TRICICLO'),
                                                                   (25, 'MONOPATIN'),
                                                                   (26, 'CARRO GOLF ');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;



###############################################15/05/2025##########################################


	SELECT `cd_stockpieza` as id, `cd_pieza` as pieza_id, `nu_cantidad` as cantidad, `qt_costo` as costo, `qt_minimo` as precio_minimo, `cd_sucursal` as sucursal_id,
proveedor.ds_proveedor as proveedor, `dt_ingreso` as ingreso, `ds_remito` as remito
FROM `stockpieza`
INNER JOIN pieza on stockpieza.cd_pieza = pieza.cd_pieza
LEFT JOIN proveedor ON stockpieza.cd_proveedor = proveedor.cd_proveedor


SELECT unidad_movimiento.`cd_unidad` as unidad_id, unidad_movimiento.`cd_movimiento` as movimiento_id
FROM `unidad_movimiento`
         INNER JOIN unidad on unidad_movimiento.cd_unidad = unidad.cd_unidad
         INNER JOIN movimiento ON unidad_movimiento.cd_movimiento = movimiento.cd_movimiento

###############################################27/05/2025#################################################
SELECT
    ventapieza.cd_ventapieza AS id,
    ventapieza.nu_preciocobrado AS precio,
    ventapieza.nu_preciomin AS precio_minimo,
    ventapieza.ds_apynomcliente AS cliente,
    ventapieza.nu_docCliente AS documento,
    ventapieza.ds_telcliente AS telefono,
    ventapieza.ds_motocliente AS moto,
    CASE
        WHEN ventapieza.cd_sucursal = '0' THEN NULL
        ELSE ventapieza.cd_sucursal
        END AS sucursal_id,
    ventapieza.nu_pedidoreparacion AS pedido,
    ventapieza.dt_ventapieza AS fecha,
    ventapieza.ds_descripcion AS descripcion,
    CASE ventapieza.nu_destino
        WHEN '1' THEN 'Salón'
        WHEN '2' THEN 'Sucursal'
        WHEN '3' THEN 'Taller'
        END AS destino,
    usuario.ds_nomusuario AS user_name
FROM ventapieza
         LEFT JOIN usuario ON ventapieza.cd_usuario = usuario.cd_usuario;


########################################### crear una pieza para apuntar los stock piezas que se quedan sin relacion########################################
    INSERT INTO piezas (codigo, descripcion)
VALUES ('PIEZA ELIMINADA', 'Pieza asociada a registros de stock inconsistentes');

####OJO!!!! ver el id creado #################
UPDATE stock_piezas
SET pieza_id = 5616
WHERE pieza_id NOT IN (SELECT id FROM piezas);


SELECT `cd_ventapieza` as venta_pieza_id, `cd_pieza` as pieza_id,`cd_sucursal` as sucursal_id,`nu_cantidadpedida` as cantidad, `qt_montoacobrar` as precio
FROM `ventapieza_unidad` WHERE 1

###############################################25/08/2025#################################################
SELECT
    cd_cliente AS id,
    ds_apynom AS nombre,
    nu_doc AS documento,
    ds_cuil_cuit AS cuil,
    dt_nacimiento as nacimiento,
    EC AS estado_civil,
    ds_email AS email,

    -- Teléfono particular
    CASE
        WHEN ds_telparticular LIKE '%-%'
            THEN SUBSTRING_INDEX(REGEXP_REPLACE(ds_telparticular, '[^0-9-]', ''), '-', 1)
        WHEN LENGTH(num_solo_particular) > 7
            THEN LEFT(num_solo_particular, LENGTH(num_solo_particular) - 7)
    ELSE NULL
END AS particular_area,
    CASE
        WHEN ds_telparticular LIKE '%-%'
            THEN SUBSTRING_INDEX(REGEXP_REPLACE(ds_telparticular, '[^0-9-]', ''), '-', -1)
        WHEN LENGTH(num_solo_particular) > 7
            THEN RIGHT(num_solo_particular, 7)
        ELSE num_solo_particular
END AS particular,

    -- Teléfono laboral
    CASE
        WHEN ds_tellaboral LIKE '%-%'
            THEN SUBSTRING_INDEX(REGEXP_REPLACE(ds_tellaboral, '[^0-9-]', ''), '-', 1)
        WHEN LENGTH(num_solo_laboral) > 7
            THEN LEFT(num_solo_laboral, LENGTH(num_solo_laboral) - 7)
        ELSE NULL
END AS celular_area,
    CASE
        WHEN ds_tellaboral LIKE '%-%'
            THEN SUBSTRING_INDEX(REGEXP_REPLACE(ds_tellaboral, '[^0-9-]', ''), '-', -1)
        WHEN LENGTH(num_solo_laboral) > 7
            THEN RIGHT(num_solo_laboral, 7)
        ELSE num_solo_laboral
END AS celular,

    ds_dircalle AS calle,
    ds_dirnro AS nro,
    ds_dirpiso AS piso,
    ds_dirdepto AS depto,
    cd_localidad AS localidad_id,
    ds_cp AS cp,
    ds_nacionalidad AS nacionalidad,
    ds_actividad_ocupacion AS ocupacion,
    ds_lugar_trabajo AS trabajo,
    CI AS iva,
    CL AS llego

FROM (
    SELECT
        cd_cliente,
        ds_apynom,
        nu_doc,
        ds_cuil_cuit,
        dt_nacimiento,
        estadocivil.ds_estadocivil AS EC,
        ds_email,
        ds_telparticular,
        ds_tellaboral,
        REGEXP_REPLACE(ds_telparticular, '[^0-9]', '') AS num_solo_particular,
        REGEXP_REPLACE(ds_tellaboral, '[^0-9]', '') AS num_solo_laboral,
        ds_dircalle,
        ds_dirnro,
        ds_dirpiso,
        ds_dirdepto,
        cd_localidad,
        ds_cp,
        ds_nacionalidad,
        ds_actividad_ocupacion,
        ds_lugar_trabajo,
        condiva.ds_condiva AS CI,
        comollego.ds_comollego AS CL
    FROM cliente
    LEFT JOIN estadocivil ON cliente.cd_estadocivil = estadocivil.cd_estadocivil
    LEFT JOIN condiva ON cliente.cd_condiva = condiva.cd_condiva
    LEFT JOIN comollego ON cliente.cd_comollego = comollego.cd_comollego
) AS sub;


