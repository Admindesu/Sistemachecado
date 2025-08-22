-- Crear tabla de horarios
CREATE TABLE IF NOT EXISTS horarios (
    id_horario INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    hora_entrada TIME NOT NULL,
    hora_salida TIME NOT NULL,
    tolerancia_entrada INT NOT NULL DEFAULT 10, -- minutos de tolerancia para entrada
    limite_retardo INT NOT NULL DEFAULT 20, -- minutos máximos para considerar retardo
    descripcion TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Agregar horario por defecto (9:00 AM - 5:00 PM)
INSERT INTO horarios (nombre, hora_entrada, hora_salida, tolerancia_entrada, limite_retardo, descripcion) 
VALUES ('Horario Default', '09:00:00', '17:00:00', 10, 20, 'Horario estándar de 9 AM a 5 PM');

-- Agregar horario alternativo (7:00 AM - 2:30 PM)
INSERT INTO horarios (nombre, hora_entrada, hora_salida, tolerancia_entrada, limite_retardo, descripcion)
VALUES ('Horario Matutino', '07:00:00', '14:30:00', 10, 20, 'Horario matutino de 7 AM a 2:30 PM');

-- Agregar columna de horario a la tabla empleado
ALTER TABLE empleado
ADD COLUMN id_horario INT,
ADD FOREIGN KEY (id_horario) REFERENCES horarios(id_horario);

-- Asignar horario por defecto a empleados existentes
UPDATE empleado SET id_horario = 1 WHERE id_horario IS NULL;
