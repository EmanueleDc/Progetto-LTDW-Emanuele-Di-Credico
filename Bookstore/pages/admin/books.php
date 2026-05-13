<?php
require_once 'admin_common.php';

//Aggiungo o modifico o elimino
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_GET['action'] ?? '';
    
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
            }
            break;

        case 'delete':
            if ($bookDao->delete((int)$_POST['id'])) {
                setFlash("Libro rimosso dal catalogo.");
            }
            break;
    }
    adminRedirect('books.php');
}

//Carico la pagina richiesta
$tpl = new Template('../../skins/admin/tabler/dtml/main_admin');
$page = $_GET['page'] ?? 'edit_book'; //Default view
$contentTpl = new Template('../../skins/admin/tabler/dtml/' . $page);

//Liste per i select autori/categorie
foreach ($authorDao->getAll() as $a) {
    $contentTpl->setContent('a_id',   $a['id']);
    $contentTpl->setContent('a_name', $a['name']);
}
foreach ($categoryDao->getAll() as $c) {
    $contentTpl->setContent('c_id',   $c['id']);
    $contentTpl->setContent('c_name', $c['name']);
}

//Lista libri a video
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

$tpl->setContent('page_content', $contentTpl->get());
populateTemplateBase($tpl);
echo $tpl->close();
?>
