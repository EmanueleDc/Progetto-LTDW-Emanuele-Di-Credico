<?php
require_once '../../include/template2.inc.php';
require_once '../../include/common.php';
require_once '../../dao/BookDAO.php';

$bookDao = new BookDAO($conn);
$featuredBooks = $bookDao->getFeatured(8);

$tpl = new Template('../../skins/frontend/Fruitables/dtml/main');
$contentTpl = new Template('../../skins/frontend/Fruitables/dtml/index');

foreach ($featuredBooks as $book) {
    $contentTpl->setContent('book_id',    $book['id']);
    $contentTpl->setContent('book_title', $book['title']);
    $contentTpl->setContent('book_price', $book['price']);
    $contentTpl->setContent('book_image', $book['cover_image'] ? '../../skins/frontend/Fruitables/img/'.$book['cover_image'] : '../../skins/frontend/Fruitables/img/fruite-item-1.jpg');
    $contentTpl->setContent('book_author', $book['authors']);
}

$tpl->setContent('page_content', $contentTpl->get());
$tpl->setContent('flash', getFlash());
$tpl->setContent('cart_count', cartCount());

echo $tpl->close();
?>
