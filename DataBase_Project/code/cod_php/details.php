<?php
session_start();

// Verifică dacă utilizatorul este autentificat
if (!isset($_SESSION["utilizatori"])) {
    header("Location: login.php");
    exit;
}

// Verifică dacă parametrul 'id' este trecut în URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "ID-ul mașinii nu este valid.";
    exit;
}

$id_masina = (int)$_GET['id'];

// Detalii conexiune la baza de date
$servername = (require __DIR__ . '/config.php')['host'];
$username = (require __DIR__ . '/config.php')['user'];
$password = (require __DIR__ . '/config.php')['pass'];
$database = (require __DIR__ . '/config.php')['db'];

// Conectare la baza de date
$conn = new mysqli($servername, $username, $password, $database);

// Verifică dacă conexiunea este reușită
if ($conn->connect_error) {
    die("Conexiune eșuată: " . $conn->connect_error);
}

// Definește calea pentru imaginile asociate fiecărei mărci
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

// Calea imaginii implicite
$default_image = 'default-car.png';  // Schimbă cu imaginea ta implicită

// Interogarea pentru a obține detaliile mașinii
$sql_masina = "
SELECT m.id_masina, 
       m.Marca, 
       m.Model, 
       m.An_fabricatie, 
       m.Tip_Motor, 
       m.Putere_Motor, 
       m.Culoare, 
       m.Kilometraj, 
       m.Disponibilitate, 
       m.Pret_masina, 
       (SELECT f.nume_furnizor FROM Furnizor f WHERE f.id_furnizor = m.id_furnizor) AS nume_furnizor, 
       (SELECT f.email FROM Furnizor f WHERE f.id_furnizor = m.id_furnizor) AS email
FROM masini m
WHERE m.id_masina = ?;
";


// Pregătește interogarea SQL
$stmt_masina = $conn->prepare($sql_masina);
$stmt_masina->bind_param("i", $id_masina);
$stmt_masina->execute();
$result_masina = $stmt_masina->get_result();

// Verifică dacă s-au găsit detalii pentru mașina respectivă
if ($result_masina->num_rows == 0) {
    echo "Mașina nu a fost găsită.";
    exit;
}

// Obține detaliile mașinii
$detalii_masina = $result_masina->fetch_assoc();

// Interogarea pentru a obține dotările asociate mașinii
$sql_dotari = "
SELECT d.nume_dotare
FROM dotari d
WHERE d.id_dotari IN (
    SELECT md.id_dotari
    FROM masina_dotari md
    WHERE md.id_masina = ?
);
";

// Pregătește interogarea pentru dotări
$stmt_dotari = $conn->prepare($sql_dotari);
$stmt_dotari->bind_param("i", $id_masina);
$stmt_dotari->execute();
$result_dotari = $stmt_dotari->get_result();

// Închide conexiunea la baza de date
$conn->close();

// Obține marca mașinii
$marca = strtolower($detalii_masina['Marca']); // Folosim marca în litere mici
$imagePath = isset($imagini[$marca]) ? $imagini[$marca] : $default_image;
?>

<!DOCTYPE html>
<html lang="ro">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RAY CARS - Detalii Mașină</title>
    <link href="img/favicon.ico" rel="icon">
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;500;600;700&family=Rubik&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.0/css/all.min.css" rel="stylesheet">
    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">
    <link href="lib/tempusdominus/css/tempusdominus-bootstrap-4.min.css" rel="stylesheet" />
    <link href="bootstrap.min.css" rel="stylesheet">
    <link href="test.css" rel="stylesheet">
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
                        <a href="contact.html" class="nav-item nav-link">Contact</a>
                    </div>
                </div>
            </nav>
        </div>
    </div>
<!-- Navbar End -->

    <!-- Detalii Mașină Start -->
    <div class="container-fluid pt-5">
        <div class="container pt-5 pb-3">
            <h1 class="display-4 text-uppercase mb-5"><?php echo htmlspecialchars($detalii_masina['Marca']) . ' ' . htmlspecialchars($detalii_masina['Model']); ?></h1>
            <div class="row align-items-center pb-2">
                <div class="col-lg-6 mb-4">
                    <!-- Afișează imaginea mașinii -->
                    <img src="<?php echo $imagePath; ?>" alt="Imagine masina" class="img-fluid w-100">
                </div>
                <div class="col-lg-6 mb-4">
                    <h4 class="mb-2"><?php echo htmlspecialchars($detalii_masina['Pret_masina']); ?> RON</h4>
                    <div class="d-flex mb-3">
                        <div class="d-flex align-items-center justify-content-center mb-1">
                        </div>
                    </div>
                    <p>
                        <strong style="font-size: 24px; font-weight: bold;">Detalii Mașină:</strong><br>
                        <strong>Marca:</strong> <?php echo htmlspecialchars($detalii_masina['Marca']); ?><br>
                        <strong>Model:</strong> <?php echo htmlspecialchars($detalii_masina['Model']); ?><br>
                        <strong>Anul fabricației:</strong> <?php echo htmlspecialchars($detalii_masina['An_fabricatie']); ?><br>
                        <strong>Tip Motor:</strong> <?php echo htmlspecialchars($detalii_masina['Tip_Motor']); ?><br>
                        <strong>Putere Motor:</strong> <?php echo htmlspecialchars($detalii_masina['Putere_Motor']); ?> CP<br>
                        <strong>Culoare:</strong> <?php echo htmlspecialchars($detalii_masina['Culoare']); ?><br>
                        <strong>Kilometraj:</strong> <?php echo htmlspecialchars($detalii_masina['Kilometraj']); ?> km<br>
                        <strong>Preț:</strong> <?php echo htmlspecialchars($detalii_masina['Pret_masina']); ?> RON<br>
                        <strong>Disponibilitate:</strong> <?php echo $detalii_masina['Disponibilitate'] ? 'Disponibilă' : 'Indisponibilă'; ?><br>
                        <strong>Furnizor:</strong> <?php echo $detalii_masina['nume_furnizor'] ?: 'N/A'; ?><br>
                        <strong>Email furnizor:</strong> <?php echo $detalii_masina['email'] ?: 'N/A'; ?><br>
                    </p>
                </div>
            </div>
            <div class="row mt-n3 mt-lg-0 pb-4">
                <div class="col-md-3 col-6 mb-2">
                    <i class="fa fa-car text-primary mr-2"></i>
                    <span>Model: <?php echo htmlspecialchars($detalii_masina['Model']); ?> - <?php echo htmlspecialchars($detalii_masina['An_fabricatie']); ?></span>
                </div>
                <div class="col-md-3 col-6 mb-2">
                    <i class="fa fa-cogs text-primary mr-2"></i>
                    <span><?php echo htmlspecialchars($detalii_masina['Tip_Motor']); ?></span>
                </div>
                <div class="col-md-3 col-6 mb-2">
                    <i class="fa fa-road text-primary mr-2"></i>
                    <span><?php echo htmlspecialchars($detalii_masina['Kilometraj']); ?> km/liter</span>
                </div>
                <div class="col-md-3 col-6 mb-2">
                    <i class="fa fa-eye text-primary mr-2"></i>
                    <span>
                        <?php
                        // Verifică dacă există dotări și afișează-le
                        if ($result_dotari->num_rows > 0) {
                            $dotari = [];
                            // Parcurge rezultatele dotărilor și le adaugă într-un array
                            while ($dotare = $result_dotari->fetch_assoc()) {
                                $dotari[] = htmlspecialchars($dotare['nume_dotare']);
                            }
                            // Afișează dotările
                            echo implode(", ", $dotari);
                        } else {
                            echo "Nu există dotări";
                        }
                        ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Înapoi la lista de mașini -->

<a href="home.php" class="btn btn-primary btn-lg">Înapoi la lista de mașini</a>



    

</body>
</html>
