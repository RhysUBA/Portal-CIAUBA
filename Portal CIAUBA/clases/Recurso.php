<?php
class Recurso {
    private $db;

    public function __construct() {
        $this->db = Database::obtenerConexion();
    }

    /**
     * Obtener todos los recursos con filtros opcionales
     */
    public function obtenerTodos($tipo = null, $proyecto_id = null, $usuario_id = null, $limite = 50) {
        $sql = "SELECT r.*, u.nombre as usuario_nombre, u.username,
                       p.titulo as proyecto_titulo
                FROM recursos r
                JOIN usuarios u ON r.usuario_id = u.id
                LEFT JOIN proyectos p ON r.proyecto_id = p.id
                WHERE 1=1";
        
        $params = [];

        if ($tipo) {
            $sql .= " AND r.tipo = :tipo";
            $params[':tipo'] = $tipo;
        }

        if ($proyecto_id) {
            $sql .= " AND r.proyecto_id = :proyecto_id";
            $params[':proyecto_id'] = $proyecto_id;
        }

        if ($usuario_id) {
            $sql .= " AND r.usuario_id = :usuario_id";
            $params[':usuario_id'] = $usuario_id;
        }

        $sql .= " ORDER BY r.fecha_subida DESC LIMIT :limite";
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $key => &$val) {
            $stmt->bindParam($key, $val);
        }
        $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
        
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Obtener recurso por ID
     */
    public function obtenerPorId($id) {
        $sql = "SELECT r.*, u.nombre as usuario_nombre, u.email,
                       p.titulo as proyecto_titulo
                FROM recursos r
                JOIN usuarios u ON r.usuario_id = u.id
                LEFT JOIN proyectos p ON r.proyecto_id = p.id
                WHERE r.id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Crear nuevo recurso (enlace)
     */
    public function crearEnlace($titulo, $descripcion, $url, $usuario_id, $proyecto_id = null) {
        $tipo = 'enlace';
        $sql = "INSERT INTO recursos (titulo, descripcion, tipo, url, usuario_id, proyecto_id, fecha_subida) 
                VALUES (:titulo, :descripcion, :tipo, :url, :usuario_id, :proyecto_id, NOW())";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':titulo', $titulo);
        $stmt->bindParam(':descripcion', $descripcion);
        $stmt->bindParam(':tipo', $tipo);
        $stmt->bindParam(':url', $url);
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->bindParam(':proyecto_id', $proyecto_id);
        
        return $stmt->execute();
    }

    /**
     * Crear nuevo recurso (archivo)
     */
    public function crearArchivo($titulo, $descripcion, $archivo_ruta, $usuario_id, $proyecto_id = null) {
        $tipo = 'archivo';
        $sql = "INSERT INTO recursos (titulo, descripcion, tipo, archivo_ruta, usuario_id, proyecto_id, fecha_subida) 
                VALUES (:titulo, :descripcion, :tipo, :archivo_ruta, :usuario_id, :proyecto_id, NOW())";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':titulo', $titulo);
        $stmt->bindParam(':descripcion', $descripcion);
        $stmt->bindParam(':tipo', $tipo);
        $stmt->bindParam(':archivo_ruta', $archivo_ruta);
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->bindParam(':proyecto_id', $proyecto_id);
        
        return $stmt->execute();
    }

    /**
     * Crear nuevo recurso (video)
     */
    public function crearVideo($titulo, $descripcion, $url, $usuario_id, $proyecto_id = null) {
        $tipo = 'video';
        $sql = "INSERT INTO recursos (titulo, descripcion, tipo, url, usuario_id, proyecto_id, fecha_subida) 
                VALUES (:titulo, :descripcion, :tipo, :url, :usuario_id, :proyecto_id, NOW())";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':titulo', $titulo);
        $stmt->bindParam(':descripcion', $descripcion);
        $stmt->bindParam(':tipo', $tipo);
        $stmt->bindParam(':url', $url);
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->bindParam(':proyecto_id', $proyecto_id);
        
        return $stmt->execute();
    }

    /**
     * Actualizar recurso
     */
    public function actualizar($id, $titulo, $descripcion) {
        $sql = "UPDATE recursos 
                SET titulo = :titulo, descripcion = :descripcion
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':titulo', $titulo);
        $stmt->bindParam(':descripcion', $descripcion);
        
        return $stmt->execute();
    }

    /**
     * Eliminar recurso
     */
    public function eliminar($id) {
        // Primero obtener la información del archivo para borrarlo físicamente
        $recurso = $this->obtenerPorId($id);
        
        if ($recurso && $recurso['tipo'] == 'archivo' && $recurso['archivo_ruta']) {
            $ruta_completa = __DIR__ . '/../uploads/' . $recurso['archivo_ruta'];
            if (file_exists($ruta_completa)) {
                unlink($ruta_completa);
            }
        }
        
        $sql = "DELETE FROM recursos WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    /**
     * Obtener recursos por tipo
     */
    public function obtenerPorTipo($tipo, $limite = 20) {
        return $this->obtenerTodos($tipo, null, null, $limite);
    }

    /**
     * Obtener recursos de un proyecto
     */
    public function obtenerPorProyecto($proyecto_id, $tipo = null) {
        return $this->obtenerTodos($tipo, $proyecto_id, null, 100);
    }

    /**
     * Obtener recursos subidos por un usuario
     */
    public function obtenerPorUsuario($usuario_id, $tipo = null) {
        return $this->obtenerTodos($tipo, null, $usuario_id, 50);
    }

    /**
     * Contar recursos por tipo
     */
    public function contarPorTipo() {
        $sql = "SELECT tipo, COUNT(*) as total 
                FROM recursos 
                GROUP BY tipo";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
}