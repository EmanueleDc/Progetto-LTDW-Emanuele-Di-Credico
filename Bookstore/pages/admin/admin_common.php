<?php
require_once '../../include/template2.inc.php';
require_once '../../include/common.php';
require_once '../../dao/BookDAO.php';
require_once '../../dao/OrderDAO.php';
require_once '../../dao/UserDAO.php';
require_once '../../dao/AuthorDAO.php';
require_once '../../dao/CategoryDAO.php';

//Solo admin
requireAdmin();

//Inizializza DAO
$bookDao     = new BookDAO($conn);
$orderDao    = new OrderDAO($conn);
$userDao     = new UserDAO($conn);
$authorDao   = new AuthorDAO($conn);
$categoryDao = new CategoryDAO($conn);

//Redirect rapido
function adminRedirect($page) {
    header("Location: $page");
    exit;
}

//Upload copertine
$handleUpload = function($file) {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) return null;
    $uploadDir = '../../skins/frontend/Fruitables/img/';
    $filename = time() . '_' . basename($file['name']);
    return move_uploaded_file($file['tmp_name'], $uploadDir . $filename) ? $filename : null;
};
?>
