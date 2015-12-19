<?php
/**
 * User authentication is done here.
 * This is a super-simple auth method
 * that tries to be secure enough.
 */

// if the user is NOT logged in already
if (!validateUser()) {

    // attempted login
    if (isset($_POST['proxy-login'])) loginUser();

    // no attempted login = show login page
    die(file_get_contents('login/login.html'));
}

/**
 * validate user cookie with database.
 * note that base64 is an extra-step
 * when storing the cookie on the user client
 * and not an attempt to encrypt passwords.
 *
 * @return bool
 */
function validateUser () {

    // compare saved cookie with the actual credentials from the db.
    if (getUser()['email'] && base64_decode(getUser(true)[1]) === getUser()['pass']) return true;

    return false;
}

/**
 * get all users info from the database
 * the db in this case is a simple made-up text file.
 * change the behaviour of the getUsers() function
 * depending on your own database needs!
 *
 * @return array
 */
function getUsers() {
    $users = array();
    foreach (explode(PHP_EOL, file_get_contents('users')) as $user) {
        $user = explode(':', $user);
        $users[$user[0]] = array(
            'email' => trim($user[0]),
            'pass' => trim($user[1]),
            'name' => trim($user[2])
        );
    }
    return $users;
}

/**
 * get specific user info
 *
 * @param bool $return_cookie. optional - return info from
 * the cookie stored on the client and not the database.
 * @return array
 */
function getUser ($return_cookie = false) {

    // first fetch the stored cookie
    $user_cookie = explode('.', $_COOKIE['proxy']);
    if ($return_cookie) return $user_cookie;

    // find the matching user in the db
    $user = getUsers()[base64_decode($user_cookie[0])];
    return getUsers()[$user['email']];
}

/**
 * login mechanism
 */
function loginUser () {

    // find user in the db
    $user = getUsers()[$_POST['email']];

    // compare hashes; set cookie; and refresh the page
    if (md5($_POST['pass']) === $user['pass']) {
        setcookie('proxy', base64_encode($user['email']) . '.' . base64_encode($user['pass']), false, '/');
        header('Location: ' . $_SERVER['REQUEST_URI']);
    } else die(file_get_contents('login/login_error.html'));
}
