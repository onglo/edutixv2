<?php

// database credentials
$dbHost = "localhost";
$dbUserName = "root";
$dbPassword = "root";
$etonDatabase = "etonCollege";

// recpatcha secret
$recaptchaSecret = "6Lc38A8UAAAAAGtxlsslgQ6suFCT-u0xZECx00LJ";

$encryptAlgorithm = "AES-256-CBC";

// function to escape strings
function dbEscape($connection, $textToEscape) {
    $result = mysqli_real_escape_string($connection, $textToEscape);
    return $result;
}

// a function to encrypt emails
function encryptEmail($targetEmail, $algorithm) {

    // make email lowercase
    $targetEmail = strtolower($targetEmail);

    // encrypt the email and return it
    $targetEmail = openssl_encrypt($targetEmail, $algorithm, "jnD414@lktR)mYAHTlvMw3ZPS4dI9&G9GxHKHhXFCL9j!dxMi", 0, "XJnfaseIbRAtLAoE");
    return $targetEmail;
}

// function to encrypt private keys
function encryptPrivateKey($keyToEncrypt, $secret, $saltInput, $algorithm, $IV) {
    $encrypted = openssl_encrypt($keyToEncrypt.$saltInput, $algorithm, $secret, 0, $IV);
    return $encrypted;
}

// function to encrypt passwords
function encryptPasswords($target) {
    $encrypted = password_hash($target, PASSWORD_DEFAULT, ['cost' => 12]);
    return $encrypted;
}

// function to encrypt names
function encryptName($name, $publicKey, $salt) {
    openssl_public_encrypt($name.$salt, $encryptedResult, $publicKey);
    $encryptedResult = bin2hex($encryptedResult);
    return $encryptedResult;
}

// a function to generate a random string for email confirmation
function emailConfirmationURL() {
    // generate a random value
    $value = mt_rand();

    // md5 it
    $value = md5($value);

    // return this
    return $value;
}

?>
