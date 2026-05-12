<?php
/**
 * Controller Centrale Amministrativo
 * Gestisce tutte le operazioni CRUD e la visualizzazione del pannello di controllo.
 */

require_once '../../include/template2.inc.php';
require_once '../../include/common.php';
require_once '../../dao/BookDAO.php';
require_once '../../dao/OrderDAO.php';
require_once '../../dao/UserDAO.php';
require_once '../../dao/AuthorDAO.php';
require_once '../../dao/CategoryDAO.php';

// Protezione: Solo gli amministratori possono accedere
requireAdmin();

$bookDao     = new BookDAO($conn);
$orderDao    = new OrderDAO($conn);
$userDao     = new UserDAO($conn);
$authorDao   = new AuthorDAO($conn);
$categoryDao = new CategoryDAO($conn);

$page   = $_GET['page']   ?? 'home';
$action = $_GET['action'] ?? '';

/* -------------------------------------------------------------------------- */
/*                          GESTIONE AZIONI (POST)                            */
/* -------------------------------------------------------------------------- */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $handleUpload = function($file) {
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) return null;
        $uploadDir = '../../skins/frontend/Fruitables/img/';
        $filename = time() . '_' . basename($file['name']);
        return move_uploaded_file($file['tmp_name'], $uploadDir . $filename) ? $filename : null;
    };

    switch ($action) {
        
        case 'add':
            $cover = $handleUpload($_FILES['cover_image'] ?? null) ?: 'fruite-item-1.jpg';
            $newId = $bookDao->create(
                sanitizeInput($_POST['title']), 
                sanitizeInput($_POST['isbn']), 
                (float)$_POST['price'], 
                (int)$_POST['stock'], 
                sanitizeInput($_POST['description']), 
                $cover
            );
            if ($newId) {
                $bookDao->setAuthors($newId, $_POST['authors'] ?? []);
                $bookDao->setCategories($newId, $_POST['categories'] ?? []);
                setFlash("Libro aggiunto con successo!");
                redirect('admin.php?page=home');
            }
            break;

        case 'edit':
            $id = (int)$_POST['id'];
            $newCover = $handleUpload($_FILES['cover_image'] ?? null);
            $oldBook  = $bookDao->getById($id);
            $cover    = $newCover ?: ($oldBook['cover_image'] ?? 'fruite-item-1.jpg');
            
            if ($bookDao->update($id, sanitizeInput($_POST['title']), sanitizeInput($_POST['isbn']), (float)$_POST['price'], (int)$_POST['stock'], sanitizeInput($_POST['description']), $cover)) {
                $bookDao->setAuthors($id, $_POST['authors'] ?? []);
                $bookDao->setCategories($id, $_POST['categories'] ?? []);
                setFlash("Libro aggiornato correttamente.");
                redirect('admin.php?page=edit_book');
            }
            break;

        case 'delete':
            if ($bookDao->delete((int)$_POST['id'])) {
                setFlash("Libro rimosso dal catalogo.");
                redirect('admin.php?page=delete_book');
            }
            break;

        case 'edit_user':
            if ($userDao->update((int)$_POST['id'], sanitizeInput($_POST['email']))) {
                setFlash("Profilo utente aggiornato.");
                redirect('admin.php?page=users');
            }
            break;

        case 'delete_user':
            if ($userDao->delete((int)$_POST['id'])) {
                setFlash("Utente eliminato definitivamente.");
                redirect('admin.php?page=users');
            }
            break;

        case 'set_role':
            $targetId = (int)$_POST['id'];
            $newRole  = sanitizeInput($_POST['role']);
            if ($targetId == $_SESSION['user']['id']) {
                setFlash("Non puoi modificare il tuo stesso ruolo!", "warning");
            } elseif ($userDao->setRole($targetId, $newRole)) {
                setFlash("Permessi aggiornati.");
            }
            redirect('admin.php?page=users');
            break;

        case 'add_author':
            if ($authorDao->create(sanitizeInput($_POST['name']), sanitizeInput($_POST['biography']))) {
                setFlash("Nuovo autore registrato.");
                redirect('admin.php?page=authors');
            }
            break;

        case 'edit_author':
            if ($authorDao->update((int)$_POST['id'], sanitizeInput($_POST['name']), sanitizeInput($_POST['biography']))) {
                setFlash("Dati autore aggiornati.");
                redirect('admin.php?page=authors');
            }
            break;

        case 'delete_author':
            if ($authorDao->delete((int)$_POST['id'])) {
                setFlash("Autore rimosso.");
                redirect('admin.php?page=authors');
            }
            break;

        case 'add_cat':
            if ($categoryDao->create(sanitizeInput($_POST['name']), sanitizeInput($_POST['description']))) {
                setFlash("Nuova categoria creata.");
                redirect('admin.php?page=categories');
            }
            break;

        case 'edit_cat':
            if ($categoryDao->update((int)$_POST['id'], sanitizeInput($_POST['name']), sanitizeInput($_POST['description']))) {
                setFlash("Categoria aggiornata.");
                redirect('admin.php?page=categories');
            }
            break;

        case 'delete_cat':
            if ($categoryDao->delete((int)$_POST['id'])) {
                setFlash("Categoria eliminata.");
                redirect('admin.php?page=categories');
            }
            break;

        case 'update_order_status':
            if ($orderDao->updateStatus((int)$_POST['id'], sanitizeInput($_POST['status']))) {
                setFlash("Stato ordine aggiornato.");
            }
            redirect('admin.php?page=orders');
            break;

        case 'delete_order':
            if ($orderDao->delete((int)$_POST['id'])) {
                setFlash("Ordine annullato ed eliminato.");
            }
            redirect('admin.php?page=orders');
            break;
    }
}

/* -------------------------------------------------------------------------- */
/*                         PREPARAZIONE TEMPLATE (GET)                        */
/* -------------------------------------------------------------------------- */

$tpl = new Template('../../skins/admin/tabler/dtml/main_admin');
$contentTpl = new Template('../../skins/admin/tabler/dtml/' . $page);

switch ($page) {

    case 'home':
        $contentTpl->setContent('total_books', $bookDao->countTotalStock());
        $contentTpl->setContent('avail_books', $bookDao->countAvailable());
        $contentTpl->setContent('orders_month', $orderDao->countThisMonth());
        $contentTpl->setContent('units_sold',   $orderDao->unitsSoldThisMonth());
        $contentTpl->setContent('total_units_sold', $orderDao->totalUnitsSold());
        $contentTpl->setContent('revenue',      number_format($orderDao->revenueThisMonth(), 2));
        
        $recentOrders = $conn->query("SELECT o.*, u.username FROM orders o JOIN users u ON u.id = o.user_id ORDER BY o.order_date DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);
        foreach ($recentOrders as $ord) {
            $contentTpl->setContent('ord_id',     $ord['id']);
            $contentTpl->setContent('ord_user',   $ord['username']);
            $contentTpl->setContent('ord_total',  $ord['total_price']);
            $contentTpl->setContent('ord_status', $ord['status']);
            $contentTpl->setContent('ord_date',   $ord['order_date']);
        }
        break;

    case 'orders':
        $orders = $orderDao->getAllWithDetails();
        foreach ($orders as $o) {
            $badgeClass = match($o['status']) {
                'completed' => 'bg-success',
                'pending'   => 'bg-warning',
                'shipped'   => 'bg-info',
                'cancelled' => 'bg-danger',
                default     => 'bg-secondary'
            };
            $contentTpl->setContent('ord_id',              $o['id']);
            $contentTpl->setContent('ord_user',            $o['username']);
            $contentTpl->setContent('ord_total',           $o['total_price']);
            $contentTpl->setContent('ord_date',            $o['order_date']);
            $contentTpl->setContent('ord_status',          $o['status']);
            $contentTpl->setContent('badge_class',         $badgeClass);
            $contentTpl->setContent('if_status_pending',   $o['status'] === 'pending');
            $contentTpl->setContent('if_status_shipped',   $o['status'] === 'shipped');
            $contentTpl->setContent('if_status_completed', $o['status'] === 'completed');
        }
        break;

    case 'users':
        $allUsers = $userDao->getAll();
        foreach ($allUsers as $u) {
            $prefix = $u['is_admin'] ? 'adm_' : 'usr_';
            $contentTpl->setContent($prefix . 'id',       $u['id']);
            $contentTpl->setContent($prefix . 'username', $u['username']);
            $contentTpl->setContent($prefix . 'email',    $u['email']);
            
            if (isset($_GET['edit_id']) && $_GET['edit_id'] == $u['id']) {
                $contentTpl->setContent('if_edit',       true);
                $contentTpl->setContent('form_id',       $u['id']);
                $contentTpl->setContent('form_username', $u['username']);
                $contentTpl->setContent('form_email',    $u['email']);
            }
        }
        if (!isset($_GET['edit_id'])) $contentTpl->setContent('if_not_edit', true);
        break;

    case 'authors':
    case 'categories':
        $dao = ($page === 'authors') ? $authorDao : $categoryDao;
        $items = $dao->getAll();
        $editId = $_GET['edit_id'] ?? null;
        
        $contentTpl->setContent('form_action', $editId ? ($page === 'authors' ? 'edit_author' : 'edit_cat') : ($page === 'authors' ? 'add_author' : 'add_cat'));
        $contentTpl->setContent('form_title_page', $editId ? 'Modifica' : 'Aggiungi');
        $contentTpl->setContent('if_edit', $editId !== null);
        
        foreach ($items as $item) {
            $idKey   = ($page === 'authors') ? 'author_id' : 'cat_id';
            $nameKey = ($page === 'authors') ? 'author_name' : 'cat_name';
            $contentTpl->setContent($idKey,   $item['id']);
            $contentTpl->setContent($nameKey, $item['name']);
            
            if ($editId == $item['id']) {
                $contentTpl->setContent('form_id',   $item['id']);
                $contentTpl->setContent('form_name', $item['name']);
                $contentTpl->setContent($page === 'authors' ? 'form_biography' : 'form_description', ($page === 'authors' ? $item['biography'] : $item['description']));
            }
        }
        break;

    case 'add_book':
    case 'edit_book':
    case 'delete_book':
        foreach ($authorDao->getAll()   as $a) {
            $contentTpl->setContent('a_id',   $a['id']);
            $contentTpl->setContent('a_name', $a['name']);
        }
        foreach ($categoryDao->getAll() as $c) {
            $contentTpl->setContent('c_id',   $c['id']);
            $contentTpl->setContent('c_name', $c['name']);
        }
        
        $books = $bookDao->getAll();
        foreach ($books as $b) {
            $contentTpl->setContent('book_id',    $b['id']);
            $contentTpl->setContent('book_title', $b['title']);
            $contentTpl->setContent('book_isbn',  $b['isbn']);
            $contentTpl->setContent('book_price', $b['price']);
            $contentTpl->setContent('book_stock', $b['stock']);
            
            if (isset($_GET['edit_id']) && $_GET['edit_id'] == $b['id']) {
                 $contentTpl->setContent('form_id',    $b['id']);
                 $contentTpl->setContent('form_title', $b['title']);
                 $contentTpl->setContent('form_isbn',  $b['isbn']);
                 $contentTpl->setContent('form_price', $b['price']);
                 $contentTpl->setContent('form_stock', $b['stock']);
                 $contentTpl->setContent('form_desc',  $b['description']);
                 $contentTpl->setContent('form_cover', $b['cover_image']);
            }
        }
        break;
}

$tpl->setContent('page_content', $contentTpl->get());
$tpl->setContent('flash', getFlash());
echo $tpl->close();