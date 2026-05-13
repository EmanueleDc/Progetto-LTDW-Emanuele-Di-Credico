<?php
require_once '../../include/template2.inc.php';
require_once '../../include/common.php';
require_once '../../dao/UserDAO.php';

requireLogin(); //Solo loggati
$userDao = new UserDAO($conn);
$userId = currentUserId();

//Salvataggio dati
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username']);
    $email    = sanitizeInput($_POST['email']);
    $password = !empty($_POST['password']) ? $_POST['password'] : null;
    
    $addressData = [
        'street'   => sanitizeInput($_POST['street']),
        'city'     => sanitizeInput($_POST['city']),
        'zip_code' => sanitizeInput($_POST['zip_code']),
        'country'  => sanitizeInput($_POST['country'])
    ];

    if ($userDao->updateProfile($userId, $username, $email, $password, $addressData)) {
        setFlash("Profilo aggiornato con successo!");
        //Aggiorno i dati in sessione
        $updatedUser = $userDao->findById($userId);
        unset($updatedUser['password_hash']);
        $_SESSION['user'] = $updatedUser;
    } else {
        setFlash("Errore durante l'aggiornamento.", "danger");
    }
    redirect('profile.php');
}

//Carico i dati
$user = $userDao->findById($userId);
$addr = $userDao->getAddress($userId);

$tpl = new Template('../../skins/frontend/Fruitables/dtml/main');
$contentTpl = new Template('../../skins/frontend/Fruitables/dtml/profile');

$contentTpl->setContent('u_username', $user['username']);
$contentTpl->setContent('u_email',    $user['email']);
$contentTpl->setContent('a_street',   $addr['street'] ?? '');
$contentTpl->setContent('a_city',     $addr['city'] ?? '');
$contentTpl->setContent('a_zip',      $addr['zip_code'] ?? '');
$contentTpl->setContent('a_country',  $addr['country'] ?? '');

$tpl->setContent('page_content', $contentTpl->get());
populateTemplateBase($tpl);

echo $tpl->close();
?>
