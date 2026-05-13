<?php
require_once 'admin_common.php';

//Gestione Azioni
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'update_status':
            $orderId   = (int)$_POST['id'];
            $newStatus = sanitizeInput($_POST['status']);
            $oldOrder  = $orderDao->getById($orderId);

            if ($oldOrder && $newStatus === 'cancelled' && $oldOrder['status'] !== 'cancelled') {
                $orderDao->restoreStock($orderId);
            }

            if ($orderDao->updateStatus($orderId, $newStatus)) {
                setFlash("Stato ordine aggiornato.");
            }
            break;

        case 'delete':
            $orderId  = (int)$_POST['id'];
            $oldOrder = $orderDao->getById($orderId);
            
            if ($oldOrder && $oldOrder['status'] !== 'cancelled') {
                $orderDao->restoreStock($orderId);
            }

            if ($orderDao->delete($orderId)) {
                setFlash("Ordine annullato, rimosso e stock ripristinato.");
            }
            break;
    }
    adminRedirect('orders.php');
}

//Preparazione Template
$tpl = new Template('../../skins/admin/tabler/dtml/main_admin');
$contentTpl = new Template('../../skins/admin/tabler/dtml/orders');

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

$tpl->setContent('page_content', $contentTpl->get());
populateTemplateBase($tpl);
echo $tpl->close();
?>
