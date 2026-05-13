<?php

class BookDAO {
    private $conn;
    public function __construct($conn) {
        $this->conn = $conn;
    }

    //Tutti i libri
    public function getAll($limit = null, $offset = 0) {
        $sql = "
            SELECT b.*,
                   GROUP_CONCAT(DISTINCT a.name ORDER BY a.name SEPARATOR ', ') AS authors,
                   GROUP_CONCAT(DISTINCT c.name ORDER BY c.name SEPARATOR ', ') AS categories
            FROM books b
            LEFT JOIN books_has_authors ba  ON ba.book_id  = b.id
            LEFT JOIN authors a             ON a.id        = ba.author_id
            LEFT JOIN books_has_categories bc ON bc.book_id = b.id
            LEFT JOIN categories c          ON c.id        = bc.category_id
            GROUP BY b.id
            ORDER BY b.title
        ";
        if ($limit !== null) {
            $sql .= " LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
        }
        $res  = $this->conn->query($sql);
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    //Filtra per categoria
    public function getByCategory($categoryId, $limit = null) {
        $sql = "
            SELECT b.*,
                   GROUP_CONCAT(DISTINCT a.name SEPARATOR ', ') AS authors
            FROM books b
            JOIN books_has_categories bc ON bc.book_id = b.id AND bc.category_id = ?
            LEFT JOIN books_has_authors ba ON ba.book_id = b.id
            LEFT JOIN authors a ON a.id = ba.author_id
            GROUP BY b.id
            ORDER BY b.title
        ";
        if ($limit !== null) $sql .= " LIMIT " . (int)$limit;
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $categoryId);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }

    //Singolo libro
    public function getById($id) {
        $stmt = $this->conn->prepare("
            SELECT b.*,
                   GROUP_CONCAT(DISTINCT a.name SEPARATOR ', ') AS authors,
                   GROUP_CONCAT(DISTINCT c.name SEPARATOR ', ') AS categories
            FROM books b
            LEFT JOIN books_has_authors ba  ON ba.book_id  = b.id
            LEFT JOIN authors a             ON a.id        = ba.author_id
            LEFT JOIN books_has_categories bc ON bc.book_id = b.id
            LEFT JOIN categories c          ON c.id        = bc.category_id
            WHERE b.id = ?
            GROUP BY b.id
        ");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $row;
    }

    //Ricerca per titolo/autore
    public function search($keyword, $limit = 20) {
        $kw   = '%' . $keyword . '%';
        $stmt = $this->conn->prepare("
            SELECT DISTINCT b.*,
                   GROUP_CONCAT(DISTINCT a.name SEPARATOR ', ') AS authors
            FROM books b
            LEFT JOIN books_has_authors ba ON ba.book_id = b.id
            LEFT JOIN authors a ON a.id = ba.author_id
            WHERE b.title LIKE ? OR a.name LIKE ?
            GROUP BY b.id
            ORDER BY b.title
            LIMIT ?
        ");
        $stmt->bind_param('ssi', $kw, $kw, $limit);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }

    //Libri in evidenza
    public function getFeatured($limit = 6) {
        $stmt = $this->conn->prepare("
            SELECT b.*,
                   GROUP_CONCAT(DISTINCT a.name SEPARATOR ', ') AS authors
            FROM books b
            LEFT JOIN books_has_authors ba ON ba.book_id = b.id
            LEFT JOIN authors a ON a.id = ba.author_id
            WHERE b.stock > 0
            GROUP BY b.id
            ORDER BY b.id DESC
            LIMIT ?
        ");
        $stmt->bind_param('i', $limit);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }

    //Stock disponibile
    public function countAvailable() {
        $res = $this->conn->query("SELECT COUNT(*) AS n FROM books WHERE stock > 0");
        return (int) $res->fetch_assoc()['n'];
    }

    //Conteggio titoli
    public function countAll() {
        $res = $this->conn->query("SELECT COUNT(*) AS n FROM books");
        return (int) $res->fetch_assoc()['n'];
    }

    //Somma stock totale
    public function countTotalStock() {
        $res = $this->conn->query("SELECT SUM(stock) AS n FROM books");
        $val = $res->fetch_assoc()['n'];
        return (int) ($val ?? 0);
    }

    //set autori
    public function setAuthors($bookId, array $authorIds) {
        $this->conn->query("DELETE FROM books_has_authors WHERE book_id = $bookId");
        if (empty($authorIds)) return;
        $stmt = $this->conn->prepare("INSERT INTO books_has_authors (book_id, author_id) VALUES (?, ?)");
        foreach ($authorIds as $aId) {
            $stmt->bind_param('ii', $bookId, $aId);
            $stmt->execute();
        }
        $stmt->close();
    }

    //set categorie
    public function setCategories($bookId, array $categoryIds) {
        $this->conn->query("DELETE FROM books_has_categories WHERE book_id = $bookId");
        if (empty($categoryIds)) return;
        $stmt = $this->conn->prepare("INSERT INTO books_has_categories (book_id, category_id) VALUES (?, ?)");
        foreach ($categoryIds as $cId) {
            $stmt->bind_param('ii', $bookId, $cId);
            $stmt->execute();
        }
        $stmt->close();
    }

    //Inserisci
    public function create($title, $isbn, $price, $stock, $description, $cover) {
        $stmt = $this->conn->prepare(
            "INSERT INTO books (title, isbn, price, stock, description, cover_image)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param('ssdiss', $title, $isbn, $price, $stock, $description, $cover);
        $ok = $stmt->execute();
        $id = $ok ? $stmt->insert_id : false;
        $stmt->close();
        return $id;
    }

    //Update
    public function update($id, $title, $isbn, $price, $stock, $description, $cover) {
        $stmt = $this->conn->prepare(
            "UPDATE books SET title=?, isbn=?, price=?, stock=?, description=?, cover_image=?
             WHERE id=?"
        );
        $stmt->bind_param('ssdissi', $title, $isbn, $price, $stock, $description, $cover, $id);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    //Elimina
    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM books WHERE id=?");
        $stmt->bind_param('i', $id);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    //Decrementa stock
    public function decrementStock($id, $qty = 1) {
        $stmt = $this->conn->prepare(
            "UPDATE books SET stock = GREATEST(0, stock - ?) WHERE id = ?"
        );
        $stmt->bind_param('ii', $qty, $id);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }
}
