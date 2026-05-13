<?php
require_once 'admin_common.php';

$tpl = new Template('../../skins/admin/tabler/dtml/main_admin');
$contentTpl = new Template('../../skins/admin/tabler/dtml/home');

//Statistiche dashboard
$contentTpl->setContent('total_books', $bookDao->countTotalStock());
$contentTpl->setContent('avail_books', $bookDao->countAvailable());
$contentTpl->setContent('orders_month', $orderDao->countThisMonth());
$contentTpl->setContent('units_sold',   $orderDao->unitsSoldThisMonth());
$contentTpl->setContent('total_units_sold', $orderDao->totalUnitsSold());
$contentTpl->setContent('revenue',      number_format($orderDao->revenueThisMonth(), 2));

//Lista ultimi ordini
$recentOrders = $conn->query("SELECT o.*, u.username FROM orders o JOIN users u ON u.id = o.user_id ORDER BY o.order_date DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);
foreach ($recentOrders as $ord) {
    $contentTpl->setContent('ord_id',     $ord['id']);
    $contentTpl->setContent('ord_user',   $ord['username']);
    $contentTpl->setContent('ord_total',  $ord['total_price']);
    $contentTpl->setContent('ord_status', $ord['status']);
    $contentTpl->setContent('ord_date',   $ord['order_date']);
}

$tpl->setContent('page_content', $contentTpl->get());
populateTemplateBase($tpl);
echo $tpl->close();
?>
