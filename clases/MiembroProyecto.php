<?php
class MiembroProyecto {
    private $db;

    public function __construct() {
        $this->db = Database::obtenerConexion();
    }

    /**
     * Agregar miembro a proyecto
     */
    public function agregar($proyecto_id, $usuario_id, $rol = 'miembro') {
        // Verificar si ya existe
        if ($this->existe($proyecto_id, $usuario_id)) {
            // Si existe pero está inactivo, reactivar
            $sql = "UPDATE miembros_proyectos 
                    SET activo = 1, rol_en_proyecto = :rol, fecha_incorporacion = CURDATE()
                    WHERE proyecto_id = :proyecto_id AND usuario_id = :usuario_id";
        } else {
            // Si no existe, insertar nuevo
            $sql = "INSERT INTO miembros_proyectos (proyecto_id, usuario_id, rol_en_proyecto, fecha_incorporacion, activo) 
                    VALUES (:proyecto_id, :usuario_id, :rol, CURDATE(), 1)";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':proyecto_id', $proyecto_id);
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->bindParam(':rol', $rol);
        
        return $stmt->execute();
    }

    /**
     * Quitar miembro de proyecto (borrado lógico)
     */
    public function quitar($proyecto_id, $usuario_id) {
        $sql = "UPDATE miembros_proyectos SET activo = 0 
                WHERE proyecto_id = :proyecto_id AND usuario_id = :usuario_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':proyecto_id', $proyecto_id);
        $stmt->bindParam(':usuario_id', $usuario_id);
        
        return $stmt->execute();
    }

    /**
     * Verificar si un usuario es miembro de un proyecto
     */
    public function existe($proyecto_id, $usuario_id, $activo = true) {
        $sql = "SELECT id FROM miembros_proyectos 
                WHERE proyecto_id = :proyecto_id AND usuario_id = :usuario_id";
        
        if ($activo) {
            $sql .= " AND activo = 1";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':proyecto_id', $proyecto_id);
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->execute();
        
        return $stmt->fetch() ? true : false;
    }

    /**
     * Obtener todos los miembros de un proyecto
     */
    public function obtenerMiembros($proyecto_id, $solo_activos = true) {
        $sql = "SELECT u.*, mp.rol_en_proyecto, mp.fecha_incorporacion, mp.activo
                FROM miembros_proyectos mp
                JOIN usuarios u ON mp.usuario_id = u.id
                WHERE mp.proyecto_id = :proyecto_id";
        
        if ($solo_activos) {
            $sql .= " AND mp.activo = 1";
        }
        
        $sql .= " ORDER BY mp.fecha_incorporacion";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':proyecto_id', $proyecto_id);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Obtener proyectos de un usuario
     */
    public function obtenerProyectosDeUsuario($usuario_id, $solo_activos = true) {
        $sql = "SELECT p.*, mp.rol_en_proyecto, mp.fecha_incorporacion,
                       (SELECT COUNT(*) FROM miembros_proyectos WHERE proyecto_id = p.id AND activo = 1) as num_miembros
                FROM miembros_proyectos mp
                JOIN proyectos p ON mp.proyecto_id = p.id
                WHERE mp.usuario_id = :usuario_id";
        
        if ($solo_activos) {
            $sql .= " AND mp.activo = 1";
        }
        
        $sql .= " ORDER BY mp.fecha_incorporacion DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Cambiar rol de miembro en proyecto
     */
    public function cambiarRol($proyecto_id, $usuario_id, $nuevo_rol) {
        $sql = "UPDATE miembros_proyectos 
                SET rol_en_proyecto = :rol
                WHERE proyecto_id = :proyecto_id AND usuario_id = :usuario_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':proyecto_id', $proyecto_id);
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->bindParam(':rol', $nuevo_rol);
        
        return $stmt->execute();
    }

    /**
     * Obtener estadísticas de miembros por proyecto
     */
    public function obtenerEstadisticas($proyecto_id) {
        $sql = "SELECT 
                    COUNT(*) as total_miembros,
                    SUM(CASE WHEN activo = 1 THEN 1 ELSE 0 END) as activos,
                    SUM(CASE WHEN rol_en_proyecto = 'lider' THEN 1 ELSE 0 END) as lideres,
                    MIN(fecha_incorporacion) as primer_miembro,
                    MAX(fecha_incorporacion) as ultimo_miembro
                FROM miembros_proyectos
                WHERE proyecto_id = :proyecto_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':proyecto_id', $proyecto_id);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Buscar miembros disponibles para agregar a proyecto
     */
    public function buscarDisponibles($proyecto_id, $termino = '') {
        $sql = "SELECT u.id, u.nombre, u.email, u.carrera, u.intereses
                FROM usuarios u
                WHERE u.estado = 'activo'
                AND u.id NOT IN (
                    SELECT usuario_id FROM miembros_proyectos 
                    WHERE proyecto_id = :proyecto_id AND activo = 1
                )";
        
        if (!empty($termino)) {
            $sql .= " AND (u.nombre LIKE :termino OR u.email LIKE :termino)";
        }
        
        $sql .= " ORDER BY u.nombre LIMIT 20";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':proyecto_id', $proyecto_id);
        
        if (!empty($termino)) {
            $termino = "%$termino%";
            $stmt->bindParam(':termino', $termino);
        }
        
        $stmt->execute();
        return $stmt->fetchAll();
    }
}