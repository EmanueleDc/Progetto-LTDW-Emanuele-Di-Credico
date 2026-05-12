<?php
require_once '../../include/template2.inc.php';
require_once '../../include/common.php';

// Gestione azioni carrello
$action = $_GET['action'] ?? '';
$id     = (int)($_GET['id'] ?? 0);

if ($action === 'remove' && $id > 0) {
    cartRemove($id);
    setFlash("Prodotto rimosso.");
    redirect('cart.php');
} elseif ($action === 'clear') {
    cartClear();
    setFlash("Carrello svuotato.");
    redirect('cart.php');
}

$tpl = new Template('../../skins/frontend/Fruitables/dtml/main');
$contentTpl = new Template('../../skins/frontend/Fruitables/dtml/cart');

$cartItems = $_SESSION['cart'] ?? [];
$total = 0;

if (empty($cartItems)) {
    $contentTpl->setContent('cart_empty', "Il tuo carrello è vuoto.");
} else {
    foreach ($cartItems as $id => $item) {
        $subtotal = $item['price'] * $item['qty'];
        $total += $subtotal;
        
        $contentTpl->setContent('item_id',    $id);
        $contentTpl->setContent('item_title', $item['title']);
        $contentTpl->setContent('item_price', $item['price']);
        $contentTpl->setContent('item_qty',   $item['qty']);
        $contentTpl->setContent('item_total', number_format($subtotal, 2));
    }
}

$contentTpl->setContent('grand_total', number_format($total, 2));

$tpl->setContent('page_content', $contentTpl->get());
$tpl->setContent('flash', getFlash());
$tpl->setContent('cart_count', cartCount());

echo $tpl->close();
?>
