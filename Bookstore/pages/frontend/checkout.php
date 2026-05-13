<?php
require_once '../../include/template2.inc.php';
require_once '../../include/common.php';
require_once '../../dao/UserDAO.php';
require_once '../../dao/OrderDAO.php';
require_once '../../dao/BookDAO.php';

requireLogin();

$userDao = new UserDAO($conn);
$userId = currentUserId();
$addr = $userDao->getAddress($userId);

$cartItems = $_SESSION['cart'] ?? [];
if (empty($cartItems)) {
    setFlash("Il carrello è vuoto.", "warning");
    redirect('shop.php');
}

//Formatto l'indirizzo
$fullAddress = "Indirizzo non specificato";
if ($addr) {
    $fullAddress = "{$addr['street']}, {$addr['city']} ({$addr['zip_code']}), {$addr['country']}";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderDao = new OrderDAO($conn);
    $bookDao  = new BookDAO($conn);
    
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
$contentTpl->setContent('delivery_address', $fullAddress);

foreach ($cartItems as $id => $item) {
    $contentTpl->setContent('item_title', $item['title']);
    $contentTpl->setContent('item_price', $item['price']);
    $contentTpl->setContent('item_qty',   $item['qty']);
    $contentTpl->setContent('item_total', number_format($item['price'] * $item['qty'], 2));
}

$tpl->setContent('page_content', $contentTpl->get());
populateTemplateBase($tpl);

echo $tpl->close();
?>
