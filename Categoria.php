<?php
class Categoria {
    private $db;

    public function __construct() {
        $this->db = Database::obtenerConexion();
    }

    public function obtenerTodas() {
        $sql = "SELECT * FROM categorias_foro ORDER BY posicion";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function obtenerActivas() {
        $sql = "SELECT * FROM categorias_foro WHERE activa = 1 ORDER BY posicion";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function obtenerPorId($id) {
        $sql = "SELECT * FROM categorias_foro WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function crear($nombre, $descripcion = '', $posicion = 0) {
        $sql = "INSERT INTO categorias_foro (nombre, descripcion, posicion, activa) 
                VALUES (:nombre, :descripcion, :posicion, 1)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':descripcion', $descripcion);
        $stmt->bindParam(':posicion', $posicion);
        return $stmt->execute();
    }

    public function actualizar($id, $nombre, $descripcion = '', $posicion = 0, $activa = true) {
        $sql = "UPDATE categorias_foro 
                SET nombre = :nombre, descripcion = :descripcion, posicion = :posicion, activa = :activa 
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':descripcion', $descripcion);
        $stmt->bindParam(':posicion', $posicion);
        $stmt->bindParam(':activa', $activa, PDO::PARAM_BOOL);
        return $stmt->execute();
    }

    public function eliminar($id) {
        $sql = "DELETE FROM categorias_foro WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function obtenerEstadisticas($id) {
        $sql = "SELECT 
                    COUNT(DISTINCT t.id) as total_temas,
                    COUNT(p.id) as total_posts,
                    MAX(t.creado_en) as ultimo_tema
                FROM categorias_foro c
                LEFT JOIN temas_foro t ON c.id = t.categoria_id
                LEFT JOIN posts_foro p ON t.id = p.tema_id
                WHERE c.id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }
}