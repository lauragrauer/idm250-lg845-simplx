CREATE TABLE users (
    id       INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE skus (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    ficha          INT            DEFAULT 0,
    sku            VARCHAR(50)    NOT NULL UNIQUE,
    description    VARCHAR(255)   NOT NULL,
    uom_primary    ENUM('BUNDLE','PALLET') DEFAULT 'BUNDLE',
    piece_count    INT            DEFAULT 0,
    length_inches  DECIMAL(10,2)  DEFAULT 0.00,
    width_inches   DECIMAL(10,2)  DEFAULT 0.00,
    height_inches  DECIMAL(10,2)  DEFAULT 0.00,
    weight_lbs     DECIMAL(10,2)  DEFAULT 0.00,
    assembly       VARCHAR(5)     DEFAULT 'false',
    rate           DECIMAL(10,2)  DEFAULT 0.00,
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE inventory (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    unit_id    VARCHAR(100) NOT NULL UNIQUE,
    sku_id     INT          NOT NULL,
    location   ENUM('internal','warehouse') DEFAULT 'internal',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sku_id) REFERENCES skus(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE mpls (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    reference_number VARCHAR(100) NOT NULL UNIQUE,
    trailer_number   VARCHAR(100),
    expected_arrival DATE,
    status           ENUM('draft','sent','confirmed') DEFAULT 'draft',
    created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE mpl_items (
    id      INT AUTO_INCREMENT PRIMARY KEY,
    mpl_id  INT          NOT NULL,
    unit_id VARCHAR(100) NOT NULL,
    FOREIGN KEY (mpl_id) REFERENCES mpls(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE orders (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    order_number     VARCHAR(100) NOT NULL UNIQUE,
    ship_to_company  VARCHAR(255),
    ship_to_street   VARCHAR(255),
    ship_to_city     VARCHAR(100),
    ship_to_state    VARCHAR(10),
    ship_to_zip      VARCHAR(20),
    status           ENUM('draft','sent','confirmed') DEFAULT 'draft',
    shipped_at       DATE,
    created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE order_items (
    id       INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT          NOT NULL,
    unit_id  VARCHAR(100) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE shipped_items (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    order_id        INT          NOT NULL,
    order_number    VARCHAR(100) NOT NULL,
    unit_id         VARCHAR(100) NOT NULL,
    sku             VARCHAR(50)  NOT NULL,
    sku_description VARCHAR(255) NOT NULL,
    shipped_at      DATE,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO users (username, password) VALUES (
    'philphil',
    '$2y$10$0Dek9wIhlUlJ.2o/R7cz4.MEhg042cAQKFPRstnOa32FDvzOcKQqq'
);

INSERT INTO skus (ficha, sku, description, uom_primary, piece_count, length_inches, width_inches, height_inches, weight_lbs, assembly, rate) VALUES
(724, '1720813-0132', 'MDF ST LX C2-- 2465X1245X05.7MM P/EF/132', 'BUNDLE', 250,  96.00, 39.00, 29.65, 3945.22, 'false', 15.16),
(987, '1720814-0248', 'PINE CLR VG 2X4X8FT KD SELECT',             'BUNDLE', 200,  96.00, 42.00, 36.00, 2850.50, 'false', 16.18),
(337, '1720815-0156', 'OAK RED FAS 4/4 RGH KD 8-12FT',             'PALLET', 150, 120.00, 48.00, 42.00, 4125.75, 'false', 15.16),
(778, '1720816-0089', 'SPRUCE DIMENSION 2X6X12FT #2BTR',            'BUNDLE', 180, 144.00, 36.00, 30.00, 3280.00, 'false', 14.50),
(187, '1720817-0234', 'CEDAR WRC CVG 1X6X8FT CLR S4S',              'BUNDLE', 300,  96.00, 36.00, 24.00, 1890.25, 'false', 20.06),
(223, '1720818-0167', 'MAPLE HARD FAS 5/4 RGH KD 10FT',             'PALLET', 120, 120.00, 48.00, 38.00, 3750.80, 'false', 16.18),
(876, '1720819-0312', 'PLYWOOD BALTIC BIRCH 3/4X4X8',               'PALLET',  45,  96.00, 48.00, 36.00, 2980.00, 'false', 17.02),
(223, '1720820-0098', 'POPLAR FAS 4/4 RGH KD 8-14FT',               'BUNDLE', 175, 144.00, 42.00, 32.00, 2650.40, 'false', 16.14),
(991, '1720821-0445', 'WALNUT BLK FAS 4/4 RGH KD 8FT',              'PALLET',  80,  96.00, 48.00, 28.00, 2240.60, 'false', 12.14),
(901, '1720822-0223', 'DOUGLAS FIR CVG 2X10X16FT #1',               'BUNDLE', 100, 192.00, 48.00, 40.00, 4580.90, 'false', 16.18),
(452, '1720823-0567', 'BIRCH YEL FAS 6/4 RGH KD 10FT',              'PALLET',  95, 120.00, 44.00, 34.00, 3120.45, 'false', 18.22),
(163, '1720824-0891', 'HEMLOCK DIM 2X8X14FT #2BTR STD',             'BUNDLE', 160, 168.00, 40.00, 28.50, 2975.30, 'false', 14.85),
(589, '1720825-0234', 'ASH WHT FAS 4/4 RGH KD 9-11FT',              'PALLET', 110, 132.00, 46.00, 40.00, 3540.60, 'false', 15.92),
(734, '1720826-0412', 'MDF ULTRALT C1-- 2440X1220X18MM',             'BUNDLE',  85,  96.00, 48.00, 52.00, 4250.75, 'false', 13.44),
(298, '1720827-0178', 'CHERRY BLK SEL 5/4 RGH KD 8FT',              'PALLET',  70,  96.00, 42.00, 26.00, 1980.20, 'false', 21.35),
(641, '1720828-0923', 'REDWOOD CLR VG 2X4X10FT KD HRT',             'BUNDLE', 225, 120.00, 38.00, 32.00, 2430.85, 'false', 19.78),
(812, '1720829-0056', 'PARTICLEBOARD IND 3/4X49X97',                 'PALLET',  60,  97.00, 49.00, 45.00, 3890.40, 'false', 11.56),
(445, '1720830-0789', 'ALDER RED SEL 4/4 RGH KD 8-10FT',            'BUNDLE', 140, 120.00, 40.00, 30.00, 2180.55, 'false', 17.64),
(127, '1720831-0345', 'WHITE OAK QS 4/4 RGH KD 10FT',               'PALLET',  65, 120.00, 48.00, 38.00, 2890.70, 'false', 22.40),
(568, '1720832-0612', 'SOUTHERN PINE PT 4X4X12FT GC',                'BUNDLE', 130, 144.00, 44.00, 48.00, 5120.35, 'false', 13.28),
(185, '1720833-0150', 'PINE CLR 2X6X8FT SELECT',                     'BUNDLE', 210,  96.00, 42.00, 32.00, 2650.00, 'false', 17.10),
(638, '1720834-0164', 'HEM-FIR 2X4X16FT #2',                         'BUNDLE', 180, 192.00, 42.00, 38.00, 3950.00, 'false', 14.70),
(921, '1720835-0178', 'CHERRY FAS 4/4 KD 8-10FT',                    'PALLET', 100, 120.00, 48.00, 36.00, 3200.00, 'false', 18.90),
(346, '1720836-0192', 'ASH 5/4 FAS KD 11FT',                         'PALLET', 120, 132.00, 48.00, 40.00, 3800.00, 'false', 17.60),
(709, '1720837-0206', 'SPRUCE 2X8X10FT #2',                          'BUNDLE', 150, 120.00, 48.00, 36.00, 3600.00, 'false', 15.20),
(174, '1720838-0220', 'CEDAR RED 2X4X8FT S1S2E',                     'BUNDLE', 240,  96.00, 36.00, 30.00, 2200.00, 'false', 20.30),
(582, '1720839-0234', 'BALTIC BIRCH PLY 18MM 5X5',                   'PALLET',  38,  60.00, 60.00, 48.00, 3100.00, 'false', 18.10),
(837, '1720840-0248', 'PINE #2 2X10X12FT KD',                        'BUNDLE', 110, 144.00, 48.00, 40.00, 4100.00, 'false', 16.40),
(291, '1720841-0262', 'OAK WHITE 4/4 FAS KD 10FT',                   'PALLET', 130, 120.00, 48.00, 42.00, 4350.00, 'false', 16.70),
(653, '1720842-0276', 'MAPLE SOFT 4/4 KD 8FT',                       'BUNDLE', 170,  96.00, 42.00, 32.00, 2600.00, 'false', 15.50);

INSERT INTO inventory (unit_id, sku_id, location) VALUES
('48114995', 18, 'internal'), ('48114996', 18, 'internal'), ('48114997', 18, 'internal'),
('48114998', 18, 'internal'), ('48114999', 18, 'internal'), ('48115000', 18, 'internal'),
('48115001', 18, 'internal'), ('48115002', 18, 'internal'), ('48115003', 18, 'internal'),
('48115004', 18, 'internal'), ('48115005', 18, 'internal'), ('48115006', 18, 'internal'),
('48115007', 18, 'internal'), ('48115008', 18, 'internal'),
('48115009', 24, 'internal'), ('48115010', 24, 'internal'), ('48115011', 24, 'internal'),
('48115012', 24, 'internal'), ('48115013', 24, 'internal'), ('48115014', 24, 'internal'),
('48115015', 24, 'internal'), ('48115016', 24, 'internal'), ('48115017', 24, 'internal'),
('48115018', 24, 'internal'), ('48115019', 24, 'internal'), ('48115020', 24, 'internal'),
('48115021', 24, 'internal'), ('48115022', 24, 'internal'), ('48115023', 24, 'internal'),
('48115024', 14, 'internal'), ('48115025', 14, 'internal'), ('48115026', 14, 'internal'),
('48115027', 14, 'internal'), ('48115028', 14, 'internal'), ('48115029', 14, 'internal'),
('48115030', 14, 'internal'), ('48115031', 14, 'internal'), ('48115032', 14, 'internal'),
('48115033', 14, 'internal'), ('48115034', 14, 'internal'), ('48115035', 14, 'internal'),
('48115036', 14, 'internal'), ('48115037', 14, 'internal'), ('48115038', 14, 'internal'),
('48115039', 12, 'internal'), ('48115040', 12, 'internal'), ('48115041', 12, 'internal'),
('48115042', 12, 'internal'), ('48115043', 12, 'internal');