<?php
class AsistenteEvento {
    private $db;

    public function __construct() {
        $this->db = Database::obtenerConexion();
    }

    /**
     * Registrar asistente a evento
     */
    public function registrar($evento_id, $usuario_id) {
        // Verificar si ya está registrado
        if ($this->estaRegistrado($evento_id, $usuario_id)) {
            return "Ya estás registrado en este evento.";
        }

        // Verificar cupo disponible
        $eventoModel = new Evento();
        $evento = $eventoModel->obtenerPorId($evento_id);
        
        if ($evento && $evento['max_asistentes'] > 0) {
            $asistentes_actuales = $this->contarAsistentes($evento_id);
            if ($asistentes_actuales >= $evento['max_asistentes']) {
                return "El evento ha alcanzado el límite de asistentes.";
            }
        }

        $sql = "INSERT INTO asistentes_eventos (evento_id, usuario_id, fecha_registro, asistio) 
                VALUES (:evento_id, :usuario_id, NOW(), 0)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':evento_id', $evento_id);
        $stmt->bindParam(':usuario_id', $usuario_id);
        
        return $stmt->execute() ? true : "Error al registrar asistente.";
    }

    /**
     * Cancelar registro de asistente
     */
    public function cancelar($evento_id, $usuario_id) {
        $sql = "DELETE FROM asistentes_eventos 
                WHERE evento_id = :evento_id AND usuario_id = :usuario_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':evento_id', $evento_id);
        $stmt->bindParam(':usuario_id', $usuario_id);
        
        return $stmt->execute();
    }

    /**
     * Verificar si un usuario está registrado en un evento
     */
    public function estaRegistrado($evento_id, $usuario_id) {
        $sql = "SELECT id FROM asistentes_eventos 
                WHERE evento_id = :evento_id AND usuario_id = :usuario_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':evento_id', $evento_id);
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->execute();
        
        return $stmt->fetch() ? true : false;
    }

    /**
     * Obtener todos los asistentes de un evento
     */
    public function obtenerAsistentes($evento_id, $solo_asistieron = false) {
        $sql = "SELECT u.*, ae.fecha_registro, ae.asistio
                FROM asistentes_eventos ae
                JOIN usuarios u ON ae.usuario_id = u.id
                WHERE ae.evento_id = :evento_id";
        
        if ($solo_asistieron) {
            $sql .= " AND ae.asistio = 1";
        }
        
        $sql .= " ORDER BY ae.fecha_registro";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':evento_id', $evento_id);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Obtener eventos de un usuario
     */
    public function obtenerEventosDeUsuario($usuario_id, $proximos = true) {
        $sql = "SELECT e.*, ae.fecha_registro, ae.asistio,
                       (SELECT COUNT(*) FROM asistentes_eventos WHERE evento_id = e.id) as total_asistentes
                FROM asistentes_eventos ae
                JOIN eventos e ON ae.evento_id = e.id
                WHERE ae.usuario_id = :usuario_id";
        
        if ($proximos) {
            $sql .= " AND e.fecha_inicio >= NOW()";
        } else {
            $sql .= " AND e.fecha_inicio < NOW()";
        }
        
        $sql .= " ORDER BY e.fecha_inicio";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Marcar asistencia de un usuario a evento
     */
    public function marcarAsistencia($evento_id, $usuario_id, $asistio = true) {
        $sql = "UPDATE asistentes_eventos SET asistio = :asistio 
                WHERE evento_id = :evento_id AND usuario_id = :usuario_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':evento_id', $evento_id);
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->bindParam(':asistio', $asistio, PDO::PARAM_BOOL);
        
        return $stmt->execute();
    }

    /**
     * Marcar asistencia múltiple (para pasar lista)
     */
    public function marcarAsistenciaMultiple($evento_id, $asistentes) {
        $this->db->beginTransaction();
        
        try {
            foreach ($asistentes as $usuario_id => $asistio) {
                $this->marcarAsistencia($evento_id, $usuario_id, $asistio);
            }
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    /**
     * Contar asistentes a un evento
     */
    public function contarAsistentes($evento_id, $confirmados = false) {
        $sql = "SELECT COUNT(*) as total FROM asistentes_eventos 
                WHERE evento_id = :evento_id";
        
        if ($confirmados) {
            $sql .= " AND asistio = 1";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':evento_id', $evento_id);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['total'];
    }

    /**
     * Obtener estadísticas de asistencia por evento
     */
    public function obtenerEstadisticas($evento_id) {
        $sql = "SELECT 
                    COUNT(*) as total_registrados,
                    SUM(CASE WHEN asistio = 1 THEN 1 ELSE 0 END) as total_asistieron,
                    SUM(CASE WHEN asistio = 0 THEN 1 ELSE 0 END) as total_no_asistieron,
                    MIN(fecha_registro) as primer_registro,
                    MAX(fecha_registro) as ultimo_registro
                FROM asistentes_eventos
                WHERE evento_id = :evento_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':evento_id', $evento_id);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Verificar si hay cupo disponible
     */
    public function hayCupo($evento_id) {
        $eventoModel = new Evento();
        $evento = $eventoModel->obtenerPorId($evento_id);
        
        if (!$evento || $evento['max_asistentes'] == 0) {
            return true; // Ilimitado
        }
        
        $actual = $this->contarAsistentes($evento_id);
        return $actual < $evento['max_asistentes'];
    }
}