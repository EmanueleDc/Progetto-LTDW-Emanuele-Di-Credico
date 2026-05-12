<?php
require_once '../../include/template2.inc.php';
require_once '../../include/common.php';
require_once '../../dao/OrderDAO.php';
require_once '../../dao/BookDAO.php';

requireLogin();

$cartItems = $_SESSION['cart'] ?? [];
if (empty($cartItems)) {
    setFlash("Il carrello è vuoto.", "warning");
    redirect('shop.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderDao = new OrderDAO($conn);
    $bookDao  = new BookDAO($conn);
    
    $userId = currentUserId();
    $total  = cartTotal();
    
    $orderId = $orderDao->create($userId, $total);
    if ($orderId) {
        foreach ($cartItems as $bookId => $item) {
            $orderDao->createItem($orderId, $bookId, $item['qty'], $item['price']);
            $bookDao->decrementStock($bookId, $item['qty']);
        }
        cartClear();
        setFlash("Ordine effettuato con successo! Grazie per l'acquisto.");
        redirect('index.php');
    } else {
        setFlash("Errore durante la creazione dell'ordine.", "danger");
    }
}

$tpl = new Template('../../skins/frontend/Fruitables/dtml/main');
$contentTpl = new Template('../../skins/frontend/Fruitables/dtml/checkout');

$contentTpl->setContent('grand_total', number_format(cartTotal(), 2));

$tpl->setContent('page_content', $contentTpl->get());
$tpl->setContent('flash', getFlash());
$tpl->setContent('cart_count', cartCount());

echo $tpl->close();
?>
