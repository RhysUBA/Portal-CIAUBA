<?php
class Post {
    private $db;
    private $temaModel;

    public function __construct() {
        $this->db = Database::obtenerConexion();
        $this->temaModel = new Tema();
    }

    public function obtenerPorTema($tema_id) {
        $sql = "SELECT p.*, u.username, u.nombre, u.rol
                FROM posts_foro p
                JOIN usuarios u ON p.usuario_id = u.id
                WHERE p.tema_id = :tema_id
                ORDER BY p.creado_en ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':tema_id', $tema_id);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function crear($tema_id, $usuario_id, $contenido, $es_respuesta_a = null) {
        $this->db->beginTransaction();
        
        try {
            $sql = "INSERT INTO posts_foro (tema_id, usuario_id, contenido, es_respuesta_a, creado_en) 
                    VALUES (:tema_id, :usuario_id, :contenido, :es_respuesta_a, NOW())";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':tema_id', $tema_id);
            $stmt->bindParam(':usuario_id', $usuario_id);
            $stmt->bindParam(':contenido', $contenido);
            $stmt->bindParam(':es_respuesta_a', $es_respuesta_a);
            $stmt->execute();
            
            $this->temaModel->actualizarActividad($tema_id);
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function obtenerPorId($id) {
        $sql = "SELECT p.*, u.username, u.nombre, u.rol, u.id as usuario_id
                FROM posts_foro p
                JOIN usuarios u ON p.usuario_id = u.id
                WHERE p.id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * NUEVO: Editar un post (solo el autor o admin)
     */
    public function editar($id, $contenido, $usuario_id) {
        // Verificar permisos
        $post = $this->obtenerPorId($id);
        if (!$post) {
            return false;
        }

        // Solo el autor o un admin pueden editar
        if ($post['usuario_id'] != $usuario_id && !User::esAdmin()) {
            return false;
        }

        $sql = "UPDATE posts_foro 
                SET contenido = :contenido, editado = 1, editado_en = NOW()
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':contenido', $contenido);
        
        $resultado = $stmt->execute();
        
        // Actualizar actividad del tema si se editó
        if ($resultado) {
            $this->temaModel->actualizarActividad($post['tema_id']);
        }
        
        return $resultado;
    }

    /**
     * NUEVO: Eliminar un post (solo admin o autor)
     */
    public function eliminar($id, $usuario_id) {
        // Verificar permisos
        $post = $this->obtenerPorId($id);
        if (!$post) {
            return false;
        }

        // Solo el autor o un admin pueden eliminar
        if ($post['usuario_id'] != $usuario_id && !User::esAdmin()) {
            return false;
        }

        // No permitir eliminar el primer post de un tema (el tema en sí)
        $temaModel = new Tema();
        $tema = $temaModel->obtenerPorId($post['tema_id']);
        
        if ($tema && $tema['contenido'] == $post['contenido']) {
            return "No puedes eliminar el primer post del tema. Elimina el tema completo.";
        }

        $sql = "DELETE FROM posts_foro WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        
        $resultado = $stmt->execute();
        
        // Actualizar actividad del tema
        if ($resultado) {
            $this->temaModel->actualizarActividad($post['tema_id']);
        }
        
        return $resultado;
    }

    /**
     * NUEVO: Obtener respuestas a un post específico
     */
    public function obtenerRespuestas($post_id) {
        $sql = "SELECT p.*, u.username, u.nombre
                FROM posts_foro p
                JOIN usuarios u ON p.usuario_id = u.id
                WHERE p.es_respuesta_a = :post_id
                ORDER BY p.creado_en ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':post_id', $post_id);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * NUEVO: Contar posts de un usuario
     */
    public function contarPorUsuario($usuario_id) {
        $sql = "SELECT COUNT(*) as total FROM posts_foro WHERE usuario_id = :usuario_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['total'];
    }
}