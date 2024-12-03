
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
VALUES	("clientes esperando pedido"),
        ("clientes comiendo"),
        ("clientes Pagando"),
        ("libre"),
        ("cancelada");

INSERT INTO estadosPedido (nombre)
VALUES	("pendiente"),
		("en preparacion"),
        ("listo para servir"),
        ("completado"),
        ("cancelado");

INSERT INTO categorias (nombre)
VALUES	("barra de tragos"),
		("barra de chopera"),
        ("cocina"),
        ("candy bar");


CREATE TABLE empleados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario TEXT,
    clave TEXT,
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
    fecha TEXT,
    idEstadoMesa INT(1),
    FOREIGN KEY(idEstadoMesa) REFERENCES estadosMesa(id)
);

CREATE TABLE pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    idMesa INT(10),
    FOREIGN KEY(idMesa) REFERENCES mesas(id),
    precio DECIMAL(10, 2),
    idEstado INT(1),
    FOREIGN KEY(idEstado) REFERENCES estados(id)
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

CREATE TABLE listaProductosPedido (
    id INT AUTO_INCREMENT PRIMARY KEY,
    idPedido INT(10),
    FOREIGN KEY(idPedido) REFERENCES pedidos(id),
    idProducto INT(3),
    FOREIGN KEY(idProducto) REFERENCES productos(id),
    tiempoPreparacion DATETIME,
    idEstadoPedido INT(1),
    FOREIGN KEY(idEstadoPedido) REFERENCES estadosPedido(id)
);

CREATE TABLE encuestas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nroMesa INT(5),
    puntuacionMesa INT(2),
    puntuacionRestaurante INT(2),
    puntuacionMozo INT(2),
    puntuacionCocinero INT(2),
    comentarios TEXT,
    fecha DATE
);

INSERT INTO empleados (usuario, clave, idRol, fechaAlta, fechaBaja, idEstado)
VALUES	("jose", 1111, 1, "2024-10-29", "", 1),
		("ivan", 2222, 2, "2024-10-15", "", 1),
        ("leandro", 3333, 3, "2024-10-22", "", 1),
        ("pedro", 4444, 4, "2024-10-22", "", 1),
        ("franco", 5555, 5, "2024-10-22", "", 1);

INSERT INTO productos (nombre, precio, idCategoria, idEstado)
VALUES	("Milanesa a caballo", 13500, 3, 1),
        ("Hamburguesa de garbanzo", 12000, 3, 1),
        ("Cerveza Corona", 8000, 2, 1),
        ("Daikiri", 5000, 1, 1);

