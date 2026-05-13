<?php
require_once 'admin_common.php';

//Gestione Azioni
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'edit_user':
            if ($userDao->update((int)$_POST['id'], sanitizeInput($_POST['email']))) {
                setFlash("Profilo utente aggiornato.");
            }
            break;

        case 'delete_user':
            if ($userDao->delete((int)$_POST['id'])) {
                setFlash("Utente eliminato definitivamente.");
            }
            break;

        case 'set_role':
            $targetId = (int)$_POST['id'];
            $newRole  = sanitizeInput($_POST['role']);
            if ($targetId == $_SESSION['user']['id']) {
                setFlash("Non puoi modificare il tuo stesso ruolo!", "warning");
            } elseif ($userDao->setRole($targetId, $newRole)) {
                setFlash("Permessi aggiornati.");
            }
            break;
    }
    adminRedirect('users.php');
}

//Preparazione Template
$tpl = new Template('../../skins/admin/tabler/dtml/main_admin');
$contentTpl = new Template('../../skins/admin/tabler/dtml/users');

$allUsers = $userDao->getAll();
foreach ($allUsers as $u) {
    $prefix = $u['is_admin'] ? 'adm_' : 'usr_';
    $contentTpl->setContent($prefix . 'id',       $u['id']);
    $contentTpl->setContent($prefix . 'username', $u['username']);
    $contentTpl->setContent($prefix . 'email',    $u['email']);
    
    if (isset($_GET['edit_id']) && $_GET['edit_id'] == $u['id']) {
        $contentTpl->setContent('if_edit',       true);
        $contentTpl->setContent('form_id',       $u['id']);
        $contentTpl->setContent('form_username', $u['username']);
        $contentTpl->setContent('form_email',    $u['email']);
    }
}
if (!isset($_GET['edit_id'])) $contentTpl->setContent('if_not_edit', true);

$tpl->setContent('page_content', $contentTpl->get());
populateTemplateBase($tpl);
echo $tpl->close();
?>
