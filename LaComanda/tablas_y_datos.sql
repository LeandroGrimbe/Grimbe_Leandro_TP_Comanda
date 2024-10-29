
CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre TEXT
);

CREATE TABLE estados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre TEXT
);

CREATE TABLE estadosComida (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre TEXT
);

CREATE TABLE estadosPedido (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre TEXT
);

CREATE TABLE categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre TEXT
);

INSERT INTO roles (nombre)
VALUES	("bartender"),
		("cervecero"),
        ("cocinero"),
        ("mozo"),
        ("socio");
        
INSERT INTO estados (nombre)
VALUES	("activo"),
		("inactivo");
        
INSERT INTO estadosComida (nombre)
VALUES	("cerrada"),
		("clientes esperando pedido"),
        ("clientes comiendo"),
        ("clientes Pagando");

INSERT INTO estadosPedido (nombre)
VALUES	("cerrado"),
		("en preparacion"),
        ("listo para servir");

INSERT INTO categorias (nombre)
VALUES	("barra de tragos"),
		("barra de chopera"),
        ("cocina"),
        ("candy bar");

-------------------------------------------------------------------------

CREATE TABLE empleados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre TEXT,
    idRol INT(1),
    FOREIGN KEY(idRol) REFERENCES roles(id),
    fechaAlta DATE,
    fechaBaja DATE,
    idEstado INT(1),
    FOREIGN KEY(idEstado) REFERENCES estados(id)
);

CREATE TABLE mesas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cuenta DECIMAL(10, 2),
    idEstadoComida INT(1),
    FOREIGN KEY(idEstadoComida) REFERENCES estadosComida(id)
);

CREATE TABLE pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    idMesa INT(1),
    FOREIGN KEY(idMesa) REFERENCES mesas(id),
    precio DECIMAL(10, 2),
    idEstadoProceso INT(1),
    FOREIGN KEY(idEstadoProceso) REFERENCES estadosPedido(id)
);

CREATE TABLE productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre TEXT,
    precio DECIMAL(10, 2),
    idCategoria INT(1),
    FOREIGN KEY(idCategoria) REFERENCES categorias(id)
);

CREATE TABLE listaProductos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    idPedido INT(1),
    FOREIGN KEY(idPedido) REFERENCES pedidos(id),
    idProducto INT(1),
    FOREIGN KEY(idProducto) REFERENCES productos(id),
    cantidad INT(3)
);


INSERT INTO empleados (nombre, idRol, fechaAlta, fechaBaja, idEstado)
VALUES	("pepito", 1, "2024-10-29", "", 1),
		("jose", 2, "2024-10-15", "", 1),
        ("carlos", 3, "2024-10-22", "", 1);

INSERT INTO mesas (cuenta, idEstadoComida)
VALUES	(0, 0),
		(32500, 3),
        (15000, 4);

INSERT INTO pedidos (idMesa, precio, idEstadoProceso)
VALUES	(1, 0, 1),
        (2, 9200, 2);

INSERT INTO productos (nombre, precio, idCategoria)
VALUES	("Milanesa a caballo", 13500, 3),
        ("Cerveza Corona", 4200, 2),
        ("Papas fritas", 5000, 3);

INSERT INTO listaProductos (idPedido, idProducto, cantidad)
VALUES	(2, 3, 1),
        (2, 2, 1);
