CREATE TABLE pedidos (
    id INT(11) NOT NULL AUTO_INCREMENT,
    mesa_id INT(11) NOT NULL,
    cliente_nombre VARCHAR(255) NOT NULL,
    productos TEXT NOT NULL,
    mozo_responsable INT(11) NOT NULL,
    estado VARCHAR(50) NOT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (mesa_id) REFERENCES mesas(id),
    FOREIGN KEY (mozo_responsable) REFERENCES empleados(id)
);