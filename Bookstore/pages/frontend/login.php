<?php
require_once '../../include/template2.inc.php';
require_once '../../include/auth.php';

if (isLogged()) {
    redirect(isAdminUser() ? '../admin/index.php' : 'index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $user = attemptLogin($username, $password);
    if ($user) {
        loginUser($user);
        setFlash("Bentornato, " . $user['username'] . "!");
        redirect($user['is_admin'] ? '../admin/index.php' : 'index.php');
    } else {
        setFlash("Username o password errati.", "danger");
    }
}

$tpl = new Template('../../skins/frontend/Fruitables/dtml/main');

$contentTpl = new Template('../../skins/frontend/Fruitables/dtml/login');
$tpl->setContent('page_content', $contentTpl->get());
populateTemplateBase($tpl);

echo $tpl->close();
?>
