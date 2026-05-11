<?php
require_once '../../include/template2.inc.php';
$templatePath = '../../skins/admin/tabler/dtml/main_admin';

$page = $_GET['page'] ?? 'home';
// Mappa i nomi delle pagine ai file HTML
$pageFiles = [
    'home' => '../../skins/admin/tabler/dtml/home.html',
    'account' => '../../skins/admin/tabler/dtml/account.html', 
    'add_book' => '../../skins/admin/tabler/dtml/add_book.html',
    'edit_book' => '../../skins/admin/tabler/dtml/edit_book.html',
    'delete_book' => '../../skins/admin/tabler/dtml/delete_book.html',
];

// Controlla se la pagina esiste
if (!isset($pageFiles[$page])) {
    $page = 'home'; 
}


$pageContent = '';
if (file_exists($pageFiles[$page])) {
    $pageContent = file_get_contents($pageFiles[$page]);
}

$tpl = new Template($templatePath);
$tpl->setContent('page_content', $pageContent);


echo $tpl->close();
?>