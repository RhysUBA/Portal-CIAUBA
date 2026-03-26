<?php
class Post {
    private $db;
    private $temaModel;

    public function __construct() {
        $this->db = Database::obtenerConexion();
        $this->temaModel = new Tema();
    }

    public function obtenerPorTema($tema_id) {
        $sql = "SELECT p.*, u.username, u.nombre, u.rol,
                       (SELECT COUNT(*) FROM posts_likes WHERE post_id = p.id) as total_likes
                FROM posts_foro p
                JOIN usuarios u ON p.usuario_id = u.id
                WHERE p.tema_id = :tema_id
                ORDER BY p.creado_en ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':tema_id', $tema_id);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Nuevo método para respuestas paginadas
    public function obtenerRespuestasPaginadas($tema_id, $limite, $offset) {
        $sql = "SELECT p.*, u.username, u.nombre, u.rol,
                       (SELECT COUNT(*) FROM posts_likes WHERE post_id = p.id) as total_likes
                FROM posts_foro p
                JOIN usuarios u ON p.usuario_id = u.id
                WHERE p.tema_id = :tema_id
                ORDER BY p.creado_en ASC
                LIMIT :limite OFFSET :offset";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':tema_id', $tema_id);
        $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function contarRespuestasPorTema($tema_id) {
        $sql = "SELECT COUNT(*) as total FROM posts_foro WHERE tema_id = :tema_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':tema_id', $tema_id);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['total'];
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
        $sql = "SELECT p.*, u.username, u.nombre, u.rol, u.id as usuario_id,
                       (SELECT COUNT(*) FROM posts_likes WHERE post_id = p.id) as total_likes
                FROM posts_foro p
                JOIN usuarios u ON p.usuario_id = u.id
                WHERE p.id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function editar($id, $contenido, $usuario_id) {
        $post = $this->obtenerPorId($id);
        if (!$post) return false;

        if ($post['usuario_id'] != $usuario_id && !User::esAdmin()) return false;

        $sql = "UPDATE posts_foro 
                SET contenido = :contenido, editado = 1, editado_en = NOW()
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':contenido', $contenido);
        
        $resultado = $stmt->execute();
        if ($resultado) $this->temaModel->actualizarActividad($post['tema_id']);
        return $resultado;
    }

    public function eliminar($id, $usuario_id) {
        $post = $this->obtenerPorId($id);
        if (!$post) return false;

        if ($post['usuario_id'] != $usuario_id && !User::esAdmin()) return false;

        $temaModel = new Tema();
        $tema = $temaModel->obtenerPorId($post['tema_id']);
        if ($tema && $tema['contenido'] == $post['contenido']) {
            return "No puedes eliminar el primer post del tema. Elimina el tema completo.";
        }

        $sql = "DELETE FROM posts_foro WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        
        $resultado = $stmt->execute();
        if ($resultado) $this->temaModel->actualizarActividad($post['tema_id']);
        return $resultado;
    }

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

    public function contarPorUsuario($usuario_id) {
        $sql = "SELECT COUNT(*) as total FROM posts_foro WHERE usuario_id = :usuario_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['total'];
    }

    // ----- MÉTODOS PARA LIKES -----
    public function darLike($post_id, $usuario_id) {
        if ($this->yaDioLike($post_id, $usuario_id)) {
            return $this->quitarLike($post_id, $usuario_id);
        } else {
            $sql = "INSERT INTO posts_likes (post_id, usuario_id) VALUES (:post_id, :usuario_id)";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':post_id', $post_id);
            $stmt->bindParam(':usuario_id', $usuario_id);
            return $stmt->execute();
        }
    }

    public function yaDioLike($post_id, $usuario_id) {
        $sql = "SELECT COUNT(*) as total FROM posts_likes WHERE post_id = :post_id AND usuario_id = :usuario_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':post_id', $post_id);
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['total'] > 0;
    }

    public function quitarLike($post_id, $usuario_id) {
        $sql = "DELETE FROM posts_likes WHERE post_id = :post_id AND usuario_id = :usuario_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':post_id', $post_id);
        $stmt->bindParam(':usuario_id', $usuario_id);
        return $stmt->execute();
    }

    public function contarLikes($post_id) {
        $sql = "SELECT COUNT(*) as total FROM posts_likes WHERE post_id = :post_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':post_id', $post_id);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['total'];
    }
}