<?php
// 1. Start the session
// You must start the session to be able to access and destroy it.
session_start();

// 2. Unset all of the session variables
// This clears all data from the $_SESSION array.
$_SESSION = array();

// 3. Destroy the session cookie
// If you are using session cookies (the default), it's good practice
// to explicitly delete the cookie from the user's browser.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 4. Finally, destroy the session
// This removes all session data from the server.
session_destroy();

// 5. Redirect to the login page
// After logging out, send the user back to the login page.
header("Location: login.php");

// 6. Ensure no further code is executed after the redirect.
exit();
?>