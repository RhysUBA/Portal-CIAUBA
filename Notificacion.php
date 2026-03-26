<?php
class Notificacion {
    private $db;

    public function __construct() {
        $this->db = Database::obtenerConexion();
    }

    /**
     * Crear una nueva notificación
     */
    public function crear($usuario_id, $tipo, $titulo, $mensaje, $enlace = null) {
        $sql = "INSERT INTO notificaciones (usuario_id, tipo, titulo, mensaje, enlace) 
                VALUES (:usuario_id, :tipo, :titulo, :mensaje, :enlace)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->bindParam(':tipo', $tipo);
        $stmt->bindParam(':titulo', $titulo);
        $stmt->bindParam(':mensaje', $mensaje);
        $stmt->bindParam(':enlace', $enlace);
        return $stmt->execute();
    }

    /**
     * Obtener notificaciones de un usuario
     */
    public function obtenerPorUsuario($usuario_id, $solo_no_leidas = false, $limite = 50) {
        $sql = "SELECT * FROM notificaciones WHERE usuario_id = :usuario_id";
        if ($solo_no_leidas) {
            $sql .= " AND leida = 0";
        }
        $sql .= " ORDER BY fecha_creacion DESC LIMIT :limite";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Marcar una notificación como leída
     */
    public function marcarLeida($id) {
        $sql = "UPDATE notificaciones SET leida = 1 WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    /**
     * Marcar todas las notificaciones de un usuario como leídas
     */
    public function marcarTodasLeidas($usuario_id) {
        $sql = "UPDATE notificaciones SET leida = 1 WHERE usuario_id = :usuario_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':usuario_id', $usuario_id);
        return $stmt->execute();
    }

    /**
     * Contar notificaciones no leídas de un usuario
     */
    public function contarNoLeidas($usuario_id) {
        $sql = "SELECT COUNT(*) as total FROM notificaciones WHERE usuario_id = :usuario_id AND leida = 0";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['total'];
    }

    /**
     * Eliminar una notificación
     */
    public function eliminar($id) {
        $sql = "DELETE FROM notificaciones WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}