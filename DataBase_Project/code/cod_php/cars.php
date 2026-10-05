<?php
// Pornim sesiunea
session_start();

// Verificăm dacă utilizatorul este autentificat
if (!isset($_SESSION["utilizatori"])) {
    header("Location: login.php");
    exit;
}

// Verificăm dacă rolul este setat în sesiune
if (!isset($_SESSION["role"])) {
    die("Rolul utilizatorului nu este definit. Te rog să te autentifici din nou.");
}

// Activăm raportarea erorilor pentru a putea depista eventualele probleme
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Detalii conexiune la baza de date
$servername = (require __DIR__ . '/config.php')['host'];
$username = (require __DIR__ . '/config.php')['user'];
$password = (require __DIR__ . '/config.php')['pass'];
$database = (require __DIR__ . '/config.php')['db'];

// Crearea conexiunii la baza de date
$conn = new mysqli($servername, $username, $password, $database);

// Verificăm dacă există vreo eroare de conexiune
if ($conn->connect_error) {
    die("Eroare de conexiune: " . $conn->connect_error);
}

// Filtre din formular (dacă există)
$marca_filter = isset($_GET['marca']) ? $_GET['marca'] : '';
$pret_min_filter = isset($_GET['pret_min']) ? $_GET['pret_min'] : '';
$pret_max_filter = isset($_GET['pret_max']) ? $_GET['pret_max'] : '';
$disponibilitate_filter = isset($_GET['disponibilitate']) ? $_GET['disponibilitate'] : '';

// Obținem mărci unice pentru dropdown
$marca_sql = "SELECT DISTINCT Marca FROM masini";
$marca_result = $conn->query($marca_sql);

// Construim interogarea SQL cu posibilele filtre
$sql = "SELECT id_masina, Marca, Model, An_fabricatie, Tip_Motor, Putere_Motor, Culoare, Kilometraj, Disponibilitate, Pret_masina FROM masini WHERE 1";

// Aplicăm filtrele
if ($marca_filter) {
    $sql .= " AND Marca LIKE ?";
}
if ($pret_min_filter) {
    $sql .= " AND Pret_masina >= ?";
}
if ($pret_max_filter) {
    $sql .= " AND Pret_masina <= ?";
}
if ($disponibilitate_filter !== '') {
    $sql .= " AND Disponibilitate = ?";
}

// Pregătim interogarea SQL
$stmt = $conn->prepare($sql);

// Legăm parametrii
$params = [];
$param_types = "";

if ($marca_filter) {
    $param_types .= "s";  // string
    $params[] = "%$marca_filter%";
}
if ($pret_min_filter) {
    $param_types .= "i";  // integer
    $params[] = $pret_min_filter;
}
if ($pret_max_filter) {
    $param_types .= "i";  // integer
    $params[] = $pret_max_filter;
}
if ($disponibilitate_filter !== '') {
    $param_types .= "i";  // integer
    $params[] = $disponibilitate_filter;
}

// Leagă parametrii și execută interogarea
if ($param_types) {
    $stmt->bind_param($param_types, ...$params);
}
$stmt->execute();
$masini_result = $stmt->get_result();

// Verificăm dacă interogarea a returnat rezultate
if ($masini_result === false) {
    die("Eroare la obținerea mașinilor: " . $conn->error);
}
// Adăugarea unei mașini
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["add_car"])) {
    // Preluăm datele din formular
    $marca = $_POST["marca"];
    $model = $_POST["model"];
    $an_fabricatie = $_POST["an_fabricatie"];
    $tip_motor = $_POST["tip_motor"];
    $putere_motor = $_POST["putere_motor"];
    $culoare = $_POST["culoare"];
    $kilometraj = $_POST["kilometraj"];
    $disponibilitate = $_POST["disponibilitate"];
    $pret = $_POST["pret"];
    
    // Pregătim interogarea SQL pentru a adăuga o mașină
    $sql = "INSERT INTO masini (Marca, Model, An_fabricatie, Tip_Motor, Putere_Motor, Culoare, Kilometraj, Disponibilitate, Pret_masina) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    
    // Verificăm dacă pregătirea interogării a fost un succes
    if ($stmt === false) {
        die("Eroare la pregătirea interogării: " . $conn->error);
    }

    // Legăm parametrii
    $stmt->bind_param("ssisssisi", $marca, $model, $an_fabricatie, $tip_motor, $putere_motor, $culoare, $kilometraj, $disponibilitate, $pret);
    
    // Executăm interogarea și verificăm dacă a avut succes
    if (!$stmt->execute()) {
        die("Eroare la inserare: " . $stmt->error);
    }

    $stmt->close(); // Închidem interogarea
    header("Location: masini.php"); // Redirect către pagina cu mașinile
    exit;
}
// Verificăm dacă este solicitată ștergerea
if (isset($_GET['delete_id']) && $_SESSION["role"] == "admin") {
    $delete_id = (int) $_GET['delete_id'];
    $delete_sql = "DELETE FROM masini WHERE id_masina = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $delete_id);
    $delete_stmt->execute();
    $delete_stmt->close();
    header("Location: masini.php");
    exit;
}

// Închidem conexiunea la baza de date
$stmt->close();
$conn->close();
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="img/favicon.ico" rel="icon">
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;500;600;700&family=Rubik&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.0/css/all.min.css" rel="stylesheet">
    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">
    <link href="lib/tempusdominus/css/tempusdominus-bootstrap-4.min.css" rel="stylesheet" />
    <link href="bootstrap.min.css" rel="stylesheet">
    <link href="test.css" rel="stylesheet">
</head>
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


<br><br>

<div class="col-lg-8 mb-5">
<h1 class="display-4 text-uppercase mb-5">Adauga masina</h1> </div>

 <!-- Formularul de adăugare -->
 <div class="container-fluid bg-white pt-3 px-lg-5">
        <form method="POST" action="">
            <div class="row mx-n2">
                <div class="col-xl-2 col-lg-4 col-md-6 px-2">
                    <input type="text" name="marca" id="marca" class="form-control p-4 mb-3" placeholder="Marca" required>
                </div>
                <div class="col-xl-2 col-lg-4 col-md-6 px-2">
                    <input type="text" name="model" id="model" class="form-control p-4 mb-3" placeholder="Model" required>
                </div>
                <div class="col-xl-2 col-lg-4 col-md-6 px-2">
                    <input type="number" name="an_fabricatie" id="an_fabricatie" class="form-control p-4 mb-3" placeholder="An Fabricatie" required>
                </div>
                <div class="col-xl-2 col-lg-4 col-md-6 px-2">
                    <input type="text" name="tip_motor" id="tip_motor" class="form-control p-4 mb-3" placeholder="Tip Motor" required>
                </div>
                <div class="col-xl-2 col-lg-4 col-md-6 px-2">
                    <input type="number" name="putere_motor" id="putere_motor" class="form-control p-4 mb-3" placeholder="Putere Motor" required>
                </div>
                <div class="col-xl-2 col-lg-4 col-md-6 px-2">
                    <input type="text" name="culoare" id="culoare" class="form-control p-4 mb-3" placeholder="Culoare" required>
                </div>
                <div class="col-xl-2 col-lg-4 col-md-6 px-2">
                    <input type="number" name="kilometraj" id="kilometraj" class="form-control p-4 mb-3" placeholder="Kilometraj" required>
                </div>
                <div class="col-xl-2 col-lg-4 col-md-6 px-2">
                    <input type="text" name="disponibilitate" id="disponibilitate" class="form-control p-4 mb-3" placeholder="Disponibilitate" required>
                </div>
                <div class="col-xl-2 col-lg-4 col-md-6 px-2">
                    <input type="number" name="pret" id="pret" class="form-control p-4 mb-3" placeholder="Preț" required>
                </div>
                <div class="col-xl-2 col-lg-4 col-md-6 px-2">
                    <button type="submit" name="add_car" class="btn btn-primary btn-block mb-3" style="height: 50px;">Adaugă Mașină</button>
                </div>
            </div>
        </form>
    </div><br><br>

 <!-- Page Header Start -->
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
          
            
<!-- Detalii Start -->
<div class="container-fluid pt-5">
    <div class="container pt-5">
        <div class="row">
            <div class="col-lg-8 mb-5">
                <h1 class="display-4 text-uppercase mb-5">Selecteaza masina</h1>
                <div class="row mb-5">
                    <div class="col-md-10">
                        <img src="bg-banner.jpg" alt="Image" class="img-fluid">
                    </div>
                    <div class="col-md-10"> <br>
                        <p class="lead">Vezi masinile adaugate si modelul tau poate fi vazut pe site-ul nostru adauga si tu o masina...</p>
                    </div>
                </div>
            </div>
            <!-- Filtrare Form inlocuit sectiunea de verificare disponibilitate -->
            <div class="col-lg-4 mb-5">
                <div class="bg-secondary p-5">
                    <h3 class="text-primary text-center mb-4">Filtrare Mașini</h3>

                    <!-- Formularul de filtrare -->
                    <form method="get" class="filter-form">
                        <div class="form-group">
                            <label for="marca">Marcă:</label>
                            <select name="marca" id="marca" class="custom-select px-4" style="height: 50px;">
                                <option value="">Toate</option>
                                <?php while ($row = $marca_result->fetch_assoc()): ?>
                                    <option value="<?php echo $row['Marca']; ?>" <?php echo ($marca_filter == $row['Marca']) ? 'selected' : ''; ?>>
                                        <?php echo $row['Marca']; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="pret_min">Preț minim:</label>
                            <input type="number" name="pret_min" id="pret_min" class="form-control px-4" placeholder="Preț minim" value="<?php echo $pret_min_filter; ?>" style="height: 50px;">
                        </div>

                        <div class="form-group">
                            <label for="pret_max">Preț maxim:</label>
                            <input type="number" name="pret_max" id="pret_max" class="form-control px-4" placeholder="Preț maxim" value="<?php echo $pret_max_filter; ?>" style="height: 50px;">
                        </div>

                        <div class="form-group">
                            <label for="disponibilitate">Disponibilitate:</label>
                            <select name="disponibilitate" id="disponibilitate" class="custom-select px-4" style="height: 50px;">
                                <option value="">Toate</option>
                                <option value="1" <?php echo ($disponibilitate_filter === '1') ? 'selected' : ''; ?>>Disponibilă</option>
                                <option value="0" <?php echo ($disponibilitate_filter === '0') ? 'selected' : ''; ?>>Indisponibilă</option>
                            </select>
                        </div>

                        <div class="form-group mb-0">
                            <button class="btn btn-primary btn-block" type="submit" style="height: 50px;">Filtrează</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Tabelul cu rezultatele filtrate -->
        <div class="row">
            <div class="col-lg-12">
                <h3>Rezultatele filtrării:</h3>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Marca</th>
                            <th>Model</th>
                            <th>An Fabricatie</th>
                            <th>Disponibilitate</th>
                            <th>Pret</th>
                            <th>Acțiune</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($masina = $masini_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $masina['Marca']; ?></td>
                                <td><?php echo $masina['Model']; ?></td>
                                <td><?php echo $masina['An_fabricatie']; ?></td>
                                <td><?php echo $masina['Disponibilitate'] == 1 ? 'Disponibilă' : 'Indisponibilă'; ?></td>
                                <td><?php echo $masina['Pret_masina']; ?></td>
                                <td>
                                    <?php if ($_SESSION["role"] == "admin"): ?>
                                        <a href="?delete_id=<?php echo $masina['id_masina']; ?>" class="btn btn-danger">Șterge</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
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
</body>
</html>
