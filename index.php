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