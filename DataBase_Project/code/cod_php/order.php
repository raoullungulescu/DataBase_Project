<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Funcția pentru conexiunea la baza de date
function getDbConnection() {
    $servername = (require __DIR__ . '/config.php')['host'];
    $username = (require __DIR__ . '/config.php')['user'];
    $password = (require __DIR__ . '/config.php')['pass'];
    $database = (require __DIR__ . '/config.php')['db'];

    // Crearea conexiunii
    $conn = new mysqli($servername, $username, $password, $database);

    // Verificarea conexiunii
    if ($conn->connect_error) {
        die("Conexiune eșuată: " . $conn->connect_error);
    }

    return $conn;
}

// Conectare la baza de date
$conn = getDbConnection();

$nume_client = $_SESSION["utilizatori"] ?? '';  // Asumăm că numele clientului este stocat în sesiune

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

// Obține tranzacția clientului
$sql = "
SELECT t.id_tranzactie, 
       t.data_vanzare, 
       t.pret, 
       t.metoda_plata, 
       (SELECT c.nume_client 
        FROM clienti c 
        WHERE c.id_client = t.id_client) AS nume_client, 
       (SELECT c.adresa 
        FROM clienti c 
        WHERE c.id_client = t.id_client) AS adresa
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


// Verifică dacă metoda de livrare a fost aleasă în formularul anterior
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Salvează metoda de livrare în sesiune
    $_SESSION['delivery_method'] = $_POST['delivery_method'];
}

// Obține metoda de livrare din sesiune
$delivery_method = $_SESSION['delivery_method'] ?? '';

// Obține detaliile clientului din baza de date
if ($delivery_method == 'courier') {
    // Asigură-te că ai conectat corect la baza de date
    $sql = "SELECT adresa, telefon_client, email FROM clienti WHERE nume_client = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $nume_client);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Verifică dacă există un rezultat
    if ($row = $result->fetch_assoc()) {
        $client_address = $row['adresa'];
        $client_phone = $row['telefon_client'];
        $client_email = $row['email'];
    }
}
$conn->close();

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
                        <div class="btn btn-primary rounded-circle mb-2">1</div>
                        <span>Coșul meu</span>
                    </div>
                    <div class="step text-center flex-fill position-relative">
                        <div class="btn btn-secondary rounded-circle mb-2">2</div>
                        <span>Detalii comandă</span>
                    </div>
                    <div class="step text-center flex-fill position-relative">
                        <div class="btn btn-secondary rounded-circle mb-2">3</div>
                        <span>Sumar comandă</span>
                    </div>
                </div>
            </div>
        </div>
        <br><br><br><br>

        <!-- Main Content -->
        <div class="container">
            <div class="row">
                <!-- Left Column: Sumar produse -->
                <div class="col-lg-8">
                    <div class="card-header">Produse adăugate</div>
                    <div class="card-body">
                        <?php
                        if ($masini_result->num_rows > 0) {
                            echo "<table class='table'>
                                    <thead>
                                        <tr>
                                            <th>Imagine</th>
                                            <th>Model</th>
                                            <th>Marca</th>
                                            <th>Preț</th>
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
                                    </tr>";
                            }
                            echo "</tbody>
                                </table>";
                        } else {
                            echo "<p>Nu există mașini asociate tranzacției dumneavoastră.</p>";
                        }
                        ?>
                    </div>
                </div>

                <!-- Right Column: Detalii comandă -->
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Detalii comandă</h5>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Subtotal</span>
                                    <span><?php echo number_format($total_produs, 2); ?> EUR</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Cost livrare</span>
                                    <span>20 EUR</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between fw-bold">
                                    <span>Total</span>
                                    <span><?php echo number_format($total_produs + 20, 2); ?> EUR</span>
                                </li>
                            </ul>
                            <div class="d-grid mt-3">
                                <a href="confirmare-comanda.php" class="btn btn-primary">Plasează comanda</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="container py-5">
                            <!-- Date facturare -->
                            <h2 class="text-start mb-4"> Date facturare</h2> 
                            <?php if ($result->num_rows > 0): ?>
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Informații Tranzacție</h5>
                                    </div>
                                    <div class="card-body">
                                        <table class="table">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Nume Client</th>
                                                    <th>Adresa</th>
                                                    <th>Data Vânzării</th>
                                                    <th>Preț</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php while ($row = $result->fetch_assoc()): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($row['nume_client']); ?></td>
                                                        <td><?php echo htmlspecialchars($row['adresa']); ?></td>
                                                        <td><?php echo htmlspecialchars($row['data_vanzare']); ?></td>
                                                        <td><?php echo htmlspecialchars($row['pret']) . " EUR"; ?></td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-warning" role="alert">
                                    Nu există tranzacții pentru acest client.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Metoda de livrare</h5>
            <?php if ($delivery_method == 'courier'): ?>
                <p><strong>Livrare prin curier:</strong></p>
                <p>Adresa de livrare: <?php echo htmlspecialchars($client_address); ?></p>
                <p>Telefon: <?php echo htmlspecialchars($client_phone); ?></p>
                <p>Email: <?php echo htmlspecialchars($client_email); ?></p>
            <?php elseif ($delivery_method == 'pickup'): ?>
                <p><strong>Ridicare personală:</strong></p>
                <p>Orașe disponibile pentru ridicare personală:</p>
                <ul>
                    <li>București</li>
                    <li>Cluj-Napoca</li>
                    <li>Iași</li>
                    <!-- Poți adăuga mai multe orașe aici -->
                </ul>
            <?php else: ?>
                <p>Nu a fost aleasă nicio metodă de livrare.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
</div>
        <br><br>

        <div class="row justify-content-center mt-4">
            <div class="col-12 col-md-8 text-center">
                <p id="step-content">Acesta este pasul 2: Detalii livrare.</p>
                <!-- Butoane plasate unul lângă altul -->
                <div class="d-flex justify-content-center">
                    <!-- Formular pentru butonul Înapoi (duce la shop.php) -->
                    <form action="shoptT.php" method="POST">
                        <button type="submit" class="btn btn-outline-primary me-2" id="prev-btn">Înapoi</button>
                    </form>

                    <!-- Formular pentru butonul Înainte (duce la comanda.php) -->
                    <form action="comanda.php" method="POST">
                        <button type="submit" class="btn btn-primary" id="next-btn">Înainte</button>
                    </form>
                </div>
            </div>
        </div>



        <!-- Bootstrap JS -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
