<?php

class CategoryDAO {

    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    /** Tutte le categorie */
    public function getAll() {
        $res  = $this->conn->query("SELECT * FROM categories ORDER BY name");
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    /** Categoria per id */
    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM categories WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row  = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $row;
    }

    /** Crea categoria */
    public function create($name, $description = '') {
        $stmt = $this->conn->prepare(
            "INSERT INTO categories (name, description) VALUES (?, ?)"
        );
        $stmt->bind_param('ss', $name, $description);
        $ok = $stmt->execute();
        $id = $ok ? $stmt->insert_id : false;
        $stmt->close();
        return $id;
    }

    /** Aggiorna categoria */
    public function update($id, $name, $description) {
        $stmt = $this->conn->prepare("UPDATE categories SET name = ?, description = ? WHERE id = ?");
        $stmt->bind_param('ssi', $name, $description, $id);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    /** Elimina categoria */
    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->bind_param('i', $id);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }
}
