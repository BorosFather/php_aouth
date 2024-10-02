Természetesen! Ha a Google könyvtár használata nélkül szeretnéd megvalósítani a beléptetést, akkor közvetlenül a Google OAuth 2.0 API-t kell használnod. Az alábbi példa megmutatja, hogyan tudod a Google bejelentkezést implementálni PHP-ben külső könyvtár nélkül.

### 1. **Google API Projekt Beállítása:**
A Google könyvtár nélküli használat esetén is először be kell állítanod a Google API hitelesítést:

1. Menj a [Google Cloud Console](https://console.cloud.google.com/) oldalra, és hozz létre egy új projektet.
2. Navigálj az **API & Services > Credentials** fülre.
3. Kattints a **Create Credentials** gombra, és válaszd az **OAuth client ID** opciót.
4. Hozz létre egy **Web Application** típusú kliens ID-t.
5. Állítsd be a **Redirect URI**-t, például `http://localhost/google-login.php`.
6. Jegyezd fel a **Client ID**-t és a **Client Secret**-et, amelyeket a kódban fogsz használni.

### 2. **Beléptetési Folyamat PHP-ban Külső Könyvtár Nélkül:**
Az OAuth folyamat két fő lépésből áll: **hitelesítési kérés** és **token kérés**. Az alábbi kód bemutatja, hogyan kell ezt megvalósítani:

#### `google-login.php` Fájl:

```php
<?php
session_start();

// Google OAuth 2.0 hitelesítési paraméterek
$clientID = 'A_TE_GOOGLE_CLIENT_ID';
$clientSecret = 'A_TE_GOOGLE_CLIENT_SECRET';
$redirectUri = 'http://localhost/google-login.php';

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
    $response = file_get_contents($tokenUrl, false, stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/x-www-form-urlencoded',
            'content' => http_build_query([
                'code' => $_GET['code'],
                'client_id' => $clientID,
                'client_secret' => $clientSecret,
                'redirect_uri' => $redirectUri,
                'grant_type' => 'authorization_code'
            ])
        ]
    ]));

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
```

#### HTML Rész a Bejelentkezési Gombbal:
```html
<!DOCTYPE html>
<html>
<head>
    <title>Google Belépés</title>
</head>
<body>
    <h2>Google Belépés</h2>
    <div>
        <!-- Google beléptetési gomb, ami a fenti PHP kódhoz van kötve -->
        <a href="?login=true">
            <img src="https://developers.google.com/identity/images/btn_google_signin_dark_normal_web.png" alt="Belépés Google fiókkal">
        </a>
    </div>
</body>
</html>
```

### 3. **Felhasználói Adatok Megjelenítése (`index.php`):**
Ezzel a kóddal megjelenítheted a felhasználói adatokat, miután sikeresen belépett.

#### `index.php` Fájl:
```php
<?php
session_start();
if (!isset($_SESSION['id'])) {
    header('Location: google-login.php');
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Üdvözlünk</title>
</head>
<body>
    <h2>Sikeres bejelentkezés!</h2>
    <div>
        <p><strong>Név:</strong> <?php echo $_SESSION['name']; ?></p>
        <p><strong>Email:</strong> <?php echo $_SESSION['email']; ?></p>
        <img src="<?php echo $_SESSION['picture']; ?>" alt="Profilkép">
    </div>
    <a href="logout.php">Kijelentkezés</a>
</body>
</html>
```

### 4. **Kijelentkezés (`logout.php`):**
Ez a fájl törli a session-t és kijelentkezteti a felhasználót.

```php
<?php
session_start();
session_destroy();
header('Location: google-login.php');
exit();
?>
```

### 5. **Fontos Megjegyzések:**
- Győződj meg róla, hogy a `http://localhost/google-login.php` URL hozzá van adva a **Google Cloud Console** Redirect URI listájához.
- Használj HTTPS-t a hitelesítési folyamat során, ha éles környezetben dolgozol.
- A `file_get_contents` helyett használhatod a `cURL` függvényt is a biztonságosabb HTTP kérésekhez, ha szeretnéd.

Ezzel a módszerrel PHP könyvtárak használata nélkül valósíthatod meg a Google bejelentkezést. Ha kérdésed van, vagy elakadtál, szólj bátran! 😊