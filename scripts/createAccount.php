<?php

// link to config file
require_once("config.php");

// get the users ip
$ip = $_SERVER['REMOTE_ADDR'];

// check if recaptcha is good
$response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=".$recaptchaSecret."&response=".$_POST["response"]."&remoteip=".$ip);
$response = json_decode($response, true);

// check if it is valid
if (intval($response["success"]) !== 1) {
    // invalid
    mysqli_close($link);
    die("recaptcha");
}

// connect to database
$link = mysqli_connect($dbHost, $dbUserName, $dbPassword, $etonDatabase);

// check if there was an error
if (mysqli_connect_error()) {
    mysqli_close($link);
    die("Error connecting to database");
}

// change carset to utf8
mysqli_set_charset($link, "utf8");

// prepare a query to check if the email is already taken
$formattedEmail = encryptEmail($_POST["email"], $encryptAlgorithm);
$formattedEmail = dbEscape($link, $formattedEmail);
$query = "SELECT `email` FROM `users` WHERE `email` = '".$formattedEmail."'";

// execute the query
if($result = mysqli_query($link, $query)) {

    // get the contents of the query
    $data = mysqli_fetch_array($result);

    // check if a result is returned
    if(!empty($data)) {

        // means email is takne, kill script
        mysqli_close($link);
        die("emailtaken");
    }
}
else {
    // die because script faied
    die("Error connecting to database");
}

// genereate a keys for the user
$keyPair = openssl_pkey_new();

// get private key
openssl_pkey_export($keyPair, $privateKey);

// get the public key
$publicKey = openssl_pkey_get_details($keyPair);
$publicKey = $publicKey["key"];

// generate a unique salt for the user
$salt = bin2hex(random_bytes(30));

// generate a unique iv for the user
$userIV = bin2hex(random_bytes(8));

// encrypt the private key
$encryptedPrivateKey = encryptPrivateKey($privateKey, $_POST["password"], $salt, $encryptAlgorithm, $userIV);

// encrypt the password
$encryptedPassword = encryptPasswords($_POST["password"]);

// encrypt the name
$encryptedName = encryptName($_POST["name"], $publicKey, $salt);

// get an email confirmation link
$emailLink = emailConfirmationURL();

// clean up all of the data
$sanitisedName = dbEscape($link, $encryptedName);
$santisedEmail = dbEscape($link, $formattedEmail);
$sanitisedPassword = dbEscape($link, $encryptedPassword);
$sanitisedSalt = dbEscape($link, $salt);
$sanitisedUserIV = dbEscape($link, $userIV);
$sanitisedPrivateKey = dbEscape($link, $encryptedPrivateKey);
$sanitisedPublicKey = dbEscape($link, $publicKey);
$sanitisedEmailConfirmation = dbEscape($link, $emailLink);

// prepare a query to insert the new user
$query = "INSERT INTO `users` (`email`, `name`, `salt`, `passkey`, `emailConfirmation`, `publicKey`, `privateKey`, `userIV`) VALUES ('$santisedEmail', '$sanitisedName', '$sanitisedSalt', '$sanitisedPassword', '$sanitisedEmailConfirmation', '$sanitisedPublicKey', '$sanitisedPrivateKey', '$sanitisedUserIV')";

// attempt the query
if (mysqli_query($link, $query)) {
    echo "sucess";

    // send the email
    mysqli_close($link);
    die();
}
else {
    mysqli_close($link);
    die("error connecting to database!");
}
?>
