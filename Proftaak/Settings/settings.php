<?php 
$servername = "localhost"; 
$username = "root"; 
$password = ""; 
$dbname = "phpfirsttest"; 

// Database verbinding maken 
$conn = new mysqli($servername, $username, $password, $dbname); 

// Controleer de verbinding 
if ($conn->connect_error) { 
    die("Connection failed: " . $conn->connect_error); 
}

// Verkrijg de user_id vanuit de GET-parameters met een standaardwaarde 
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 1;

// Haal het gewicht op voor de gebruiker
$sql = "SELECT Weight FROM user WHERE ID = $user_id"; 
$result = $conn->query($sql); 

$goal = 0; // Initieer de goal variabele

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $weight = $row['Weight'];
    $goal = $weight * 35; // Bereken de goal
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Water Data</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <header class="d-flex justify-content-between align-items-center p-3 bg-light">
        <div>
            <img src="img/h2flow.png" alt="Logo" style="height: 50px;"> 
        </div>
        <div>
            <a href="Grafiek.php?user_id=<?php echo $user_id; ?>"><img src="img/gear.png" class="gear" alt="Settings"></a>
        </div>
    </header>

    <div class="container mt-5">
        <div class="row mb-4">
            <div class="col-12 col-md-6">
                <div class="GegevensSettings">
                    <?php 
                    // Haal alle gebruikersgegevens op
                    $sql = "SELECT * FROM user WHERE ID = $user_id"; 
                    $result = $conn->query($sql);
                    while ($row = $result->fetch_assoc()): ?>
                        <p>Volledige naam: <?php echo htmlspecialchars($row['Fullname']); ?></p>
                        <p>Email: <?php echo htmlspecialchars($row['E-mail']); ?></p>
                        <p>Telefoonnummer: <?php echo htmlspecialchars($row['PhoneNumber']); ?></p>
                        <p>Lengte: <?php echo htmlspecialchars($row['Height']); ?> cm</p>
                        <p>Gewicht: <?php echo htmlspecialchars($row['Weight']); ?> kg</p>

                        <button type="button" id="UpdateGegevens" class="btn btn-primary" data-toggle="modal" data-target="#updateModal">
                            Update gegevens
                        </button>
                  
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="DoelSettings">
                    <p>Doel: <?php echo htmlspecialchars($goal); ?> ML</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal voor gegevensupdate -->
    <div class="modal fade" id="updateModal" tabindex="-1" role="dialog" aria-labelledby="updateModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="updateModalLabel">Update Gegevens</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Sluiten">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="updateForm" method="POST" action="update.php">
                        <input type="hidden" name="id" value="<?php echo $row['ID']; ?>">
                        <div class="form-group">
                            <label for="fullname">Volledige naam</label>
                            <input type="text" class="form-control" name="fullname" id="fullname" value="<?php echo htmlspecialchars($row['Fullname']); ?>">
                        </div>
                        <div class="form-group">
                            <label for="email">E-mail</label>
                            <input type="email" class="form-control" name="email" id="email" value="<?php echo htmlspecialchars($row['E-mail']); ?>">
                        </div>
                        <div class="form-group">
                            <label for="phone">Telefoonnummer</label>
                            <input type="text" class="form-control" name="phone" id="phone" value="<?php echo htmlspecialchars($row['PhoneNumber']); ?>">
                        </div>
                        <div class="form-group">
                            <label for="height">Lengte (cm)</label> 
                            <input type="number" class="form-control" name="height" id="height" value="<?php echo htmlspecialchars($row['Height']); ?>">
                        </div>
                        <div class="form-group">
                            <label for="weight">Gewicht (kg)</label>
                            <input type="number" class="form-control" name="weight" id="weight" value="<?php echo htmlspecialchars($row['Weight']); ?>">
                        </div>
                        <button type="submit" class="btn btn-primary">Bijwerken</button>
                    </form>
                </div>
            </div>  
        </div>
    </div>
    <?php endwhile; ?>
    <!-- Voeg Bootstrap JS en jQuery toe -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>

<?php 
$conn->close(); 
?>