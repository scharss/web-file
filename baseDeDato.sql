-- Crear la tabla usuarios
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'suscriptor') NOT NULL DEFAULT 'suscriptor',
    storage_limit INT DEFAULT 10
);

-- Crear la tabla archivos
CREATE TABLE IF NOT EXISTS archivos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    nombre_archivo VARCHAR(255) NOT NULL,
    ruta VARCHAR(255) NOT NULL,
    tamano INT NOT NULL,
    fecha_subida TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES usuarios(id) ON DELETE CASCADE
);


-- Crear un usuario administrador con nombre de usuario: admin y contraseña cifrada: motostung1
INSERT INTO usuarios (username, password, role, storage_limit) 
VALUES ('admin', 'micontraseña', 'admin', 100);

-- Crear un usuario suscriptor con nombre de usuario: suscriptor@suscriptor.com y contraseña cifrada: 1234
INSERT INTO usuarios (username, password, role, storage_limit) 
VALUES ('correoDelUsuario', 'micontraseña', 'suscriptor', 10);
