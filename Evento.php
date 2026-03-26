<?php
class Evento {
    private $db;

    public function __construct() {
        $this->db = Database::obtenerConexion();
    }

    public function obtenerProximos($limite = 10) {
        $sql = "SELECT e.*, u.nombre as organizador_nombre,
                       (SELECT COUNT(*) FROM asistentes_eventos WHERE evento_id = e.id) as asistentes_count
                FROM eventos e
                LEFT JOIN usuarios u ON e.organizador_id = u.id
                WHERE e.fecha_inicio >= NOW()
                ORDER BY e.fecha_inicio ASC
                LIMIT :limite";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function obtenerPasados($limite = 10) {
        $sql = "SELECT e.*, u.nombre as organizador_nombre,
                       (SELECT COUNT(*) FROM asistentes_eventos WHERE evento_id = e.id) as asistentes_count
                FROM eventos e
                LEFT JOIN usuarios u ON e.organizador_id = u.id
                WHERE e.fecha_inicio < NOW()
                ORDER BY e.fecha_inicio DESC
                LIMIT :limite";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function obtenerTodos() {
        $sql = "SELECT e.*, u.nombre as organizador_nombre,
                       (SELECT COUNT(*) FROM asistentes_eventos WHERE evento_id = e.id) as asistentes_count
                FROM eventos e
                LEFT JOIN usuarios u ON e.organizador_id = u.id
                ORDER BY e.fecha_inicio DESC";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function obtenerPorId($id) {
        $sql = "SELECT e.*, u.nombre as organizador_nombre
                FROM eventos e
                LEFT JOIN usuarios u ON e.organizador_id = u.id
                WHERE e.id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function crear($datos) {
        $sql = "INSERT INTO eventos 
                (titulo, descripcion, tipo, lugar, fecha_inicio, fecha_fin, organizador_id, max_asistentes, creado_en) 
                VALUES 
                (:titulo, :descripcion, :tipo, :lugar, :fecha_inicio, :fecha_fin, :organizador_id, :max_asistentes, NOW())";
        
        $stmt = $this->db->prepare($sql);
        
        $stmt->bindParam(':titulo', $datos['titulo']);
        $stmt->bindParam(':descripcion', $datos['descripcion']);
        $stmt->bindParam(':tipo', $datos['tipo']);
        $stmt->bindParam(':lugar', $datos['lugar']);
        $stmt->bindParam(':fecha_inicio', $datos['fecha_inicio']);
        $stmt->bindParam(':fecha_fin', $datos['fecha_fin']);
        $stmt->bindParam(':organizador_id', $datos['organizador_id']);
        $stmt->bindParam(':max_asistentes', $datos['max_asistentes']);
        
        return $stmt->execute();
    }

    public function actualizar($id, $datos) {
        $sql = "UPDATE eventos 
                SET titulo = :titulo, descripcion = :descripcion, tipo = :tipo,
                    lugar = :lugar, fecha_inicio = :fecha_inicio, fecha_fin = :fecha_fin,
                    max_asistentes = :max_asistentes
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':titulo', $datos['titulo']);
        $stmt->bindParam(':descripcion', $datos['descripcion']);
        $stmt->bindParam(':tipo', $datos['tipo']);
        $stmt->bindParam(':lugar', $datos['lugar']);
        $stmt->bindParam(':fecha_inicio', $datos['fecha_inicio']);
        $stmt->bindParam(':fecha_fin', $datos['fecha_fin']);
        $stmt->bindParam(':max_asistentes', $datos['max_asistentes']);
        
        return $stmt->execute();
    }

    public function eliminar($id) {
        $sql = "DELETE FROM eventos WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function obtenerAsistentes($evento_id) {
        $sql = "SELECT u.*, ae.fecha_registro, ae.asistio
                FROM asistentes_eventos ae
                JOIN usuarios u ON ae.usuario_id = u.id
                WHERE ae.evento_id = :evento_id
                ORDER BY ae.fecha_registro";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':evento_id', $evento_id);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function registrarAsistente($evento_id, $usuario_id) {
        $sql = "INSERT INTO asistentes_eventos (evento_id, usuario_id, fecha_registro) 
                VALUES (:evento_id, :usuario_id, NOW())";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':evento_id', $evento_id);
        $stmt->bindParam(':usuario_id', $usuario_id);
        
        return $stmt->execute();
    }

    public function quitarAsistente($evento_id, $usuario_id) {
        $sql = "DELETE FROM asistentes_eventos WHERE evento_id = :evento_id AND usuario_id = :usuario_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':evento_id', $evento_id);
        $stmt->bindParam(':usuario_id', $usuario_id);
        
        return $stmt->execute();
    }

    public function marcarAsistencia($evento_id, $usuario_id, $asistio = true) {
        $sql = "UPDATE asistentes_eventos SET asistio = :asistio 
                WHERE evento_id = :evento_id AND usuario_id = :usuario_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':evento_id', $evento_id);
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->bindParam(':asistio', $asistio, PDO::PARAM_BOOL);
        
        return $stmt->execute();
    }
    public function obtenerAsistenciaUltimosEventos($limite = 5) {
        $sql = "SELECT e.titulo, e.fecha_inicio, COUNT(ae.usuario_id) as asistentes, e.max_asistentes FROM eventos e LEFT JOIN asistentes_eventos ae ON e.id = ae.evento_id WHERE e.fecha_inicio <= NOW() GROUP BY e.id ORDER BY e.fecha_inicio DESC LIMIT :limite";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}