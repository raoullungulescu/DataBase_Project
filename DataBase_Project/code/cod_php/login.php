<?php
session_start();

// Configurația bazei de date
$servername = (require __DIR__ . '/config.php')['host'];
$username = (require __DIR__ . '/config.php')['user'];
$password = (require __DIR__ . '/config.php')['pass'];
$database = (require __DIR__ . '/config.php')['db'];

// Crearea conexiunii la baza de date
$conn = new mysqli($servername, $username, $password, $database);

// Verificarea conexiunii
if ($conn->connect_error) {
    die("Eroare de conexiune: " . $conn->connect_error);
}

// Verificăm dacă formularul de autentificare a fost trimis
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["action"]) && $_POST["action"] == "login") {
    $user = trim($_POST["username"]); // Elimină spațiile albe
    $pass = trim($_POST["password"]); // Elimină spațiile albe

    // Verificăm în baza de date
    $sql = "SELECT id, role FROM utilizatori WHERE username = ? AND parola = SHA2(?, 256)";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ss", $user, $pass);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            // Autentificare reușită, preluăm rolul utilizatorului
            $stmt->bind_result($id, $role);
            $stmt->fetch();

            // Setăm variabilele de sesiune
            $_SESSION["utilizatori"] = $user;
            // Creeaza clientul asociat daca nu exista
            $chk_cl = $conn->prepare("SELECT id_client FROM clienti WHERE nume_client = ?");
            $chk_cl->bind_param("s", $user);
            $chk_cl->execute();
            if ($chk_cl->get_result()->num_rows === 0) {
                $ins_cl = $conn->prepare("INSERT INTO clienti (nume_client, prenume_client, tip_client) VALUES (?, '-', 'privat')");
                $ins_cl->bind_param("s", $user);
                $ins_cl->execute();
            }
            $_SESSION["role"] = $role;  // Salvăm rolul utilizatorului în sesiune

            // Redirecționăm utilizatorul către pagina principală
            header("Location: home.php");
            exit;
        } else {
            $eroare = "Utilizator sau parolă incorecte.";
        }

        $stmt->close();
    } else {
        $eroare = "Eroare la interogarea bazei de date.";
    }
}

// Verificăm dacă formularul de înregistrare a fost trimis
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["action"]) && $_POST["action"] == "register") {
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);
    $repeatPassword = trim($_POST["repeat_password"]);

    if ($password === $repeatPassword) {
        $hashedPassword = hash("sha256", $password);
        $sql = "INSERT INTO utilizatori (username, parola) VALUES (?, ?)";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ss", $username, $hashedPassword);
            if ($stmt->execute()) {
                $success = "Înregistrare reușită. Puteți să vă autentificați acum.";
            } else {
                $eroare = "Eroare la înregistrare, încercați din nou.";
            }
            $stmt->close();
        } else {
            $eroare = "Eroare la interogarea bazei de date.";
        }
    } else {
        $eroare = "Parolele nu se potrivesc.";
    }
}

// Verificăm dacă formularul de ștergere a unui utilizator a fost trimis
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["action"]) && $_POST["action"] == "delete") {
    $deleteUser = trim($_POST["delete_username"]);

    $sql = "DELETE FROM utilizatori WHERE username = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $deleteUser);
        if ($stmt->execute()) {
            $success = "Utilizatorul a fost șters cu succes.";
        } else {
            $eroare = "Eroare la ștergerea utilizatorului.";
        }
        $stmt->close();
    } else {
        $eroare = "Eroare la interogarea bazei de date.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Login Dealer Auto</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<div class="login-wrap">
    <div class="login-html">
        <?php if (isset($eroare)) echo "<p style='color:red;'>$eroare</p>"; ?>
        <?php if (isset($success)) echo "<p style='color:green;'>$success</p>"; ?>
        <input id="tab-1" type="radio" name="tab" class="sign-in" checked><label for="tab-1" class="tab">Sign In</label>
        <input id="tab-2" type="radio" name="tab" class="sign-up"><label for="tab-2" class="tab">Sign Up</label>

        <div class="login-form">
            <!-- Formular Sign In -->
            <div class="sign-in-htm">
                <form action="" method="POST">
                    <div class="group">
                        <label for="user" class="label">Username</label>
                        <input id="user" type="text" class="input" name="username" required>
                    </div>
                    <div class="group">
                        <label for="pass" class="label">Password</label>
                        <input id="pass" type="password" class="input" data-type="password" name="password" required>
                    </div>
                    <div class="group">
                        <input id="check" type="checkbox" class="check" checked>
                        <label for="check"><span class="icon"></span> Keep me Signed in</label>
                    </div>
                    <div class="group">
                        <input type="hidden" name="action" value="login">
                        <input type="submit" class="button" value="Sign In">
                    </div>
                    <div class="hr"></div>
                </form>
            </div>

            <!-- Formular Sign Up -->
            <div class="sign-up-htm">
                <form action="" method="POST">
                    <div class="group">
                        <label for="user" class="label">Username</label>
                        <input id="user" type="text" class="input" name="username" required>
                    </div>
                    <div class="group">
                        <label for="pass" class="label">Password</label>
                        <input id="pass" type="password" class="input" data-type="password" name="password" required>
                    </div>
                    <div class="group">
                        <label for="repeat_pass" class="label">Repeat Password</label>
                        <input id="repeat_pass" type="password" class="input" data-type="password" name="repeat_password" required>
                    </div>
                    <div class="group">
                        <input type="hidden" name="action" value="register">
                        <input type="submit" class="button" value="Sign Up">
                    </div>
                    <div class="hr"></div>
                    <div class="foot-lnk"><br><br>
                        <label for="tab-1">Already Member?</label><br><br>
                    </div>
                </form>
            </div>
        </div>
        <br>

        <!-- Formular pentru Ștergere utilizator -->
        <div class="delete-user">
            <form action="" method="POST">
                <div class="group">
                    <label for="delete_user" class="label">Username de șters</label>
                    <input id="delete_user" type="text" class="input" name="delete_username" required>
                </div>
                <div class="group">
                    <input type="hidden" name="action" value="delete">
                    <input type="submit" class="button" value="Delete User">
                </div>
            </form>
        </div>

    </div>
</div>

</body>
</html>
