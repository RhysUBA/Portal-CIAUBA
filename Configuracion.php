<?php
class Configuracion {
    private $db;

    public function __construct() {
        $this->db = Database::obtenerConexion();
    }

    public function obtenerTodas() {
        $sql = "SELECT clave, valor FROM configuracion";
        $stmt = $this->db->query($sql);
        $result = [];
        while ($row = $stmt->fetch()) {
            $result[$row['clave']] = $row['valor'];
        }
        return $result;
    }

    public function obtener($clave) {
        $sql = "SELECT valor FROM configuracion WHERE clave = :clave";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':clave', $clave);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result ? $result['valor'] : null;
    }

    public function guardar($clave, $valor) {
        // Verificar si existe
        $sql = "SELECT id FROM configuracion WHERE clave = :clave";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':clave', $clave);
        $stmt->execute();
        
        if ($stmt->fetch()) {
            // Actualizar
            $sql = "UPDATE configuracion SET valor = :valor WHERE clave = :clave";
        } else {
            // Insertar
            $sql = "INSERT INTO configuracion (clave, valor) VALUES (:clave, :valor)";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':clave', $clave);
        $stmt->bindParam(':valor', $valor);
        return $stmt->execute();
    }

    public function guardarMultiples($configuraciones) {
        $this->db->beginTransaction();
        
        try {
            foreach ($configuraciones as $clave => $valor) {
                $this->guardar($clave, $valor);
            }
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function eliminar($clave) {
        $sql = "DELETE FROM configuracion WHERE clave = :clave";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':clave', $clave);
        return $stmt->execute();
    }
}