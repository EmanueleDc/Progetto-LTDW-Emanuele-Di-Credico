<?php
require_once '../../include/template2.inc.php';
require_once '../../include/common.php';
require_once '../../dao/BookDAO.php';
require_once '../../dao/CategoryDAO.php';

$bookDao = new BookDAO($conn);
$categoryDao = new CategoryDAO($conn);

$search = sanitizeInput($_GET['search'] ?? '');
$catId  = (int)($_GET['category'] ?? 0);

if ($search) {
    $books = $bookDao->search($search);
} elseif ($catId > 0) {
    $books = $bookDao->getByCategory($catId);
} else {
    $books = $bookDao->getAll();
}

$categories = $categoryDao->getAll();

$tpl = new Template('../../skins/frontend/Fruitables/dtml/main');
$contentTpl = new Template('../../skins/frontend/Fruitables/dtml/shop');

$displayTerm = !empty($search) ? $search : "Tutti i nostri libri";

$tpl = new Template('../../skins/frontend/Fruitables/dtml/main');
$contentTpl = new Template('../../skins/frontend/Fruitables/dtml/shop');

$contentTpl->setContent('search_term', $displayTerm);

//Menu categorie
foreach ($categories as $cat) {
    $contentTpl->setContent('cat_id',   $cat['id']);
    $contentTpl->setContent('cat_name', $cat['name']);
}

//Elenco prodotti
foreach ($books as $book) {
    $contentTpl->setContent('book_id',    $book['id']);
    $contentTpl->setContent('book_title', $book['title']);
    $contentTpl->setContent('book_price', $book['price']);
    $contentTpl->setContent('book_image', $book['cover_image'] ? '../../skins/frontend/Fruitables/img/'.$book['cover_image'] : '../../skins/frontend/Fruitables/img/fruite-item-1.jpg');
    $contentTpl->setContent('book_author', $book['authors'] ?? 'N/A');
}

$tpl->setContent('page_content', $contentTpl->get());
populateTemplateBase($tpl);

echo $tpl->close();
?>
