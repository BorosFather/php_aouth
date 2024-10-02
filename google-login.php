<?php
session_start();

// Google OAuth 2.0 hitelesítési paraméterek
$clientID = 'id';
$clientSecret = 'secret';
$redirectUri = 'http://localhost/csakphp/google-login.php';

// Alapértelmezett Google OAuth 2.0 URL-ek
$authUrl = "https://accounts.google.com/o/oauth2/v2/auth";
$tokenUrl = "https://oauth2.googleapis.com/token";
$userInfoUrl = "https://www.googleapis.com/oauth2/v3/userinfo";

// Ha nincs "code" paraméter, akkor irányítson át a Google OAuth bejelentkezési oldalra
if (!isset($_GET['code'])) {
    // Hitelesítési URL létrehozása
    $authRequestUrl = $authUrl . '?' . http_build_query([
        'response_type' => 'code',
        'client_id' => $clientID,
        'redirect_uri' => $redirectUri,
        'scope' => 'openid email profile',
        'access_type' => 'offline',
        'prompt' => 'consent'
    ]);

    // Átirányítás a Google bejelentkezési oldalra
    header('Location: ' . $authRequestUrl);
    exit();
}

// Ha "code" paraméter van az URL-ben, akkor szerezzük meg a hozzáférési tokent
if (isset($_GET['code'])) {
    // Cseréljük ki a hitelesítési kódot hozzáférési tokenre
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $tokenUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'code' => $_GET['code'],
        'client_id' => $clientID,
        'client_secret' => $clientSecret,
        'redirect_uri' => $redirectUri,
        'grant_type' => 'authorization_code'
    ]));

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
    
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        // Hiba kezelése
        echo 'Curl error: ' . curl_error($ch);
        exit;
    }
    curl_close($ch);
    
    $token = json_decode($response, true);
    if (!isset($token['access_token'])) {
        die('Token nem jött létre! Hiba: ' . json_encode($token));
    }
    

    // A JSON választ feldolgozzuk
    $token = json_decode($response, true);

    // Ha van hozzáférési token, akkor lekérjük a felhasználói információkat
    if (isset($token['access_token'])) {
        // Felhasználói adatok lekérése a hozzáférési tokennel
        $userInfoResponse = file_get_contents($userInfoUrl . '?' . http_build_query([
            'access_token' => $token['access_token']
        ]));

        // Felhasználói adatok feldolgozása
        $userInfo = json_decode($userInfoResponse, true);

        // Session változók beállítása
        $_SESSION['id'] = $userInfo['sub'];
        $_SESSION['email'] = $userInfo['email'];
        $_SESSION['name'] = $userInfo['name'];
        $_SESSION['picture'] = $userInfo['picture'];

        // Átirányítás a felhasználói oldalra
        header('Location: index.php');
        exit();
    }
}
?>