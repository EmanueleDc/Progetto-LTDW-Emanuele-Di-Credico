<?php

class AuthorDAO {
    private $conn;
    public function __construct($conn) {
        $this->conn = $conn;
    }

    //Tutti
    public function getAll() {
        $res  = $this->conn->query("SELECT * FROM authors ORDER BY name");
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    //Autore per id
    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM authors WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row  = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $row;
    }

    //Crea nuovo autore
    public function create($name, $biography = '') {
        $stmt = $this->conn->prepare(
            "INSERT INTO authors (name, biography) VALUES (?, ?)"
        );
        $stmt->bind_param('ss', $name, $biography);
        $ok = $stmt->execute();
        $id = $ok ? $stmt->insert_id : false;
        $stmt->close();
        return $id;
    }

    //Aggiorna dati autore
    public function update($id, $name, $biography) {
        $stmt = $this->conn->prepare("UPDATE authors SET name = ?, biography = ? WHERE id = ?");
        $stmt->bind_param('ssi', $name, $biography, $id);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    //Elimina autore
    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM authors WHERE id = ?");
        $stmt->bind_param('i', $id);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }
}
