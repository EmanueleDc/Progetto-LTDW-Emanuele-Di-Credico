<?php
require_once '../../include/template2.inc.php';
require_once '../../include/auth.php';

if (isLogged()) {
    redirect('index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username'] ?? '');
    $email    = sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    $dao = _userDAO();

    if ($password !== $confirm) {
        setFlash("Le password non coincidono.", "danger");
    } elseif ($dao->findByUsername($username)) {
        setFlash("Username già esistente.", "danger");
    } elseif ($dao->findByEmail($email)) {
        setFlash("Email già registrata.", "danger");
    } else {
        $id = $dao->create($username, $email, $password);
        if ($id) {
            setFlash("Registrazione completata! Ora puoi accedere.");
            redirect('login.php');
        } else {
            setFlash("Errore durante la registrazione.", "danger");
        }
    }
}

$tpl = new Template('../../skins/frontend/Fruitables/dtml/main');
$contentTpl = new Template('../../skins/frontend/Fruitables/dtml/signup');

$tpl->setContent('page_content', $contentTpl->get());
$tpl->setContent('flash', getFlash());
$tpl->setContent('cart_count', cartCount());

echo $tpl->close();
?>
