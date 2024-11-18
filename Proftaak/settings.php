<?php
// Verkrijg de user_id vanuit de GET-parameters met een standaardwaarde
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 1;

// Functie om gegevens van de API te verkrijgen
function getDataFromAPI($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Schakel certificaatverificatie uit
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // Schakel hostverificatie uit
    $json = curl_exec($ch);
    curl_close($ch);
    return json_decode($json, true);
}

// Functie om gegevens naar de API te sturen
function updateDataToAPI($url, $data) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH'); // Gebruik PATCH hier
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json',
        'Content-Length: ' . strlen(json_encode($data))
    ]);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

// API URL
$api_url = "https://192.168.123.16/api/users";

// Haal de gegevens op vanaf de API
$users = getDataFromAPI($api_url);

// Zoek de gebruiker op basis van user_id
$current_user = null;
foreach ($users as $user) {
    if ($user['id'] == $user_id) {
        $current_user = $user;
        break;
    }
}

$goal = 0; // Initieer de goal variabele
if ($current_user) {
    $weight = $current_user['weight'];
    $goal = $weight * 35; // Bereken de goal
} else {
    die("User not found.");
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
    <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Extra styling to move the pencil icon to the right */
        .DoelSettings {
            position: relative;
        }
        .DoelSettings img {
            position: absolute;
            top: 8px;
            right: 10px;
            cursor: pointer;
        }
    </style>
</head>
<body>
<header class="d-flex justify-content-between align-items-center p-3">
    <div>
        <img src="img/H2flow..svg" alt="Logo" style="height: 25px;">
    </div>
    <div>
        <a href="index.php?user_id=<?php echo $user_id; ?>"><img src="img/home.svg" class="gear" alt="Settings"></a>
    </div>
</header>

<div class="container mt-5">
    <div class="row mb-4">
        <div class="col-12 col-md-6">
            <div class="DoelSettings">
                <?php if ($current_user): ?>
                    <p>Personal Information</p>
                    <p>Volledige naam: <?php echo htmlspecialchars($current_user['name']); ?></p>
                    <p>Email: <?php echo htmlspecialchars($current_user['email']); ?></p>
                    <p>Lengte: <?php echo htmlspecialchars($current_user['height']); ?> cm</p>
                    <p>Gewicht: <?php echo htmlspecialchars($current_user['weight']); ?> kg</p>
                    <img src="img/potlood.svg" alt="" data-toggle="modal" data-target="#updateModal">
                    <button type="button" id="UpdateGegevens" class="btn btn-primary" data-toggle="modal" data-target="#updateModal" style="display:none;">
                        Update gegevens
                    </button>
                <?php else: ?>
                    <p>Geen gegevens gevonden voor deze gebruiker.</p>
                <?php endif; ?>
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
                <form id="updateForm">
                    <input type="hidden" name="id" value="<?php echo $current_user['id']; ?>">
                    <div class="form-group">
                        <label for="fullname">Volledige naam</label>
                        <input type="text" class="form-control" name="fullname" id="fullname" value="<?php echo htmlspecialchars($current_user['name']); ?>">
                    </div>
                    <div class="form-group">
                        <label for="email">E-mail</label>
                        <input type="email" class="form-control" name="email" id="email" value="<?php echo htmlspecialchars($current_user['email']); ?>">
                    </div>
                    <div class="form-group">
                        <label for="height">Lengte (cm)</label>
                        <input type="number" class="form-control" name="height" id="height" value="<?php echo htmlspecialchars($current_user['height']); ?>">
                    </div>
                    <div class="form-group">
                        <label for="weight">Gewicht (kg)</label>
                        <input type="number" class="form-control" name="weight" id="weight" value="<?php echo htmlspecialchars($current_user['weight']); ?>">
                    </div>
                    <button type="submit" class="btn btn-primary">Bijwerken</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Voeg Bootstrap JS en jQuery toe -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
<script>
    $(document).ready(function() {
        $("#updateForm").on("submit", function(event) {
            event.preventDefault(); // Voorkom dat het formulier normaal wordt verzonden
            
            // Verkrijg de gegevens van het formulier
            var formData = {
                name: $("#fullname").val(),
                email: $("#email").val(),
                height: $("#height").val(),
                weight: $("#weight").val()
            };
            
            // Voer de AJAX-aanroep uit
            $.ajax({
                url: "https://192.168.123.16/api/users/<?php echo $current_user['id']; ?>",
                type: "PATCH",
                data: JSON.stringify(formData),
                contentType: "application/json",
                success: function(response) {
                    // Geef een melding dat het is bijgewerkt
                    alert("Gegevens succesvol bijgewerkt!");
                    // Sluit de modale
                    $("#updateModal").modal('hide');
                    location.reload(); // Herlaad de pagina om de nieuwe gegevens weer te geven
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    // Foutafhandeling
                    alert("Fout bij het bijwerken van gegevens: " + textStatus);
                }
            });
        });
    });
</script>
</body>
</html>