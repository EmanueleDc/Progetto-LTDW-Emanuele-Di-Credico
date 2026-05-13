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
            //Salvo address
            $addressData = [
                'street'   => sanitizeInput($_POST['street'] ?? ''),
                'city'     => sanitizeInput($_POST['city'] ?? ''),
                'zip_code' => sanitizeInput($_POST['zip_code'] ?? ''),
                'country'  => sanitizeInput($_POST['country'] ?? '')
            ];
            $dao->updateProfile($id, $username, $email, null, $addressData);

            //Lo loggo subito
            $newUser = $dao->findById($id);
            unset($newUser['password_hash']);
            loginUser($newUser);

            setFlash("Benvenuto su BookStories! Registrazione completata.");
            redirect('index.php');
        } else {
            setFlash("Errore durante la registrazione.", "danger");
        }
    }
}

$tpl = new Template('../../skins/frontend/Fruitables/dtml/main');
$contentTpl = new Template('../../skins/frontend/Fruitables/dtml/signup');

$tpl->setContent('page_content', $contentTpl->get());
populateTemplateBase($tpl);

echo $tpl->close();
?>
