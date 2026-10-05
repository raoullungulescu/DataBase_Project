<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Verifică dacă utilizatorul este autentificat
if (!isset($_SESSION["utilizatori"])) {
    die("Nu sunteți autentificat. Vă rugăm să vă logați.");
}

// Funcție pentru a stabili conexiunea la baza de date
function getDbConnection() {
    $servername = (require __DIR__ . '/config.php')['host'];
    $username = (require __DIR__ . '/config.php')['user'];
    $password = (require __DIR__ . '/config.php')['pass'];
    $database = (require __DIR__ . '/config.php')['db'];
    $conn = new mysqli($servername, $username, $password, $database);

    if ($conn->connect_error) {
        die("Conexiune eșuată: " . $conn->connect_error);
    }
    return $conn;
}

// Gestionează cererea de cumpărare a unei mașini
if (isset($_POST['cumpara'])) {
    $id_masina = $_POST['id_masina'];
    $pret = $_POST['pret'];
    $metoda_plata = $_POST['metoda_plata'];
    
    // Verifică dacă sunt toate datele necesare
    if (empty($id_masina) || empty($pret) || empty($metoda_plata)) {
        die("Te rugăm să completezi toate câmpurile!");
    }
    
    $conn = getDbConnection();
    
    // Obține tranzacția clientului
    $nume_client = $_SESSION["utilizatori"];
    $sql = "
    SELECT t.id_tranzactie
    FROM tranzactii t
    WHERE t.id_client = (
        SELECT c.id_client
        FROM clienti c
        WHERE c.nume_client = ?
    );
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $nume_client);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        // Crează tranzacția dacă nu există
        $sql_client = "SELECT id_client FROM clienti WHERE nume_client = ?";
        $stmt_client = $conn->prepare($sql_client);
        $stmt_client->bind_param("s", $nume_client);
        $stmt_client->execute();
        $client_result = $stmt_client->get_result();
        $client_row = $client_result->fetch_assoc();
        if (!$client_row) { die("Nu exista client asociat utilizatorului in tabela clienti."); }
        $id_client = $client_row['id_client'];

        // Crează tranzacția
        $sql_tranzactie = "INSERT INTO tranzactii (id_client, data_vanzare, pret, metoda_plata) VALUES (?, NOW(), 0, 'cash')";
        $stmt_tranzactie = $conn->prepare($sql_tranzactie);
        $stmt_tranzactie->bind_param("i", $id_client);
        $stmt_tranzactie->execute();
        $id_tranzactie = $stmt_tranzactie->insert_id;
    } else {
        $row = $result->fetch_assoc();
        $id_tranzactie = $row['id_tranzactie'];
    }

    // Actualizează tranzacția
    $sql_tranzactie_update = "UPDATE tranzactii SET pret = ?, metoda_plata = ? WHERE id_tranzactie = ?";
    $stmt_tranzactie_update = $conn->prepare($sql_tranzactie_update);
    $stmt_tranzactie_update->bind_param("dsi", $pret, $metoda_plata, $id_tranzactie);
    $stmt_tranzactie_update->execute();

    // Actualizează tabelul masini
    $sql_update_masina = "UPDATE masini SET id_tranzactie = ? WHERE id_masina = ?";
    $stmt_update = $conn->prepare($sql_update_masina);
    $stmt_update->bind_param("ii", $id_tranzactie, $id_masina);
    $stmt_update->execute();

    // Redirecționează pagina după completarea cererii
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Gestionează cererea de ștergere a unei mașini din coș
if (isset($_POST['sterge'])) {
    $id_masina = $_POST['id_masina'];

    $conn = getDbConnection();
    
    // Șterge mașina din tranzacție
    $sql_sterge_masina = "UPDATE masini SET id_tranzactie = NULL WHERE id_masina = ?";
    $stmt_sterge = $conn->prepare($sql_sterge_masina);
    $stmt_sterge->bind_param("i", $id_masina);
    $stmt_sterge->execute();
    
    // Redirecționează pagina după ștergerea mașinii
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Închide conexiunea la baza de date
function closeConnection($conn) {
    $conn->close();
}

$conn = getDbConnection();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RAY CARS - Lista Mașinilor Disponibile</title>
    <link href="img/favicon.ico" rel="icon">
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;500;600;700&family=Rubik&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.0/css/all.min.css" rel="stylesheet">
    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">
    <link href="lib/tempusdominus/css/tempusdominus-bootstrap-4.min.css" rel="stylesheet" />
    <link href="bootstrap.min.css" rel="stylesheet">
    <link href="test.css" rel="stylesheet"> 
    <script src="java.js" defer></script>
</head>

<body>
   <!-- Navbar Start -->
   <div class="container-fluid position-relative nav-bar p-0">
        <div class="position-relative px-lg-5" style="z-index: 9;">
            <nav class="navbar navbar-expand-lg bg-secondary navbar-dark py-3 py-lg-0 pl-3 pl-lg-5">
              
                <a href="home.php" class="navbar-brand">
                    <h1 class="text-uppercase text-primary mb-1">RAY Cars</h1>
                </a>
               
                <button type="button" class="navbar-toggler" data-toggle="collapse" data-target="#navbarCollapse">
                    <span class="navbar-toggler-icon"></span>
                </button>
                
                <div class="collapse navbar-collapse justify-content-between px-3" id="navbarCollapse">
                    <div class="navbar-nav ml-auto py-0">
                        
                        <a href="home.php" class="nav-item nav-link">Home</a>
                        <a href="login.php" class="nav-item nav-link">Login și Register</a>
                        <a href="masini.php" class="nav-item nav-link">Adaugă Mașină</a>

                        <span class="nav-item nav-link">
                            <i class="fas fa-shopping-bag"></i>
                        </span>
                        <a href="cumpara.php" class="nav-item nav-link">Shop</a>

                        <span class="nav-item nav-link">
                        <i class="fas fa-shopping-cart"></i> 
                        </span>

                        <a href="shop.php" class="nav-item nav-link">Coș</a>
                        <a href="contact.php" class="nav-item nav-link">Contact</a>
                    </div>
                </div>
            </nav>
        </div>
    </div>
<!-- Navbar End -->



<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8">
            <div class="d-flex justify-content-between align-items-center bg-light p-3 rounded shadow">
                <div class="step text-center flex-fill position-relative">
                    <div class="btn btn-secondary rounded-circle mb-2" id="step1">1</div>
                    <span>Coșul meu</span>
                </div>
                <div class="step text-center flex-fill position-relative">
                    <div class="btn btn-secondary rounded-circle mb-2" id="step2">2</div>
                    <span>Detalii comandă</span>
                </div>
                <div class="step text-center flex-fill position-relative">
                    <div class="btn btn-secondary rounded-circle mb-2" id="step3">3</div>
                    <span>Sumar comandă</span>
                </div>
            </div>
        </div>
    </div>
</div>

    <!-- Cart Section Start -->
    <div class="container py-5">
        <h2 class="text-center mb-4">Coșul tău de cumpărături</h2>
        <div class="card mb-4">
            <div class="card-header">Produse adăugate</div>
            <div class="card-body">
                <?php
                // Conectare la baza de date
                $conn = getDbConnection();

                $nume_client = $_SESSION["utilizatori"];

                // Obține tranzacția clientului
                $sql = "SELECT t.id_tranzactie
                        FROM tranzactii t
                        JOIN clienti c ON t.id_client = c.id_client
                        WHERE c.nume_client = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("s", $nume_client);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows === 0) {
                    // Crează tranzacția dacă nu există
                    $sql_client = "SELECT id_client FROM clienti WHERE nume_client = ?";
                    $stmt_client = $conn->prepare($sql_client);
                    $stmt_client->bind_param("s", $nume_client);
                    $stmt_client->execute();
                    $client_result = $stmt_client->get_result();
                    $client_row = $client_result->fetch_assoc();
                    if (!$client_row) { die("Nu exista client asociat utilizatorului in tabela clienti."); }
                    $id_client = $client_row['id_client'];

                    $sql_tranzactie = "INSERT INTO tranzactii (id_client, data_vanzare, pret, metoda_plata) VALUES (?, NOW(), 0, 'cash')";
                    $stmt_tranzactie = $conn->prepare($sql_tranzactie);
                    $stmt_tranzactie->bind_param("i", $id_client);
                    $stmt_tranzactie->execute();
                    $id_tranzactie = $stmt_tranzactie->insert_id;
                } else {
                    $row = $result->fetch_assoc();
                    $id_tranzactie = $row['id_tranzactie'];
                }

                // Array cu imagini corespunzătoare fiecărei mărci
                $imagini = [
                    'ford' => 'car-rent-2.png',
                    'toyota' => 'car-rent-3.png',
                    'audi' => 'car-rent-4.png',
                    'bmw' => 'car-rent-5.png',
                    'mercedes-benz' => 'car-rent-6.png',
                    'volkswagen' => 'car-rent-6.png',
                    'skoda' => 'scar-rent-4.png',
                    'chevrolet' => 'car-rent-2.png',
                    'honda' => 'car-rent-2.png',
                    'nissan' => 'car-rent-2.png',
                ];

                // Obține mașinile din tranzacție
                $sql_masini = "SELECT m.id_masina, m.Model, m.Marca, m.Pret_masina FROM masini m WHERE m.id_tranzactie = ?";
                $stmt_masini = $conn->prepare($sql_masini);
                $stmt_masini->bind_param("i", $id_tranzactie);
                $stmt_masini->execute();
                $masini_result = $stmt_masini->get_result();

                $total_produs = 0;

                if ($masini_result->num_rows > 0) {
                    echo "<table class='table'>
                            <thead>
                                <tr>
                                    <th>Imagine</th>
                                    <th>Model</th>
                                    <th>Marca</th>
                                    <th>Preț</th>
                                    <th>Acțiuni</th>
                                </tr>
                            </thead>
                            <tbody>";
                    while ($masina = $masini_result->fetch_assoc()) {
                        $total_produs += $masina['Pret_masina'];
                        $marca = strtolower($masina['Marca']);
                        $imagine = isset($imagini[$marca]) ? $imagini[$marca] : 'default-image.png';

                        echo "<tr>
                                <td><img src='$imagine' alt='Car Image' style='width: 100px; height: auto;'></td>
                                <td>" . htmlspecialchars($masina['Model']) . "</td>
                                <td>" . htmlspecialchars($masina['Marca']) . "</td>
                                <td class='price'>" . htmlspecialchars($masina['Pret_masina']) . " EUR</td>
                                <td>
                                    <form method='POST' style='display:inline;'>
                                        <input type='hidden' name='id_masina' value='" . $masina['id_masina'] . "'>
                                        <button type='submit' name='sterge' value='sterge' class='btn btn-danger btn-sm'>Șterge</button>
                                    </form>
                                </td>
                              </tr>";
                    }
                    echo "</tbody>
                        </table>";
                } else {
                    echo "<p>Nu există mașini asociate tranzacției dumneavoastră.</p>";
                }

                $taxe = $total_produs * 0.19;
                $voucher = $total_produs * 0.1;
                $total_final = $total_produs + $taxe - $voucher;

                closeConnection($conn);
                ?>
            </div>
        </div>

        <div class="card">
            <div class="card-header">Rezumat comandă</div>
            <div class="card-body">
                <table class="table">
                    <tbody>
                        <tr>
                            <td><strong>Produse</strong></td>
                            <td style="text-align: right;"><?php echo number_format($total_produs, 2); ?> EUR</td>
                        </tr>
                        <tr>
                            <td><strong>TVA (19%)</strong></td>
                            <td style="text-align: right;"><?php echo number_format($taxe, 2); ?> EUR</td>
                        </tr>
                        <tr>
                            <td><strong>Voucher_Start</strong></td>
                            <td style="text-align: right;"><?php echo number_format($voucher, 2); ?> EUR</td>
                        </tr>
                        <tr>
                            <td><strong>Total final</strong></td>
                            <td style="text-align: right;"><?php echo number_format($total_final, 2); ?> EUR</td>
                        </tr>
                    </tbody>
                </table>
                <form action="shoptT.php" method="POST">
                <button type="submit" class="btn btn-primary btn-block">Finalizează comanda</button>
                </form>

            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="lib/owlcarousel/owl.carousel.min.js"></script>
    <script src="lib/tempusdominus/js/moment.min.js"></script>
    <script src="lib/tempusdominus/js/tempusdominus-bootstrap-4.min.js"></script>
    <script src="js/bootstrap.bundle.min.js"></script>

    <div class="row justify-content-center mt-4">
    <div class="col-12 col-md-8 text-center">
        <p id="step-content">Acesta este pasul 1: Coșul meu.</p>
        
        <!-- Butoane plasate unul lângă altul -->
        <div class="d-flex justify-content-center">
            <!-- Buton Înapoi (nu face nimic pe shop.php, deci este dezactivat) -->
            <button class="btn btn-outline-primary me-2" id="prev-btn" disabled>Înapoi</button>

            <!-- Formular pentru butonul Înainte (duce la shoptT.php) -->
            <form action="shoptT.php" method="POST">
                <button type="submit" class="btn btn-primary" id="next-btn">Înainte</button>
            </form>
        </div>
    </div>
</div>
        <br>
</body>
</html>
