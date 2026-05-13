<?php

class UserDAO {

    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    //Cerca per username
    public function findByUsername($username) {
        $stmt = $this->conn->prepare("
            SELECT u.*,
                   IF(MAX(g.name = 'admin'), 1, 0) AS is_admin
            FROM users u
            LEFT JOIN users_has_groups ug ON ug.user_id = u.id
            LEFT JOIN groups g ON g.id = ug.group_id
            WHERE u.username = ?
            GROUP BY u.id
        ");
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $res  = $stmt->get_result();
        $row  = $res->fetch_assoc();
        $stmt->close();
        return $row;
    }

    //Cerca per email
    public function findByEmail($email) {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $row  = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $row;
    }

    //Cerca per ID
    public function findById($id) {
        $stmt = $this->conn->prepare("
            SELECT u.*,
                   IF(MAX(g.name = 'admin'), 1, 0) AS is_admin
            FROM users u
            LEFT JOIN users_has_groups ug ON ug.user_id = u.id
            LEFT JOIN groups g ON g.id = ug.group_id
            WHERE u.id = ?
            GROUP BY u.id
        ");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row  = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $row;
    }

    //Create utente
    public function create($username, $email, $password) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->conn->prepare(
            "INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)"
        );
        $stmt->bind_param('sss', $username, $email, $hash);
        $ok = $stmt->execute();
        $id = $ok ? $stmt->insert_id : false;
        $stmt->close();
        return $id;
    }

    //Elenco utenti
    public function getAll() {
        $res = $this->conn->query("
            SELECT u.*, 
                   IF(MAX(g.name = 'admin'), 1, 0) AS is_admin
            FROM users u
            LEFT JOIN users_has_groups ug ON ug.user_id = u.id
            LEFT JOIN groups g ON g.id = ug.group_id
            GROUP BY u.id
            ORDER BY u.username
        ");
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    //Elimina
    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param('i', $id);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    //Update email
    public function update($id, $email) {
        $stmt = $this->conn->prepare("UPDATE users SET email = ? WHERE id = ?");
        $stmt->bind_param('si', $email, $id);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    //Cambia ruolo (admin/user)
    public function setRole($userId, $roleName) {
        //Rimuove tutti i gruppi attuali
        $this->conn->query("DELETE FROM users_has_groups WHERE user_id = $userId");
        //Trova ID del nuovo gruppo
        $stmt = $this->conn->prepare("SELECT id FROM groups WHERE name = ?");
        $stmt->bind_param('s', $roleName);
        $stmt->execute();
        $groupId = $stmt->get_result()->fetch_assoc()['id'] ?? null;
        $stmt->close();
        if ($groupId) {
            $stmt = $this->conn->prepare("INSERT INTO users_has_groups (user_id, group_id) VALUES (?, ?)");
            $stmt->bind_param('ii', $userId, $groupId);
            $stmt->execute();
            $stmt->close();
            return true;
        }
        return false;
    }

    //Indirizzo principale
    public function getAddress($userId) {
        $stmt = $this->conn->prepare("
            SELECT a.* FROM addresses a
            JOIN users_has_addresses ua ON ua.address_id = a.id
            WHERE ua.user_id = ?
            LIMIT 1
        ");
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    //Salva profilo e indirizzo
    public function updateProfile($id, $username, $email, $password = null, $addressData = null) {
        $this->conn->begin_transaction();
        try {
            //Aggiorna dati base
            if ($password) {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $this->conn->prepare("UPDATE users SET username = ?, email = ?, password_hash = ? WHERE id = ?");
                $stmt->bind_param('sssi', $username, $email, $hash, $id);
            } else {
                $stmt = $this->conn->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
                $stmt->bind_param('ssi', $username, $email, $id);
            }
            $stmt->execute();

            //Aggiorna indirizzo se fornito
            if ($addressData) {
                $current = $this->getAddress($id);
                if ($current) {
                    $stmt = $this->conn->prepare("UPDATE addresses SET street = ?, city = ?, zip_code = ?, country = ? WHERE id = ?");
                    $stmt->bind_param('ssssi', $addressData['street'], $addressData['city'], $addressData['zip_code'], $addressData['country'], $current['id']);
                } else {
                    $stmt = $this->conn->prepare("INSERT INTO addresses (street, city, zip_code, country) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param('ssss', $addressData['street'], $addressData['city'], $addressData['zip_code'], $addressData['country']);
                    $stmt->execute();
                    $addrId = $stmt->insert_id;
                    
                    $stmt = $this->conn->prepare("INSERT INTO users_has_addresses (user_id, address_id) VALUES (?, ?)");
                    $stmt->bind_param('ii', $id, $addrId);
                }
                $stmt->execute();
            }

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            return false;
        }
    }

    //Lista permessi/servizi
    public function getServices($userId) {
        $stmt = $this->conn->prepare("
            SELECT DISTINCT s.username
            FROM services s
            JOIN services_has_groups sg ON sg.service_username = s.username
            JOIN users_has_groups ug ON ug.group_id = sg.group_id
            WHERE ug.user_id = ?
        ");
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $res = $stmt->get_result();
        $services = [];
        while ($row = $res->fetch_assoc()) {
            $services[] = $row['username'];
        }
        $stmt->close();
        return $services;
    }

    //Conteggio totale
    public function countAll() {
        $res = $this->conn->query("SELECT COUNT(*) AS n FROM users");
        return (int) $res->fetch_assoc()['n'];
    }
}
