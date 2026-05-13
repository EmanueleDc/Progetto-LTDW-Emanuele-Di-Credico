<?php
require_once '../../include/template2.inc.php';
require_once '../../include/common.php';
require_once '../../dao/BookDAO.php';

$bookDao = new BookDAO($conn);
$featuredBooks = $bookDao->getFeatured(8);

$tpl = new Template('../../skins/frontend/Fruitables/dtml/main');
$contentTpl = new Template('../../skins/frontend/Fruitables/dtml/home');

foreach ($featuredBooks as $book) {
    $contentTpl->setContent('book_id',    $book['id']);
    $contentTpl->setContent('book_title', $book['title']);
    $contentTpl->setContent('book_price', $book['price']);
    $contentTpl->setContent('book_image', $book['cover_image'] ? '../../skins/frontend/Fruitables/img/'.$book['cover_image'] : '../../skins/frontend/Fruitables/img/fruite-item-1.jpg');
    $contentTpl->setContent('book_author', $book['authors']);
}

populateTemplateBase($contentTpl);
$tpl->setContent('page_content', $contentTpl->get());
populateTemplateBase($tpl);

echo $tpl->close();
?>
