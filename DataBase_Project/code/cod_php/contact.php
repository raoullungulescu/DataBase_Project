<?php
// Conexiunea la baza de date
$servername = (require __DIR__ . '/config.php')['host'];
$username = (require __DIR__ . '/config.php')['user'];
$password = (require __DIR__ . '/config.php')['pass'];
$dbname = (require __DIR__ . '/config.php')['db'];

// Crează conexiunea
$conn = new mysqli($servername, $username, $password, $dbname);

// Verifică dacă conexiunea a fost stabilită cu succes
if ($conn->connect_error) {
    die("Conexiunea a eșuat: " . $conn->connect_error);
}

// Interogarea pentru numărul de mașini al fiecărui client
$sql_clienti = "
    SELECT 
        c.nume_client, 
        COUNT(m.id_masina) AS masini_client
    FROM 
        clienti c
    LEFT JOIN 
        tranzactii t ON c.id_client = t.id_client
    LEFT JOIN 
        masini m ON t.id_tranzactie = m.id_tranzactie
    GROUP BY 
        c.nume_client
    ORDER BY 
        c.nume_client;
";

// Interogarea pentru numărul de mașini al fiecărui furnizor
$sql_furnizori = "
    SELECT 
        f.nume_furnizor, 
        COUNT(m.id_masina) AS masini_furnizor
    FROM 
        furnizor f
    LEFT JOIN 
        masini m ON f.id_furnizor = m.id_furnizor
    GROUP BY 
        f.nume_furnizor
    ORDER BY 
        f.nume_furnizor;
";

// Executăm ambele interogări
$result_clienti = $conn->query($sql_clienti);
$result_furnizori = $conn->query($sql_furnizori);
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

<!-- Contact Start -->
<div class="container-fluid py-5">
        <div class="container pt-5 pb-3">
        
            <h1 class="display-4 text-uppercase text-center mb-5">Contact Us</h1>
            <div class="row">
                <div class="col-lg-7 mb-2">
                    <div class="contact-form bg-light mb-4" style="padding: 30px;">
                        <form>
                            <div class="row">
                                <div class="col-6 form-group">
                                    <input type="text" class="form-control p-4" placeholder="Your Name" required="required">
                                </div>
                                <div class="col-6 form-group">
                                    <input type="email" class="form-control p-4" placeholder="Your Email" required="required">
                                </div>
                            </div>
                            <div class="form-group">
                                <input type="text" class="form-control p-4" placeholder="Subject" required="required">
                            </div>
                            <div class="form-group">
                                <textarea class="form-control py-3 px-4" rows="5" placeholder="Message" required="required"></textarea>
                            </div>
                            <div>
                                <button class="btn btn-primary py-3 px-5" type="submit">Send Message</button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="col-lg-5 mb-2">
                    <div class="bg-secondary d-flex flex-column justify-content-center px-5 mb-4" style="height: 435px;">
                        <div class="d-flex mb-3">
                            <i class="fa fa-2x fa-map-marker-alt text-primary flex-shrink-0 mr-3"></i>
                            <div class="mt-n1">
                                <h5 class="text-light">Head Office</h5>
                                <p>123 Street, New York, USA</p>
                            </div>
                        </div>
                        <div class="d-flex mb-3">
                            <i class="fa fa-2x fa-map-marker-alt text-primary flex-shrink-0 mr-3"></i>
                            <div class="mt-n1">
                                <h5 class="text-light">Branch Office</h5>
                                <p>123 Street, New York, USA</p>
                            </div>
                        </div>
                        <div class="d-flex mb-3">
                            <i class="fa fa-2x fa-envelope-open text-primary flex-shrink-0 mr-3"></i>
                            <div class="mt-n1">
                                <h5 class="text-light">Customer Service</h5>
                                <p>customer@example.com</p>
                            </div>
                        </div>
                        <div class="d-flex">
                            <i class="fa fa-2x fa-envelope-open text-primary flex-shrink-0 mr-3"></i>
                            <div class="mt-n1">
                                <h5 class="text-light">Return & Refund</h5>
                                <p class="m-0">refund@example.com</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Contact End -->
    

<!-- Tabelul cu statistici - Numărul de mașini pentru fiecare client și furnizor -->
<div class="container py-5">
    <h2 class="text-center mb-4">Statistici Mașini</h2>

    <!-- Formular pentru selectarea tabelului -->
    <form method="GET" class="text-center mb-4">
        <label for="tabel_selectat">Alege tabelul de afișat:</label>
        <select name="tabel_selectat" id="tabel_selectat" class="form-control d-inline-block w-auto">
            <option value="clienti" <?php if(isset($_GET['tabel_selectat']) && $_GET['tabel_selectat'] == 'clienti') echo 'selected'; ?>>Clienți</option>
            <option value="furnizori" <?php if(isset($_GET['tabel_selectat']) && $_GET['tabel_selectat'] == 'furnizori') echo 'selected'; ?>>Furnizori</option>
        </select>
        <button type="submit" class="btn btn-primary ml-2">Afișează</button>
    </form>

    <?php
    // Verifică ce tabel a fost selectat
    $tabel_selectat = isset($_GET['tabel_selectat']) ? $_GET['tabel_selectat'] : 'clienti';

    // Alege interogarea și headerul corespunzător
    if ($tabel_selectat == 'clienti') {
        $result = $result_clienti;
        $header = ['Client', 'Număr Mașini Client'];
    } else {
        $result = $result_furnizori;
        $header = ['Furnizor', 'Număr Mașini Furnizor'];
    }
    ?>

    <!-- Tabelul selectat -->
    <h4 class="text-center mb-4">
        <?php echo ($tabel_selectat == 'clienti') ? 'Număr de Mașini pentru Clienți' : 'Număr de Mașini pentru Furnizori'; ?>
    </h4>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <?php foreach($header as $col): ?>
                    <th><?php echo $col; ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    // Afișează datele corecte
                    if ($tabel_selectat == 'clienti') {
                        echo "<td>" . $row['nume_client'] . "</td>";
                        echo "<td>" . ($row['masini_client'] > 0 ? $row['masini_client'] : '0') . "</td>";
                    } else {
                        echo "<td>" . $row['nume_furnizor'] . "</td>";
                        echo "<td>" . ($row['masini_furnizor'] > 0 ? $row['masini_furnizor'] : '0') . "</td>";
                    }
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='" . count($header) . "'>Nu există date pentru " . ($tabel_selectat == 'clienti' ? 'clienți' : 'furnizori') . "</td></tr>";
            }
            ?>
        </tbody>
    </table>
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

<?php
// Închide conexiunea la baza de date
$conn->close();
?>
  