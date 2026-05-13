<?php
require_once '../../include/template2.inc.php';
require_once '../../include/common.php';
require_once '../../dao/BookDAO.php';

$bookDao = new BookDAO($conn);
$id = (int)($_GET['id'] ?? 0);
$book = $bookDao->getById($id);

if (!$book) {
    setFlash("Libro non trovato.", "warning");
    redirect('shop.php');
}

//Aggiunta al carrello
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $qty = (int)($_POST['quantity'] ?? 1);
    if ($qty > 0 && $qty <= $book['stock']) {
        cartAdd($book['id'], $book['title'], $book['price'], $qty);
        setFlash("Libro aggiunto al carrello!");
        redirect('cart.php');
    } else {
        setFlash("Quantità non valida o stock insufficiente.", "danger");
    }
}

$tpl = new Template('../../skins/frontend/Fruitables/dtml/main');
$contentTpl = new Template('../../skins/frontend/Fruitables/dtml/book');

$contentTpl->setContent('book_id',          $book['id']);
$contentTpl->setContent('book_title',       $book['title']);
$contentTpl->setContent('book_price',       $book['price']);
$contentTpl->setContent('book_description', $book['description']);
$contentTpl->setContent('book_stock',       $book['stock']);
$contentTpl->setContent('book_isbn',        $book['isbn']);
$contentTpl->setContent('book_author',      $book['authors']);
$contentTpl->setContent('book_category',    $book['categories']);
$contentTpl->setContent('book_image',       $book['cover_image'] ? '../../skins/frontend/Fruitables/img/'.$book['cover_image'] : '../../skins/frontend/Fruitables/img/fruite-item-1.jpg');

if ($book['stock'] <= 0) {
    $contentTpl->setContent('out_of_stock_attr', 'disabled');
    $contentTpl->setContent('btn_label', 'Esaurito');
} else {
    $contentTpl->setContent('out_of_stock_attr', '');
    $contentTpl->setContent('btn_label', 'Aggiungi al Carrello');
}

$tpl->setContent('page_content', $contentTpl->get());
populateTemplateBase($tpl);

echo $tpl->close();
?>
