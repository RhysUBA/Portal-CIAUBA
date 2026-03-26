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
    public function obtenerAsistenciaUltimosEventos($limite = 5) {
        $sql = "SELECT 
                    e.titulo,
                    e.fecha_inicio,
                    COUNT(ae.usuario_id) as asistentes,
                    e.max_asistentes
                FROM eventos e
                LEFT JOIN asistentes_eventos ae ON e.id = ae.evento_id
                WHERE e.fecha_inicio <= NOW()
                GROUP BY e.id
                ORDER BY e.fecha_inicio DESC
                LIMIT :limite";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    public function obtenerConteoPorEstado() {
        $sql = "SELECT estado, COUNT(*) as total FROM proyectos GROUP BY estado";
        $stmt = $this->db->query($sql);
        $result = [];
        while ($row = $stmt->fetch()) {
            $result[$row['estado']] = $row['total'];
        }
        return $result;
    }
    public function solicitudPendiente($proyecto_id, $usuario_id) {
    $sql = "SELECT id FROM miembros_proyectos 
            WHERE proyecto_id = :proyecto_id AND usuario_id = :usuario_id AND activo = 0";
    $stmt = $this->db->prepare($sql);
    $stmt->bindParam(':proyecto_id', $proyecto_id);
    $stmt->bindParam(':usuario_id', $usuario_id);
    $stmt->execute();
    return $stmt->fetch() ? true : false;
}

/**
 * Envía una solicitud para unirse al proyecto
 */
public function solicitar($proyecto_id, $usuario_id) {
    // Verificar si ya es miembro activo
    if ($this->esMiembro($proyecto_id, $usuario_id)) {
        return "Ya eres miembro de este proyecto.";
    }
    // Verificar si ya tiene solicitud pendiente
    if ($this->solicitudPendiente($proyecto_id, $usuario_id)) {
        return "Ya has solicitado unirte a este proyecto. Espera la respuesta del líder.";
    }
    // Insertar solicitud
    $sql = "INSERT INTO miembros_proyectos (proyecto_id, usuario_id, rol_en_proyecto, fecha_solicitud, activo) 
            VALUES (:proyecto_id, :usuario_id, 'solicitante', NOW(), 0)";
    $stmt = $this->db->prepare($sql);
    $stmt->bindParam(':proyecto_id', $proyecto_id);
    $stmt->bindParam(':usuario_id', $usuario_id);
    return $stmt->execute() ? true : "Error al enviar la solicitud.";
}

    public function obtenerSolicitudes($proyecto_id) {
        $sql = "SELECT u.*, mp.fecha_solicitud
                FROM miembros_proyectos mp
                JOIN usuarios u ON mp.usuario_id = u.id
                WHERE mp.proyecto_id = :proyecto_id AND mp.activo = 0
                ORDER BY mp.fecha_solicitud ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':proyecto_id', $proyecto_id);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function aprobarSolicitud($proyecto_id, $usuario_id, $rol = 'miembro') {
        $sql = "UPDATE miembros_proyectos 
                SET activo = 1, rol_en_proyecto = :rol, fecha_incorporacion = NOW()
                WHERE proyecto_id = :proyecto_id AND usuario_id = :usuario_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':proyecto_id', $proyecto_id);
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->bindParam(':rol', $rol);
        return $stmt->execute();
    }

    public function rechazarSolicitud($proyecto_id, $usuario_id) {
        $sql = "DELETE FROM miembros_proyectos 
                WHERE proyecto_id = :proyecto_id AND usuario_id = :usuario_id AND activo = 0";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':proyecto_id', $proyecto_id);
        $stmt->bindParam(':usuario_id', $usuario_id);
        return $stmt->execute();
    }

    public function esMiembro($proyecto_id, $usuario_id) {
        $sql = "SELECT id FROM miembros_proyectos 
                WHERE proyecto_id = :proyecto_id AND usuario_id = :usuario_id AND activo = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':proyecto_id', $proyecto_id);
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->execute();
        return $stmt->fetch() ? true : false;
    }
}