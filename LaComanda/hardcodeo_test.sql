
CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre TEXT
);

CREATE TABLE estados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre TEXT
);

CREATE TABLE estadosMesa (
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
        
INSERT INTO estadosMesa (nombre)
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
    nombreCliente TEXT,
    pathFoto TEXT,
    nroMesa INT(5),
    cuenta DECIMAL(10, 2),
    idEstadoMesa INT(1),
    FOREIGN KEY(idEstadoMesa) REFERENCES estadosMesa(id)
);

CREATE TABLE pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nroMesa INT(5),
    precio DECIMAL(10, 2),
    idEstadoProceso INT(1),
    FOREIGN KEY(idEstadoProceso) REFERENCES estadosPedido(id)
);

CREATE TABLE productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre TEXT,
    precio DECIMAL(10, 2),
    idCategoria INT(1),
    FOREIGN KEY(idCategoria) REFERENCES categorias(id),
    idEstado INT(1),
    FOREIGN KEY(idEstado) REFERENCES estados(id)
);

CREATE TABLE listaProductos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    idPedido INT(1),
    FOREIGN KEY(idPedido) REFERENCES pedidos(id),
    idProducto INT(1),
    FOREIGN KEY(idProducto) REFERENCES productos(id)
);

CREATE TABLE ventas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nroMesa INT(5),
    fecha TEXT,
    idEstado INT(1),
    FOREIGN KEY(idEstado) REFERENCES estados(id)
);

CREATE TABLE listaPedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    idPedido INT(1),
    FOREIGN KEY(idPedido) REFERENCES pedidos(id),
    idVenta INT(1),
    FOREIGN KEY(idVenta) REFERENCES ventas(id)
);


INSERT INTO empleados (nombre, idRol, fechaAlta, fechaBaja, idEstado)
VALUES	("pepito", 1, "2024-10-29", "", 1),
		("jose", 2, "2024-10-15", "", 1),
        ("carlos", 3, "2024-10-22", "", 1);

INSERT INTO mesas (nombreCliente, pathFoto, nroMesa, cuenta, idEstadoMesa)
VALUES	("pepe", "fotos" ,23233, 0, 1),
		("carlos", "fotos" 23253, 32500, 3),
        ("juan", "fotos" 23213, 15000, 4);

INSERT INTO pedidos (idMesa, precio, idEstadoProceso)
VALUES	(1, 0, 1),
        (2, 9200, 2);

INSERT INTO productos (nombre, precio, idCategoria)
VALUES	("Milanesa a caballo", 13500, 3, 1),
        ("Cerveza Corona", 4200, 2, 1),
        ("Papas fritas", 5000, 3, 1);

INSERT INTO listaProductos (idPedido, idProducto)
VALUES	(2, 3),
        (2, 2);