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

$nume_client = $_SESSION["utilizatori"];  // Asumăm că numele clientului este stocat în sesiune

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

// Detalii client pentru livrare
$sql_adresa = "SELECT adresa, telefon_client, email FROM clienti WHERE nume_client = ?";
$stmt_adresa = $conn->prepare($sql_adresa);
$stmt_adresa->bind_param("s", $nume_client);
$stmt_adresa->execute();
$adresa_result = $stmt_adresa->get_result();
$adresa = $adresa_result->fetch_assoc();

// Orașe disponibile pentru ridicare personală
$orase = ["București", "Cluj-Napoca", "Timișoara", "Iași", "Constanța"];

// Obține tranzacția clientului
$sql = "SELECT t.id_tranzactie, t.data_vanzare, t.pret, t.metoda_plata, c.nume_client, c.adresa
        FROM tranzactii t
        JOIN clienti c ON t.id_client = c.id_client
        WHERE c.nume_client = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $nume_client);
$stmt->execute();
$result = $stmt->get_result();

// Verificăm dacă s-a trimis formularul pentru adăugarea unei noi tranzacții
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_tranzactie'])) {
    $adresa = $_POST['adresa'];
    $telefon = $_POST['telefon'];
    $email = $_POST['email'];
    $pret = $_POST['pret'];
    $metoda_plata = $_POST['metoda_plata'];

    // Inserăm sau actualizăm adresa clientului
    $sql_update_client = "UPDATE clienti SET adresa = ?, telefon_client = ?, email = ? WHERE nume_client = ?";
    $stmt_update_client = $conn->prepare($sql_update_client);
    $stmt_update_client->bind_param("ssss", $adresa, $telefon, $email, $nume_client);
    $stmt_update_client->execute();

    // Obținem ID-ul clientului
    $sql_client = "SELECT id_client FROM clienti WHERE nume_client = ?";
    $stmt_client = $conn->prepare($sql_client);
    $stmt_client->bind_param("s", $nume_client);
    $stmt_client->execute();
    $client_result = $stmt_client->get_result();
    $client_row = $client_result->fetch_assoc();
    if (!$client_row) { die("Nu exista client asociat utilizatorului in tabela clienti."); }
    $id_client = $client_row['id_client'];

    // Inserăm tranzacția
    $sql_tranzactie = "INSERT INTO tranzactii (id_client, data_vanzare, pret, metoda_plata) VALUES (?, NOW(), ?, ?)";
    $stmt_tranzactie = $conn->prepare($sql_tranzactie);
    $stmt_tranzactie->bind_param("ids", $id_client, $pret, $metoda_plata);
    $stmt_tranzactie->execute();
    $id_tranzactie = $stmt_tranzactie->insert_id;

    echo "<div class='alert alert-success'>Tranzacția a fost adăugată cu succes!</div>";
}





$conn->close();  // Închide conexiunea la baza de date
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

>
    </div>

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
     
        <!-- Secțiunea de Livrare -->
        <div class="container mt-5">
		<h2 class="text-left mb-4"><span class="badge bg-primary rounded-circle me-2">1</span> Modalitate de livrare</h2>
		<form action="comanda.php" method="POST">
    <!-- Tabel Modalitate de Livrare -->
    <table class="table table-bordered">
        <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Opțiune Livrare</th>
                <th>Alege</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td>Prin curier</td>
                <td>
                    <input type="radio" class="btn btn-primary" name="delivery_method" value="courier" id="courier" required>
                </td>
            </tr>
            <tr>
                <td>2</td>
                <td>Ridicare personală</td>
                <td>
                    <input type="radio" class="btn btn-primary" name="delivery_method" value="pickup" id="pickup">
                </td>
            </tr>
        </tbody>
    </table>
    <!-- Buton pentru trimiterea formularului -->
    <button type="submit" class="btn btn-primary">Confirmă alegerea</button> <br> <br>
</form>

            <div id="details"></div>

				<!-- Produse adăugate -->
				<div class="card mb-4 mt-5">
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
			</div>
		</div>

		<script>
    document.addEventListener("DOMContentLoaded", () => {
        const courierOption = document.getElementById("courier");
        const pickupOption = document.getElementById("pickup");
        const detailsDiv = document.getElementById("details");

        // Adresa clientului disponibilă în JavaScript
        const clientData = {
            nume: "<?php echo htmlspecialchars($nume_client); ?>",
            telefon: "<?php echo htmlspecialchars($adresa['telefon_client']); ?>",
            email: "<?php echo htmlspecialchars($adresa['email']); ?>",
            adresa: "<?php echo htmlspecialchars($adresa['adresa']); ?>"
        };

        courierOption.addEventListener("change", () => {
            if (courierOption.checked) {
                detailsDiv.style.display = "block";
                detailsDiv.innerHTML = `
                    <h4>Detalii Client pentru Livrare prin Curier</h4>
                    <table class='table table-striped'>
                        <thead>
                            <tr>
                                <th>Nume</th>
                                <th>Telefon</th>
                                <th>Email</th>
                                <th>Adresă</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>${clientData.nume}</td>
                                <td>${clientData.telefon}</td>
                                <td>${clientData.email}</td>
                                <td>${clientData.adresa}</td>
                            </tr>
                        </tbody>
                    </table>`;
            }
        });

        pickupOption.addEventListener("change", () => {
            if (pickupOption.checked) {
                detailsDiv.style.display = "block";
                detailsDiv.innerHTML = `
                    <h4>Orașe Disponibile pentru Ridicare Personală</h4>
                    <ul class='list-group'>
                        <?php foreach ($orase as $oras): ?>
                            <li class='list-group-item'><?php echo $oras; ?></li>
                        <?php endforeach; ?>
                    </ul>`;
            }
        });
    });
</script>


    <div class="container py-5">
        <!-- Date facturare -->
        <h2 class="text-start mb-4"><span class="badge bg-primary rounded-circle me-2">2</span> Date facturare</h2> 
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

        <div class="container py-5">
            <!-- Adăugare Tranzacție Nouă -->
            <h2 class="text-left mb-4"><span class="badge bg-primary rounded-circle me-2">3</span> Adăugare Tranzacție Nouă</h2>

            <!-- Formular pentru adăugarea tranzacției -->
            <form method="POST">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Datele Tranzacției</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="adresa" class="form-label">Adresă</label>
                            <input type="text" class="form-control" id="adresa" name="adresa" required>
                        </div>
                        <div class="mb-3">
                            <label for="telefon" class="form-label">Telefon</label>
                            <input type="text" class="form-control" id="telefon" name="telefon" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="pret" class="form-label">Preț Tranzacție</label>
                            <input type="number" class="form-control" id="pret" name="pret" required>
                        </div>
                        <div class="mb-3">
                            <label for="metoda_plata" class="form-label">Metodă de Plata</label>
                            <select class="form-control" id="metoda_plata" name="metoda_plata" required>
                                <option value="card">cash</option>
                                <option value="numerar">card</option>
                                <option value="numerar">rate</option>
                            </select>
                        </div>
                        <button type="submit" name="submit_tranzactie" class="btn btn-primary">Adaugă Tranzacția</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

	<div class="row justify-content-center mt-4">
    <div class="col-12 col-md-8 text-center">
        <p id="step-content">Acesta este pasul 2: Detalii livrare.</p>
        
        <!-- Butoane plasate unul lângă altul -->
        <div class="d-flex justify-content-center">
            <!-- Formular pentru butonul Înapoi (duce la shop.php) -->
            <form action="shop.php" method="POST">
                <button type="submit" class="btn btn-outline-primary me-2" id="prev-btn">Înapoi</button>
            </form>

            <!-- Formular pentru butonul Înainte (duce la comanda.php) -->
            <form action="comanda.php" method="POST">
                <button type="submit" class="btn btn-primary" id="next-btn">Înainte</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>
