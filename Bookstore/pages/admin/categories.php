<?php
require_once 'admin_common.php';

//Aggiungo o cancello categorie
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'add':
            if ($categoryDao->create(sanitizeInput($_POST['name']), sanitizeInput($_POST['description']))) {
                setFlash("Nuova categoria creata.");
            }
            break;

        case 'edit':
            if ($categoryDao->update((int)$_POST['id'], sanitizeInput($_POST['name']), sanitizeInput($_POST['description']))) {
                setFlash("Categoria aggiornata.");
            }
            break;

        case 'delete':
            if ($categoryDao->delete((int)$_POST['id'])) {
                setFlash("Categoria eliminata.");
            }
            break;
    }
    adminRedirect('categories.php');
}

//Mostro la lista
$tpl = new Template('../../skins/admin/tabler/dtml/main_admin');
$contentTpl = new Template('../../skins/admin/tabler/dtml/categories');

$items = $categoryDao->getAll();
$editId = $_GET['edit_id'] ?? null;

$contentTpl->setContent('form_action', $editId ? 'edit' : 'add');
$contentTpl->setContent('form_title_page', $editId ? 'Modifica' : 'Aggiungi');
$contentTpl->setContent('if_edit', $editId !== null);

foreach ($items as $item) {
    $contentTpl->setContent('cat_id',   $item['id']);
    $contentTpl->setContent('cat_name', $item['name']);
    
    if ($editId == $item['id']) {
        $contentTpl->setContent('form_id',          $item['id']);
        $contentTpl->setContent('form_name',        $item['name']);
        $contentTpl->setContent('form_description', $item['description']);
    }
}

$tpl->setContent('page_content', $contentTpl->get());
populateTemplateBase($tpl);
echo $tpl->close();
?>
