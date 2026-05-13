<?php

session_start();
require_once __DIR__ . '/dbms.inc.php';

//Sanifica input
function sanitizeInput($value) {
    return trim(htmlspecialchars($value, ENT_QUOTES, 'UTF-8'));
}

//Utility redirect
function redirect($url) {
    header("Location: $url");
    exit;
}

//Gestione messaggi flash
function setFlash($message, $type = 'success') {
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}

function getFlash() {
    if (empty($_SESSION['flash'])) return '';
    $f = $_SESSION['flash'];
    unset($_SESSION['flash']);
    $type = $f['type'] === 'danger' ? 'danger' : ($f['type'] === 'success' ? 'success' : 'info');
    return "<div class='alert alert-{$type} alert-important alert-dismissible fade show' role='alert'>
                <div class='d-flex'>
                    <div>
                        <svg xmlns='http://www.w3.org/2000/svg' class='icon alert-icon' width='24' height='24' viewBox='0 0 24 24' stroke-width='2' stroke='currentColor' fill='none' stroke-linecap='round' stroke-linejoin='round'><path stroke='none' d='M0 0h24v24H0z' fill='none'/><path d='M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0' /><path d='M12 8l0 4' /><path d='M12 16l.01 0' /></svg>
                    </div>
                    <div>{$f['message']}</div>
                </div>
                <button type='button' class='btn-close btn-close-white' data-bs-dismiss='alert' aria-label='Close'></button>
            </div>";
}

//Controlli accesso
function isLogged() {
    return !empty($_SESSION['user']['id']);
}

function isAdminUser() {
    return hasService('admin_access');
}

function hasService($serviceName) {
    if (!isLogged()) return false;
    $services = $_SESSION['user']['services'] ?? [];
    return in_array($serviceName, $services);
}

function currentUserId() {
    return $_SESSION['user']['id'] ?? null;
}

function requireLogin($redirect = '../../pages/frontend/login.php') {
    if (!isLogged()) {
        redirect($redirect);
    }
}

function requireAdmin($redirect = '../../pages/frontend/login.php') {
    if (!isAdminUser()) {
        redirect($redirect);
    }
}

function getCurrentUser() {
    return $_SESSION['user'] ?? null;
}

//Funzioni carrello
function cartCount() {
    if (empty($_SESSION['cart'])) return 0;
    return array_sum(array_column($_SESSION['cart'], 'qty'));
}

function cartAdd($bookId, $title, $price, $qty = 1) {
    if (!isset($_SESSION['cart'][$bookId])) {
        $_SESSION['cart'][$bookId] = ['title' => $title, 'price' => $price, 'qty' => 0];
    }
    $_SESSION['cart'][$bookId]['qty'] += $qty;
}

function cartRemove($bookId) {
    unset($_SESSION['cart'][$bookId]);
}

function cartClear() {
    $_SESSION['cart'] = [];
}

function cartTotal() {
    if (empty($_SESSION['cart'])) return 0.0;
    $total = 0.0;
    foreach ($_SESSION['cart'] as $item) {
        $total += $item['price'] * $item['qty'];
    }
    return $total;
}

//Popola i tag base del template
function populateTemplateBase(Template $tpl) {
    $logged = isLogged();
    $admin  = isAdminUser();
    
    
    $tpl->setContent('if_logged',     $logged ? "1" : "");
    $tpl->setContent('if_not_logged', !$logged ? "1" : "");
    $tpl->setContent('if_admin',      $admin ? "1" : "");
    $tpl->setContent('logged_user_name', $_SESSION['user']['username'] ?? '');
    $tpl->setContent('cart_count',    cartCount());
    $tpl->setContent('flash',         getFlash());
}
