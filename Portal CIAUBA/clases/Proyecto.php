<?php
class Proyecto {
    private $db;

    public function __construct() {
        $this->db = Database::obtenerConexion();
    }

    public function obtenerTodos() {
        $sql = "SELECT p.*, u.nombre as lider_nombre,
                       (SELECT COUNT(*) FROM miembros_proyectos WHERE proyecto_id = p.id) as num_miembros
                FROM proyectos p
                LEFT JOIN usuarios u ON p.lider_id = u.id
                ORDER BY p.creado_en DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function obtenerActivos() {
        $sql = "SELECT p.*, u.nombre as lider_nombre,
                       (SELECT COUNT(*) FROM miembros_proyectos WHERE proyecto_id = p.id) as num_miembros
                FROM proyectos p
                LEFT JOIN usuarios u ON p.lider_id = u.id
                WHERE p.estado IN ('planning', 'in_progress')
                ORDER BY p.fecha_inicio DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function obtenerPorId($id) {
        $sql = "SELECT p.*, u.nombre as lider_nombre
                FROM proyectos p
                LEFT JOIN usuarios u ON p.lider_id = u.id
                WHERE p.id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function crear($datos) {
        $sql = "INSERT INTO proyectos 
                (titulo, descripcion, objetivos, estado, fecha_inicio, fecha_fin_estimada, lider_id, presupuesto_asignado, creado_en) 
                VALUES 
                (:titulo, :descripcion, :objetivos, :estado, :fecha_inicio, :fecha_fin_estimada, :lider_id, :presupuesto_asignado, NOW())";
        
        $stmt = $this->db->prepare($sql);
        
        $stmt->bindParam(':titulo', $datos['titulo']);
        $stmt->bindParam(':descripcion', $datos['descripcion']);
        $stmt->bindParam(':objetivos', $datos['objetivos']);
        $stmt->bindParam(':estado', $datos['estado']);
        $stmt->bindParam(':fecha_inicio', $datos['fecha_inicio']);
        $stmt->bindParam(':fecha_fin_estimada', $datos['fecha_fin_estimada']);
        $stmt->bindParam(':lider_id', $datos['lider_id']);
        $stmt->bindParam(':presupuesto_asignado', $datos['presupuesto_asignado']);
        
        return $stmt->execute();
    }

    public function actualizar($id, $datos) {
        $sql = "UPDATE proyectos 
                SET titulo = :titulo, descripcion = :descripcion, objetivos = :objetivos,
                    estado = :estado, fecha_inicio = :fecha_inicio, 
                    fecha_fin_estimada = :fecha_fin_estimada, lider_id = :lider_id,
                    presupuesto_asignado = :presupuesto_asignado
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':titulo', $datos['titulo']);
        $stmt->bindParam(':descripcion', $datos['descripcion']);
        $stmt->bindParam(':objetivos', $datos['objetivos']);
        $stmt->bindParam(':estado', $datos['estado']);
        $stmt->bindParam(':fecha_inicio', $datos['fecha_inicio']);
        $stmt->bindParam(':fecha_fin_estimada', $datos['fecha_fin_estimada']);
        $stmt->bindParam(':lider_id', $datos['lider_id']);
        $stmt->bindParam(':presupuesto_asignado', $datos['presupuesto_asignado']);
        
        return $stmt->execute();
    }

    public function eliminar($id) {
        $sql = "DELETE FROM proyectos WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function obtenerMiembros($proyecto_id) {
        $sql = "SELECT u.*, mp.rol_en_proyecto, mp.fecha_incorporacion
                FROM miembros_proyectos mp
                JOIN usuarios u ON mp.usuario_id = u.id
                WHERE mp.proyecto_id = :proyecto_id AND mp.activo = 1
                ORDER BY mp.fecha_incorporacion";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':proyecto_id', $proyecto_id);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function agregarMiembro($proyecto_id, $usuario_id, $rol = 'miembro') {
        $sql = "INSERT INTO miembros_proyectos (proyecto_id, usuario_id, rol_en_proyecto, fecha_incorporacion, activo) 
                VALUES (:proyecto_id, :usuario_id, :rol, NOW(), 1)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':proyecto_id', $proyecto_id);
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->bindParam(':rol', $rol);
        
        return $stmt->execute();
    }

    public function quitarMiembro($proyecto_id, $usuario_id) {
        $sql = "UPDATE miembros_proyectos SET activo = 0 
                WHERE proyecto_id = :proyecto_id AND usuario_id = :usuario_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':proyecto_id', $proyecto_id);
        $stmt->bindParam(':usuario_id', $usuario_id);
        
        return $stmt->execute();
    }
}