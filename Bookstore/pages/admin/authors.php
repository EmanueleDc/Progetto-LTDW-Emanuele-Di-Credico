<?php
require_once 'admin_common.php';

//Gestione azioni
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'add':
            if ($authorDao->create(sanitizeInput($_POST['name']), sanitizeInput($_POST['biography']))) {
                setFlash("Nuovo autore registrato.");
            }
            break;

        case 'edit':
            if ($authorDao->update((int)$_POST['id'], sanitizeInput($_POST['name']), sanitizeInput($_POST['biography']))) {
                setFlash("Dati autore aggiornati.");
            }
            break;

        case 'delete':
            if ($authorDao->delete((int)$_POST['id'])) {
                setFlash("Autore rimosso.");
            }
            break;
    }
    adminRedirect('authors.php');
}

//Mostro elenco e form
$tpl = new Template('../../skins/admin/tabler/dtml/main_admin');
$contentTpl = new Template('../../skins/admin/tabler/dtml/authors');

$items = $authorDao->getAll();
$editId = $_GET['edit_id'] ?? null;

$contentTpl->setContent('form_action', $editId ? 'edit' : 'add');
$contentTpl->setContent('form_title_page', $editId ? 'Modifica' : 'Aggiungi');
$contentTpl->setContent('if_edit', $editId !== null);

foreach ($items as $item) {
    $contentTpl->setContent('author_id',   $item['id']);
    $contentTpl->setContent('author_name', $item['name']);
    
    if ($editId == $item['id']) {
        $contentTpl->setContent('form_id',        $item['id']);
        $contentTpl->setContent('form_name',      $item['name']);
        $contentTpl->setContent('form_biography', $item['biography']);
    }
}

$tpl->setContent('page_content', $contentTpl->get());
populateTemplateBase($tpl);
echo $tpl->close();
?>
