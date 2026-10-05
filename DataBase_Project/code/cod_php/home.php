<?php
session_start();

// Verifică dacă utilizatorul este autentificat
if (!isset($_SESSION["utilizatori"])) {
    header("Location: login.php");
    exit;
}

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

// Obține filtrele din URL și protejează împotriva atacurilor SQL Injection
$marca_filter = isset($_GET['marca']) ? mysqli_real_escape_string($conn, $_GET['marca']) : '';
$pret_min_filter = isset($_GET['pret_min']) ? (int)$_GET['pret_min'] : '';
$pret_max_filter = isset($_GET['pret_max']) ? (int)$_GET['pret_max'] : '';
$disponibilitate_filter = isset($_GET['disponibilitate']) ? (int)$_GET['disponibilitate'] : '';

// Construiește interogarea SQL cu filtre
$sql_masini = "SELECT m.id_masina, m.Marca, m.Model, m.An_fabricatie, m.Pret_masina, m.Disponibilitate, m.Kilometraj, m.Tip_motor, f.nume_furnizor
               FROM masini m
               LEFT JOIN Furnizor f ON m.id_furnizor = f.id_furnizor
               WHERE 1";

// Aplică filtrele
if ($marca_filter) {
    $sql_masini .= " AND m.Marca LIKE ?";
}
if ($pret_min_filter) {
    $sql_masini .= " AND m.Pret_masina >= ?";
}
if ($pret_max_filter) {
    $sql_masini .= " AND m.Pret_masina <= ?";
}
if ($disponibilitate_filter !== '') {
    $sql_masini .= " AND m.Disponibilitate = ?";
}

// Pregătește interogarea SQL
$stmt = $conn->prepare($sql_masini);

// Legarea parametrilor pentru interogare
$param_types = "";
$params = [];

if ($marca_filter) {
    $param_types .= "s";  // tipul pentru string
    $params[] = "%$marca_filter%";
}
if ($pret_min_filter) {
    $param_types .= "i";  // tipul pentru int
    $params[] = $pret_min_filter;
}
if ($pret_max_filter) {
    $param_types .= "i";  // tipul pentru int
    $params[] = $pret_max_filter;
}
if ($disponibilitate_filter !== '') {
    $param_types .= "i";  // tipul pentru int
    $params[] = $disponibilitate_filter;
}

// Leagă parametrii și execută interogarea
if ($param_types) {
    $stmt->bind_param($param_types, ...$params);
}

$stmt->execute();
$result_masini = $stmt->get_result();

// Obține lista de mărci pentru formular
$result_marci = $conn->query("SELECT DISTINCT Marca FROM masini");

// Definirea unui array pentru imagini
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

// Imagine implicită în cazul în care marca nu este găsită
$default_image = 'images/default_car.jpg';

?>

<!DOCTYPE html>
<html lang="ro">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RAY CARS - Lista Mașinilor Disponibile</title>
    <link href="img/favicon.ico" rel="icon">
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;500;600;700&family=Rubik&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.0/css/all.min.css" rel="stylesheet">
    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">
    <link href="lib/tempusdominus/css/tempusdominus-bootstrap-4.min.css" rel="stylesheet" />
    <link href="bootstrap.min.css" rel="stylesheet">
    <link href="test.css" rel="stylesheet">
</head>

<body>

<!-- Topbar Start -->
    <div class="container-fluid bg-dark py-3 px-lg-5 d-none d-lg-block">
        <div class="row">
            <div class="col-md-6 text-center text-lg-left mb-2 mb-lg-0">
                <div class="d-inline-flex align-items-center">
                    <a class="text-body pr-3" href=""><i class="fa fa-phone-alt mr-2"></i>+0757890736</a>
                    <span class="text-body">|</span>
                    <a class="text-body px-3" href=""><i class="fa fa-envelope mr-2"></i>raullungulescu@gmail.com</a>
                </div>
            </div>
        
            <div class="col-md-6 text-center text-lg-right">
                <div class="d-inline-flex align-items-center">
                    <a class="text-body px-3" href="">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a class="text-body px-3" href="">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a class="text-body px-3" href="">
                        <i class="fab fa-linkedin-in"></i>
                    </a>
                    <a class="text-body px-3" href="">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a class="text-body pl-3" href="">
                        <i class="fab fa-youtube"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
<!-- Topbar End -->

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



    <div class="container">
        <h1>Lista Mașinilor Disponibile</h1>
        <p>Bine ai venit, <?php echo htmlspecialchars($_SESSION["utilizatori"]); ?>!</p>  </div>
   
        
    <div class="container-fluid p-0" style="margin-bottom: 90px;">
        <div id="header-carousel" class="carousel slide" data-ride="carousel">
            <div class="carousel-inner">
                <div class="carousel-item active">
                    <img class="w-100" src="test1.jpg" alt="Image">
                    <div class="carousel-caption d-flex flex-column align-items-center justify-content-center">
                        <div class="p-3" style="max-width: 800px;">
                            <h1 class="display-1 text-white mb-md-4">Poti fii si tu un furnizor de masini</h1>
                        </div>
                    </div>
                </div>
          </div>
      </div>    
 </div>
 <div class="container">

        <!-- Formular pentru filtrare -->
        <form method="get" class="filter-form">
            <label for="marca">Marcă:</label>
            <select name="marca" id="marca">
                <option value="">Toate</option>
                <?php while ($row = $result_marci->fetch_assoc()): ?>
                    <option value="<?php echo $row['Marca']; ?>" <?php echo $marca_filter == $row['Marca'] ? 'selected' : ''; ?>>
                        <?php echo $row['Marca']; ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <label for="pret">Preț:</label>
            <input type="number" name="pret_min" placeholder="Preț minim" value="<?php echo $pret_min_filter; ?>">
            <input type="number" name="pret_max" placeholder="Preț maxim" value="<?php echo $pret_max_filter; ?>">

            <label for="disponibilitate">Disponibilitate:</label>
            <select name="disponibilitate" id="disponibilitate">
                <option value="">Toate</option>
                <option value="1" <?php echo $disponibilitate_filter == 1 ? 'selected' : ''; ?>>Disponibil</option>
                <option value="0" <?php echo $disponibilitate_filter == 0 ? 'selected' : ''; ?>>Indisponibil</option>
            </select>

            <button type="submit">Filtrează</button>
        </form>

        <div class="row">
            <?php if ($result_masini->num_rows > 0): ?>
                <?php while ($row = $result_masini->fetch_assoc()): ?>
                    <div class="col-lg-4 col-md-6 mb-2">
                        <div class="rent-item mb-4">
                            <!-- Atribuirea imaginii în funcție de marca mașinii -->
                            <?php
                                $marca = strtolower($row['Marca']); // Folosim marca pentru a găsi imaginea
                                $imagePath = isset($imagini[$marca]) ? $imagini[$marca] : $default_image;
                            ?>
                            <img class="img-fluid mb-4" src="<?php echo $imagePath; ?>" alt="<?php echo htmlspecialchars($row['Marca']) . ' ' . htmlspecialchars($row['Model']); ?>">
                            <h4 class="text-uppercase mb-4"><?php echo htmlspecialchars($row['Marca']) . ' ' . htmlspecialchars($row['Model']); ?></h4>
                            <div class="d-flex justify-content-center mb-4">
                                <div class="px-2">
                                    <i class="fa fa-car text-primary mr-1"></i>
                                    <span><?php echo htmlspecialchars($row['An_fabricatie']); ?></span>
                                </div>
                                <div class="px-2 border-left border-right">
                                    <i class="fa fa-cogs text-primary mr-1"></i>
                                    <span><?php echo htmlspecialchars($row['Tip_motor']); ?></span>
                                </div>
                                <div class="px-2">
                                    <i class="fa fa-road text-primary mr-1"></i>
                                    <span><?php echo htmlspecialchars($row['Kilometraj']); ?> km</span>
                                </div>
                            </div>
                            <!-- Afișează prețul mașinii -->
                            <div class="price mb-4">
                                <span class="text-primary font-weight-bold"><?php echo number_format($row['Pret_masina'], 2, ',', '.') . ' RON'; ?></span>
                            </div>
                            <a class="btn btn-primary px-3" href="detalii.php?id=<?php echo $row['id_masina']; ?>">Detalii</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>Nu au fost găsite mașini!</p>
            <?php endif; ?>
        </div>
    </div>
          
     <!-- Footer Start -->
     <div class="container-fluid bg-secondary py-5 px-sm-3 px-md-5" style="margin-top: 90px;">
        <div class="row pt-5">
            <div class="col-lg-3 col-md-6 mb-5">
                <h4 class="text-uppercase text-light mb-4">Contact</h4>
                <p class="mb-2"><i class="fa fa-map-marker-alt text-white mr-3"></i>123 Street, Bucuresti, Romanaia</p>
                <p class="mb-2"><i class="fa fa-phone-alt text-white mr-3"></i>+0757 890 736</p>
                <p><i class="fa fa-envelope text-white mr-3"></i>raullungulescu.com</p>
            </div>
            <div class="col-lg-3 col-md-6 mb-5">
                <h4 class="text-uppercase text-light mb-4">Links utile</h4>
                <div class="d-flex flex-column justify-content-start">
                    <a class="text-body mb-2" href="#"><i class="fa fa-angle-right text-white mr-2"></i>Coppy right</a>
                    <a class="text-body mb-2" href="#"><i class="fa fa-angle-right text-white mr-2"></i>Termeni si Conditii</a>
                    <a class="text-body mb-2" href="#"><i class="fa fa-angle-right text-white mr-2"></i>Informatii</a>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-5">
                <h4 class="text-uppercase text-light mb-4">Masini Gallery</h4>
                <div class="row mx-n1">
                    <div class="col-4 px-1 mb-2">
                        <a href=""><img class="w-100" src="test1.jpg" alt=""></a>
                    </div>
                    <div class="col-4 px-1 mb-2">
                        <a href=""><img class="w-100" src="principal_phtoto.jpg" alt=""></a>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-5">
                <h4 class="text-uppercase text-light mb-4">Newsletter</h4>
                <p class="mb-4">Pentru urmatoarele idei pe care le poti accesa pentru site-ul nostru si diverse informatii contacteaza-ne </p>
                <div class="w-100 mb-3">
                    <div class="input-group">
                        <input type="text" class="form-control bg-dark border-dark" style="padding: 25px;" placeholder="Email">
                    </div>
        </div>
    </div>
    <!-- Footer End -->
    

    <!-- Back to Top -->
    <a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top"><i class="fa fa-angle-double-up"></i></a>

</body>

</html>
