-- Tabla para los empleados
CREATE TABLE empleados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    rol ENUM('mozo', 'bartender', 'cervecero', 'cocinero', 'socio') NOT NULL,
    estado ENUM('activo', 'suspendido', 'borrado') DEFAULT 'activo',
    fecha_ingreso TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla para las mesas
CREATE TABLE mesas (
    id CHAR(5) PRIMARY KEY,  -- Código único de la mesa (5 caracteres alfanuméricos)
    estado ENUM('con cliente esperando pedido', 'con cliente comiendo', 'con cliente pagando', 'cerrada') NOT NULL,
    mozo_responsable INT,
    FOREIGN KEY (mozo_responsable) REFERENCES empleados(id)
);

-- Tabla para los pedidos
CREATE TABLE pedidos (
    id CHAR(5) PRIMARY KEY,  -- Código único del pedido (5 caracteres alfanuméricos)
    mesa_id CHAR(5),
    cliente_nombre VARCHAR(100),
    estado ENUM('pendiente', 'en preparación', 'listo para servir', 'cancelado') NOT NULL DEFAULT 'pendiente',
    tiempo_estimado INT,  -- Tiempo estimado en minutos
    empleado_responsable INT,  -- Puede ser un bartender, cocinero, etc.
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (mesa_id) REFERENCES mesas(id),
    FOREIGN KEY (empleado_responsable) REFERENCES empleados(id)
);

-- Tabla para los comentarios y encuestas
CREATE TABLE comentarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mesa_id CHAR(5),
    puntuacion_mesa INT CHECK (puntuacion_mesa BETWEEN 1 AND 10),
    puntuacion_restaurante INT CHECK (puntuacion_restaurante BETWEEN 1 AND 10),
    puntuacion_mozo INT CHECK (puntuacion_mozo BETWEEN 1 AND 10),
    puntuacion_cocinero INT CHECK (puntuacion_cocinero BETWEEN 1 AND 10),
    comentario VARCHAR(66),  -- Máximo 66 caracteres
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (mesa_id) REFERENCES mesas(id)
);