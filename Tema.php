<?php
class Tema {
    private $db;

    public function __construct() {
        $this->db = Database::obtenerConexion();
    }

    public function obtenerTodos($categoria_id = null) {
        $sql = "SELECT t.*, u.username, u.nombre, c.nombre as categoria_nombre,
                       (SELECT COUNT(*) FROM posts_foro WHERE tema_id = t.id) as num_respuestas
                FROM temas_foro t
                JOIN usuarios u ON t.usuario_id = u.id
                JOIN categorias_foro c ON t.categoria_id = c.id
                WHERE 1=1";
        
        if ($categoria_id) {
            $sql .= " AND t.categoria_id = :categoria_id";
        }
        
        $sql .= " ORDER BY t.fijo DESC, COALESCE(t.actualizado_en, t.creado_en) DESC";
        
        $stmt = $this->db->prepare($sql);
        if ($categoria_id) {
            $stmt->bindParam(':categoria_id', $categoria_id);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function obtenerTodosPaginado($categoria_id = null, $busqueda = '', $limite = 10, $offset = 0) {
        $sql = "SELECT t.*, u.username, u.nombre, c.nombre as categoria_nombre,
                       (SELECT COUNT(*) FROM posts_foro WHERE tema_id = t.id) as num_respuestas
                FROM temas_foro t
                JOIN usuarios u ON t.usuario_id = u.id
                JOIN categorias_foro c ON t.categoria_id = c.id
                WHERE 1=1";
        $params = [];

        if ($categoria_id) {
            $sql .= " AND t.categoria_id = :categoria_id";
            $params[':categoria_id'] = $categoria_id;
        }
        if (!empty($busqueda)) {
            $sql .= " AND (t.titulo LIKE :busqueda OR t.contenido LIKE :busqueda)";
            $params[':busqueda'] = '%' . $busqueda . '%';
        }

        $sql .= " ORDER BY t.fijo DESC, COALESCE(t.actualizado_en, t.creado_en) DESC LIMIT :limite OFFSET :offset";
        
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => &$val) {
            $stmt->bindParam($key, $val);
        }
        $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function contarTodos($categoria_id = null, $busqueda = '') {
        $sql = "SELECT COUNT(*) as total FROM temas_foro t WHERE 1=1";
        $params = [];

        if ($categoria_id) {
            $sql .= " AND t.categoria_id = :categoria_id";
            $params[':categoria_id'] = $categoria_id;
        }
        if (!empty($busqueda)) {
            $sql .= " AND (t.titulo LIKE :busqueda OR t.contenido LIKE :busqueda)";
            $params[':busqueda'] = '%' . $busqueda . '%';
        }

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => &$val) {
            $stmt->bindParam($key, $val);
        }
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['total'];
    }

    public function obtenerRecientes($limite = 10) {
        $sql = "SELECT t.*, u.username, u.nombre, c.nombre as categoria_nombre,
                       (SELECT COUNT(*) FROM posts_foro WHERE tema_id = t.id) as num_respuestas
                FROM temas_foro t
                JOIN usuarios u ON t.usuario_id = u.id
                JOIN categorias_foro c ON t.categoria_id = c.id
                ORDER BY t.creado_en DESC
                LIMIT :limite";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function obtenerPorId($id) {
        $sql = "SELECT t.*, u.username, u.nombre, c.nombre as categoria_nombre,
                       (SELECT COUNT(*) FROM posts_foro WHERE tema_id = t.id) as num_respuestas
                FROM temas_foro t
                JOIN usuarios u ON t.usuario_id = u.id
                JOIN categorias_foro c ON t.categoria_id = c.id
                WHERE t.id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function crear($titulo, $contenido, $categoria_id, $usuario_id) {
        $sql = "INSERT INTO temas_foro (titulo, contenido, usuario_id, categoria_id) 
                VALUES (:titulo, :contenido, :usuario_id, :categoria_id)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':titulo', $titulo);
        $stmt->bindParam(':contenido', $contenido);
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->bindParam(':categoria_id', $categoria_id);
        return $stmt->execute();
    }

    public function incrementarVisitas($id) {
        $sql = "UPDATE temas_foro SET visitas = visitas + 1 WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function actualizarActividad($id) {
        $sql = "UPDATE temas_foro SET actualizado_en = NOW() WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function actualizar($id, $titulo, $contenido, $categoria_id) {
        $sql = "UPDATE temas_foro 
                SET titulo = :titulo, contenido = :contenido, categoria_id = :categoria_id
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':titulo', $titulo);
        $stmt->bindParam(':contenido', $contenido);
        $stmt->bindParam(':categoria_id', $categoria_id);
        return $stmt->execute();
    }

    public function eliminar($id) {
        $sql = "DELETE FROM temas_foro WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function fijar($id, $fijo = true) {
        $sql = "UPDATE temas_foro SET fijo = :fijo WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':fijo', $fijo, PDO::PARAM_BOOL);
        return $stmt->execute();
    }

    public function cerrar($id, $cerrado = true) {
        $sql = "UPDATE temas_foro SET cerrado = :cerrado WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':cerrado', $cerrado, PDO::PARAM_BOOL);
        return $stmt->execute();
    }

    public function obtenerPorUsuario($usuario_id, $limite = 20) {
        $sql = "SELECT t.*, u.username, u.nombre, c.nombre as categoria_nombre,
                       (SELECT COUNT(*) FROM posts_foro WHERE tema_id = t.id) as num_respuestas
                FROM temas_foro t
                JOIN usuarios u ON t.usuario_id = u.id
                JOIN categorias_foro c ON t.categoria_id = c.id
                WHERE t.usuario_id = :usuario_id
                ORDER BY t.creado_en DESC
                LIMIT :limite";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function obtenerParticipados($usuario_id, $limite = 20) {
        $sql = "SELECT DISTINCT t.*, u.username, u.nombre, c.nombre as categoria_nombre,
                       (SELECT COUNT(*) FROM posts_foro WHERE tema_id = t.id) as num_respuestas
                FROM temas_foro t
                JOIN posts_foro p ON t.id = p.tema_id
                JOIN usuarios u ON t.usuario_id = u.id
                JOIN categorias_foro c ON t.categoria_id = c.id
                WHERE p.usuario_id = :usuario_id
                ORDER BY t.actualizado_en DESC
                LIMIT :limite";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    public function obtenerActividadPorMes($meses = 6) {
        $sql = "SELECT 
                    DATE_FORMAT(t.creado_en, '%Y-%m') as mes,
                    COUNT(DISTINCT t.id) as temas,
                    COUNT(p.id) as posts
                FROM temas_foro t
                LEFT JOIN posts_foro p ON t.id = p.tema_id
                WHERE t.creado_en >= DATE_SUB(NOW(), INTERVAL :meses MONTH)
                GROUP BY mes
                ORDER BY mes ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':meses', $meses, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}