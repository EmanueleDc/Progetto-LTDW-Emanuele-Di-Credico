<?php

require_once __DIR__ . '/common.php';

//Carica il DAO utente
function _userDAO() {
    static $dao = null;
    if ($dao === null) {
        require_once __DIR__ . '/../dao/UserDAO.php';
        global $conn;
        $dao = new UserDAO($conn);
    }
    return $dao;
}

//Salva utente in sessione
function loginUser(array $user) {
    session_regenerate_id(true);
    $_SESSION['user'] = $user;
}

//Logout e pulizia sessione
function logoutUser() {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

//Controllo credenziali login
function attemptLogin($username, $password) {
    $dao  = _userDAO();
    $user = $dao->findByUsername($username);
    if ($user && password_verify($password, $user['password_hash'])) {
        //Carica i permessi
        $user['services'] = $dao->getServices($user['id']);
        //Rimuovo la password per sicurezza
        unset($user['password_hash']);
        return $user;
    }
    return false;
}
