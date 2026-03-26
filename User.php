<?php
class User {
    private $db;

    public function __construct() {
        $this->db = Database::obtenerConexion();
    }

    /**
     * Registra un nuevo usuario
     */
    public function registrar($datos) {
        try {
            // Verificar si email, username o cédula ya existen
            $sql = "SELECT id FROM usuarios WHERE email = :email OR username = :username OR cedula = :cedula";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':email', $datos['email']);
            $stmt->bindParam(':username', $datos['username']);
            $stmt->bindParam(':cedula', $datos['cedula']);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                return "El email, nombre de usuario o cédula ya están registrados.";
            }

            // Hashear contraseña
            $passwordHash = password_hash($datos['password'], PASSWORD_DEFAULT);

            // Procesar intereses (array a string separado por comas)
            $intereses = !empty($datos['intereses']) ? implode(',', $datos['intereses']) : '';

            // Insertar
            $sql = "INSERT INTO usuarios 
                    (nombre, cedula, telefono, email, username, password, carrera, intereses, nivel_experiencia, estado, fecha_registro) 
                    VALUES 
                    (:nombre, :cedula, :telefono, :email, :username, :password, :carrera, :intereses, :nivel_experiencia, 'pendiente', NOW())";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':nombre', $datos['nombre']);
            $stmt->bindParam(':cedula', $datos['cedula']);
            $stmt->bindParam(':telefono', $datos['telefono']);
            $stmt->bindParam(':email', $datos['email']);
            $stmt->bindParam(':username', $datos['username']);
            $stmt->bindParam(':password', $passwordHash);
            $stmt->bindParam(':carrera', $datos['carrera']);
            $stmt->bindParam(':intereses', $intereses);
            $stmt->bindParam(':nivel_experiencia', $datos['nivel_experiencia']);

            if ($stmt->execute()) {
                return true;
            } else {
                $errorInfo = $stmt->errorInfo();
                return "Error al registrar: " . $errorInfo[2];
            }
        } catch (PDOException $e) {
            return "Error de base de datos: " . $e->getMessage();
        }
    }

    /**
     * Inicia sesión con email o username
     */
    public function login($identificador, $password) {
        $sql = "SELECT * FROM usuarios WHERE email = :identificador OR username = :identificador";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':identificador', $identificador);
        $stmt->execute();
        $usuario = $stmt->fetch();

        if ($usuario && password_verify($password, $usuario['password'])) {
            if ($usuario['estado'] !== 'activo') {
                return "Tu cuenta está pendiente de aprobación o inactiva.";
            }
            
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nombre'] = $usuario['nombre'];
            $_SESSION['usuario_email'] = $usuario['email'];
            $_SESSION['usuario_rol'] = $usuario['rol'];
            $_SESSION['usuario_username'] = $usuario['username'];
            $_SESSION['usuario_avatar'] = $usuario['avatar'];
            
            // Actualizar último acceso
            $this->actualizarUltimoAcceso($usuario['id']);
            
            return true;
        } else {
            return "Credenciales incorrectas.";
        }
    }

    /**
     * Actualizar último acceso del usuario
     */
    private function actualizarUltimoAcceso($id) {
        $sql = "UPDATE usuarios SET ultimo_acceso = NOW() WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
    }

    /**
     * Verifica si hay sesión activa
     */
    public static function estaLogueado() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['usuario_id']);
    }

    /**
     * Verifica si el usuario logueado es admin
     */
    public static function esAdmin() {
        return self::estaLogueado() && $_SESSION['usuario_rol'] === 'admin';
    }

    /**
     * Obtiene el ID del usuario logueado
     */
    public static function getId() {
        return self::estaLogueado() ? $_SESSION['usuario_id'] : null;
    }

    /**
     * Obtiene el nombre del usuario logueado
     */
    public static function getNombre() {
        return self::estaLogueado() ? $_SESSION['usuario_nombre'] : null;
    }

    /**
     * Cierra sesión
     */
    public static function logout() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_unset();
        session_destroy();
    }

    /**
     * Obtiene miembros activos o pendientes
     */
    public function obtenerMiembros($activos = true) {
        $estado = $activos ? 'activo' : 'pendiente';
        $sql = "SELECT id, nombre, cedula, telefono, email, carrera, intereses, nivel_experiencia, rol, avatar, fecha_registro, ultimo_acceso 
                FROM usuarios 
                WHERE estado = :estado 
                ORDER BY nombre ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':estado', $estado);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Obtiene todos los usuarios (para admin)
     */
    public function obtenerTodos() {
        $sql = "SELECT * FROM usuarios ORDER BY fecha_registro DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Obtiene un usuario por ID
     */
    public function obtenerPorId($id) {
        $sql = "SELECT * FROM usuarios WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Obtiene un usuario por email
     */
    public function obtenerPorEmail($email) {
        $sql = "SELECT * FROM usuarios WHERE email = :email";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Obtiene un usuario por username
     */
    public function obtenerPorUsername($username) {
        $sql = "SELECT * FROM usuarios WHERE username = :username";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Cambia el estado de un usuario (activo/pendiente/inactivo)
     */
    public function cambiarEstado($id, $estado) {
        $sql = "UPDATE usuarios SET estado = :estado WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':estado', $estado);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    /**
     * Cambia el rol de un usuario (user/admin)
     */
    public function cambiarRol($id, $rol) {
        $sql = "UPDATE usuarios SET rol = :rol WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':rol', $rol);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    /**
     * Actualizar perfil de usuario (versión mejorada con validaciones)
     */
    public function actualizarPerfil($id, $datos) {
        // Validar email único (excepto el propio usuario)
        $sql = "SELECT id FROM usuarios WHERE email = :email AND id != :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':email', $datos['email']);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            return "El email ya está siendo usado por otro usuario.";
        }

        // Validar teléfono si se proporciona
        if (!empty($datos['telefono']) && !preg_match('/^(0412|0414|0424|0416|0426)\d{7}$/', $datos['telefono'])) {
            return "El teléfono debe tener 11 dígitos con prefijo válido (0412, 0414, 0424, 0416, 0426).";
        }

        $sql = "UPDATE usuarios 
                SET nombre = :nombre, telefono = :telefono, email = :email, 
                    carrera = :carrera, intereses = :intereses, nivel_experiencia = :nivel_experiencia
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        
        $intereses = !empty($datos['intereses']) ? implode(',', $datos['intereses']) : '';
        
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':nombre', $datos['nombre']);
        $stmt->bindParam(':telefono', $datos['telefono']);
        $stmt->bindParam(':email', $datos['email']);
        $stmt->bindParam(':carrera', $datos['carrera']);
        $stmt->bindParam(':intereses', $intereses);
        $stmt->bindParam(':nivel_experiencia', $datos['nivel_experiencia']);
        
        if ($stmt->execute()) {
            // Actualizar sesión si es el mismo usuario
            if (self::estaLogueado() && $_SESSION['usuario_id'] == $id) {
                $_SESSION['usuario_nombre'] = $datos['nombre'];
                $_SESSION['usuario_email'] = $datos['email'];
            }
            return true;
        }
        
        return "Error al actualizar perfil.";
    }

    /**
     * Cambiar contraseña
     */
    public function cambiarPassword($id, $password_actual, $password_nueva) {
        // Verificar contraseña actual
        $usuario = $this->obtenerPorId($id);
        
        if (!password_verify($password_actual, $usuario['password'])) {
            return "La contraseña actual es incorrecta.";
        }
        
        // Validar nueva contraseña
        if (strlen($password_nueva) < 8) {
            return "La nueva contraseña debe tener al menos 8 caracteres.";
        }
        if (!preg_match('/[A-Z]/', $password_nueva)) {
            return "La nueva contraseña debe contener al menos una letra mayúscula.";
        }
        if (!preg_match('/[0-9]/', $password_nueva)) {
            return "La nueva contraseña debe contener al menos un número.";
        }
        if (!preg_match('/[^a-zA-Z0-9]/', $password_nueva)) {
            return "La nueva contraseña debe contener al menos un carácter especial.";
        }
        
        $passwordHash = password_hash($password_nueva, PASSWORD_DEFAULT);
        
        $sql = "UPDATE usuarios SET password = :password WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':password', $passwordHash);
        
        return $stmt->execute() ? true : "Error al cambiar contraseña.";
    }

    /**
     * Actualizar avatar
     */
    public function actualizarAvatar($id, $ruta_avatar) {
        $sql = "UPDATE usuarios SET avatar = :avatar WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':avatar', $ruta_avatar);
        
        if ($stmt->execute()) {
            // Actualizar sesión si es el mismo usuario
            if (self::estaLogueado() && $_SESSION['usuario_id'] == $id) {
                $_SESSION['usuario_avatar'] = $ruta_avatar;
            }
            return true;
        }
        return false;
    }

    /**
     * Eliminar avatar (dejar en null)
     */
    public function eliminarAvatar($id) {
        // Obtener avatar actual para borrar archivo
        $usuario = $this->obtenerPorId($id);
        if ($usuario && $usuario['avatar']) {
            $ruta_completa = __DIR__ . '/../public/uploads/avatars/' . $usuario['avatar'];
            if (file_exists($ruta_completa)) {
                unlink($ruta_completa);
            }
        }

        $sql = "UPDATE usuarios SET avatar = NULL WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        
        if ($stmt->execute()) {
            if (self::estaLogueado() && $_SESSION['usuario_id'] == $id) {
                $_SESSION['usuario_avatar'] = null;
            }
            return true;
        }
        return false;
    }

    /**
     * Eliminar usuario (solo admin)
     */
    public function eliminar($id) {
        // No permitir eliminar al admin principal (id=1)
        if ($id == 1) {
            return "No se puede eliminar al administrador principal.";
        }

        $sql = "DELETE FROM usuarios WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    /**
     * Buscar usuarios por término (para admin)
     */
    public function buscar($termino) {
        $sql = "SELECT id, nombre, email, cedula, carrera, rol, estado, avatar 
                FROM usuarios 
                WHERE nombre LIKE :termino 
                   OR email LIKE :termino 
                   OR cedula LIKE :termino
                   OR username LIKE :termino
                ORDER BY nombre
                LIMIT 20";
        $stmt = $this->db->prepare($sql);
        $termino = "%$termino%";
        $stmt->bindParam(':termino', $termino);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Obtener estadísticas de un usuario
     */
    public function obtenerEstadisticas($id) {
        $stats = [];
        
        // Temas creados
        $sql = "SELECT COUNT(*) as total FROM temas_foro WHERE usuario_id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $stats['temas'] = $stmt->fetch()['total'];
        
        // Posts (respuestas)
        $sql = "SELECT COUNT(*) as total FROM posts_foro WHERE usuario_id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $stats['respuestas'] = $stmt->fetch()['total'];
        
        // Proyectos donde participa
        $sql = "SELECT COUNT(*) as total FROM miembros_proyectos WHERE usuario_id = :id AND activo = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $stats['proyectos'] = $stmt->fetch()['total'];
        
        // Eventos donde participa
        $sql = "SELECT COUNT(*) as total FROM asistentes_eventos WHERE usuario_id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $stats['eventos'] = $stmt->fetch()['total'];
        
        return $stats;
    }

    /**
     * Obtener últimos miembros registrados
     */
    public function obtenerUltimos($limite = 10) {
        $sql = "SELECT id, nombre, email, carrera, avatar, fecha_registro 
                FROM usuarios 
                WHERE estado = 'activo'
                ORDER BY fecha_registro DESC 
                LIMIT :limite";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Contar usuarios por estado
     */
    public function contarPorEstado() {
        $sql = "SELECT estado, COUNT(*) as total 
                FROM usuarios 
                GROUP BY estado";
        $stmt = $this->db->query($sql);
        $resultados = [];
        while ($row = $stmt->fetch()) {
            $resultados[$row['estado']] = $row['total'];
        }
        return $resultados;
    }

    /**
     * Verificar si un email ya está registrado
     */
    public function emailExiste($email, $excluir_id = null) {
        $sql = "SELECT id FROM usuarios WHERE email = :email";
        if ($excluir_id) {
            $sql .= " AND id != :excluir_id";
        }
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':email', $email);
        if ($excluir_id) {
            $stmt->bindParam(':excluir_id', $excluir_id);
        }
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    /**
     * Verificar si un username ya está registrado
     */
    public function usernameExiste($username, $excluir_id = null) {
        $sql = "SELECT id FROM usuarios WHERE username = :username";
        if ($excluir_id) {
            $sql .= " AND id != :excluir_id";
        }
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':username', $username);
        if ($excluir_id) {
            $stmt->bindParam(':excluir_id', $excluir_id);
        }
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    /**
     * Generar token para recuperación de contraseña
     */
    public function generarTokenRecuperacion($email) {
        $usuario = $this->obtenerPorEmail($email);
        if (!$usuario) {
            return false;
        }

        $token = bin2hex(random_bytes(32));
        $expiracion = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Guardar token en la base de datos (necesitas crear tabla password_resets)
        $sql = "INSERT INTO password_resets (usuario_id, token, expiracion) 
                VALUES (:usuario_id, :token, :expiracion)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':usuario_id', $usuario['id']);
        $stmt->bindParam(':token', $token);
        $stmt->bindParam(':expiracion', $expiracion);
        
        return $stmt->execute() ? $token : false;
    }

    /**
     * Validar token de recuperación
     */
    public function validarTokenRecuperacion($token) {
        $sql = "SELECT usuario_id FROM password_resets 
                WHERE token = :token AND expiracion > NOW() AND usado = 0";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':token', $token);
        $stmt->execute();
        $result = $stmt->fetch();
        
        return $result ? $result['usuario_id'] : false;
    }

    /**
     * Restablecer contraseña con token
     */
    public function restablecerPassword($token, $nueva_password) {
        $usuario_id = $this->validarTokenRecuperacion($token);
        if (!$usuario_id) {
            return "Token inválido o expirado.";
        }

        // Validar nueva contraseña
        if (strlen($nueva_password) < 8) {
            return "La contraseña debe tener al menos 8 caracteres.";
        }

        $passwordHash = password_hash($nueva_password, PASSWORD_DEFAULT);
        
        // Actualizar contraseña
        $sql = "UPDATE usuarios SET password = :password WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $usuario_id);
        $stmt->bindParam(':password', $passwordHash);
        
        if ($stmt->execute()) {
            // Marcar token como usado
            $sql = "UPDATE password_resets SET usado = 1 WHERE token = :token";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':token', $token);
            $stmt->execute();
            
            return true;
        }
        
        return "Error al restablecer la contraseña.";
    }
    public function obtenerRegistrosPorMes($meses = 6) {
        $sql = "SELECT 
                    DATE_FORMAT(fecha_registro, '%Y-%m') as mes,
                    COUNT(*) as total
                FROM usuarios
                WHERE fecha_registro >= DATE_SUB(NOW(), INTERVAL :meses MONTH)
                GROUP BY mes
                ORDER BY mes ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':meses', $meses, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}