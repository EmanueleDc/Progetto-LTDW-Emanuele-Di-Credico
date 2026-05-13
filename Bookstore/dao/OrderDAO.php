<?php

class OrderDAO {
    private $conn;
    public function __construct($conn) {
        $this->conn = $conn;
    }

    //Recupera per ID
    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $row;
    }

    //Crea ordine
    public function create($userId, $totalPrice) {
        $stmt = $this->conn->prepare(
            "INSERT INTO orders (user_id, total_price, status) VALUES (?, ?, 'pending')"
        );
        $stmt->bind_param('id', $userId, $totalPrice);
        $ok = $stmt->execute();
        $id = $ok ? $stmt->insert_id : false;
        $stmt->close();
        return $id;
    }

    //Aggiunge articolo
    public function createItem($orderId, $bookId, $quantity, $priceAtPurchase) {
        $stmt = $this->conn->prepare(
            "INSERT INTO order_items (order_id, book_id, quantity, price_at_purchase)
             VALUES (?, ?, ?, ?)"
        );
        $stmt->bind_param('iiid', $orderId, $bookId, $quantity, $priceAtPurchase);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    //Tutti con dettagli utente
    public function getAllWithDetails() {
        $res = $this->conn->query("
            SELECT o.*, u.username 
            FROM orders o 
            JOIN users u ON u.id = o.user_id 
            ORDER BY o.order_date DESC
        ");
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    //Ordini di un utente
    public function getByUser($userId) {
        $stmt = $this->conn->prepare(
            "SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC"
        );
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }

    //Update stato
    public function updateStatus($id, $status) {
        $stmt = $this->conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->bind_param('si', $status, $id);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    //Elimina ordine e articoli
    public function delete($id) {
        $this->conn->query("DELETE FROM order_items WHERE order_id = " . (int)$id);
        $stmt = $this->conn->prepare("DELETE FROM orders WHERE id = ?");
        $stmt->bind_param('i', $id);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    /* --- Metodi Statistiche --- */

    public function countAll() {
        $res = $this->conn->query("SELECT COUNT(*) AS n FROM orders");
        return (int)$res->fetch_assoc()['n'];
    }

    public function countThisMonth() {
        $res = $this->conn->query(
            "SELECT COUNT(*) AS n FROM orders
             WHERE MONTH(order_date) = MONTH(NOW()) AND YEAR(order_date) = YEAR(NOW())"
        );
        return (int)$res->fetch_assoc()['n'];
    }

    public function revenueThisMonth() {
        $res = $this->conn->query(
            "SELECT COALESCE(SUM(total_price), 0) AS rev FROM orders
             WHERE MONTH(order_date) = MONTH(NOW()) AND YEAR(order_date) = YEAR(NOW()) AND status != 'cancelled'"
        );
        return (float)$res->fetch_assoc()['rev'];
    }

    public function unitsSoldThisMonth() {
        $res = $this->conn->query(
            "SELECT COALESCE(SUM(oi.quantity), 0) AS n
             FROM order_items oi
             JOIN orders o ON o.id = oi.order_id
             WHERE MONTH(o.order_date) = MONTH(NOW()) AND YEAR(o.order_date) = YEAR(NOW()) AND o.status != 'cancelled'"
        );
        return (int)$res->fetch_assoc()['n'];
    }

    public function totalUnitsSold() {
        $res = $this->conn->query("SELECT COALESCE(SUM(quantity), 0) AS n FROM order_items");
        return (int)$res->fetch_assoc()['n'];
    }

    //Articoli di un ordine
    public function getItemsByOrder($orderId) {
        $stmt = $this->conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
        $stmt->bind_param('i', $orderId);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }

    //Ripristina stock se annullato
    public function restoreStock($orderId) {
        $items = $this->getItemsByOrder($orderId);
        foreach ($items as $item) {
            $stmt = $this->conn->prepare("UPDATE books SET stock = stock + ? WHERE id = ?");
            $stmt->bind_param('ii', $item['quantity'], $item['book_id']);
            $stmt->execute();
            $stmt->close();
        }
    }
}
