create database pruebas2;

use pruebas2;

-- select * from usuario;
-- select * from donante;
-- select * from organizacion;
-- select * from categoria;
-- select * from necesidad;

create table usuario(
    id_usuario int auto_increment primary key,
    email varchar(200) unique not null,
    password text not null,
    tipo_usuario enum('organizacion', 'donante', 'admin') not null,
    fecha_registro timestamp default current_timestamp,
    estado enum('activo', 'inactivo') default 'activo'
);

create table administrador(
    id_admin int auto_increment primary key,
    id_usuario int unique not null,
    nombre varchar(100) not null,
    apellido varchar(100) not null,
    foreign key (id_usuario) references usuario(id_usuario)
);

create table donante(
    id_donante int auto_increment primary key,
    id_usuario int unique not null,
    nombre_completo varchar(200) not null,
    fecha_nacimiento date not null,
    dni char(8) unique not null,
    num_donaciones_realizadas int default 0,
    foreign key (id_usuario) references usuario(id_usuario)

);

create table organizacion(
    id_organizacion int auto_increment primary key,
    id_usuario int unique not null,
    razon_social varchar(100) not null,
    ruc char(11) unique not null,
    telefono char(9) not null,
    direccion text not null,
    tipo_organizacion ENUM('comedor_popular', 'albergue', 'centro_comunitario', 'escuela', 'otro') not null,
    otro_tipo text,
    descripcion_corta varchar(50) not null,
    descripcion_larga text not null,
    estado_verificacion ENUM('pendiente', 'aprobada', 'rechazada') DEFAULT 'pendiente',
    fecha_verificacion date,
    foreign key (id_usuario) references usuario(id_usuario)
);

create table categoria(
    id_categoria int auto_increment primary key,
    nombre varchar(100) unique not null,
    estado enum('activa', 'inactiva') default 'activa'
);

create table necesidad(
    id_necesidad int auto_increment primary key,
    id_organizacion int not null,
    id_categoria int not null,
    nombre varchar(100) not null,
    prioridad enum('alta', 'media', 'baja') not null,
    cantidad_solicitada int not null,
    cantidad_recaudada int default 0,
    unidad_medida varchar(50),
    descripcion_corta varchar(50) not null,
    descripcion_larga text not null,
    fecha_publicacion timestamp default current_timestamp,
    estado enum('pendiente','activa', 'cubierta', 'vencida', 'cancelada') default 'pendiente',
    FOREIGN KEY (id_organizacion) REFERENCES organizacion(id_organizacion),
    FOREIGN KEY (id_categoria) REFERENCES categoria(id_categoria)
);


create table donacion(
    id_donacion int auto_increment primary key,
    id_donante int not null,
    id_necesidad int not null,
    cantidad_donanda int not null,
    fecha_emision datetime default now(),
    fecha_entreda datetime,
    estado ENUM('reservada', 'confirmada', 'entregada', 'cancelada') default 'reservada',
    comentario text,
    foreign key (id_donante) references donante(id_donante),
    foreign key (id_necesidad) references necesidad(id_necesidad)
);



-- Tabla de historial de donaciones por donante
CREATE TABLE historial_donacion (
    id_historial INT AUTO_INCREMENT PRIMARY KEY,
    id_donante INT NOT NULL,
    id_donacion INT NOT NULL,
    fecha_accion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    accion ENUM('reservada', 'confirmada', 'entregada', 'cancelada') NOT NULL,
    FOREIGN KEY (id_donante) REFERENCES donante(id_donante) ON DELETE CASCADE,
    FOREIGN KEY (id_donacion) REFERENCES donacion(id_donacion) ON DELETE CASCADE
);

-- VISTAS ADICIONALES

CREATE VIEW view_necesidad_card AS
SELECT
    n.id_necesidad,
    n.nombre AS nombre_necesidad,
    n.prioridad,
    o.razon_social,
    o.direccion,
    n.descripcion_corta,
    c.nombre AS categoria
FROM
    necesidad n
JOIN
    organizacion o ON n.id_organizacion = o.id_organizacion
JOIN
    categoria c ON n.id_categoria = c.id_categoria;

CREATE VIEW view_organizacion_card AS
SELECT
    razon_social,
    ruc,
    direccion AS ubicacion,
    descripcion_corta,
    tipo_organizacion
FROM
    organizacion
WHERE
    estado_verificacion = 'aprobada';


CREATE VIEW view_usuario_perfil AS
SELECT
    u.id_usuario,
    u.email,
    u.password,
    u.fecha_registro,
    u.estado,

    -- Datos del administrador
    a.id_admin,
    a.nombre AS admin_nombre,
    a.apellido AS admin_apellido,

    -- Datos del donante
    d.id_donante,
    d.nombre_completo AS donante_nombre,
    d.fecha_nacimiento,
    d.dni,
    d.num_donaciones_realizadas,

    -- Datos de la organización
    o.id_organizacion,
    o.razon_social,
    o.ruc,
    o.telefono,
    o.direccion,
    o.tipo_organizacion,
    o.otro_tipo,
    o.descripcion_corta,
    o.descripcion_larga,
    o.estado_verificacion,
    o.fecha_verificacion

FROM usuario u
LEFT JOIN administrador a ON u.id_usuario = a.id_usuario
LEFT JOIN donante d ON u.id_usuario = d.id_usuario
LEFT JOIN organizacion o ON u.id_usuario = o.id_usuario;
