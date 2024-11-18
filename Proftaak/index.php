<?php 
// Aangenomen dat je de gebruiker ID hebt, dit moet je instellen op basis van je authenticatie.
$userId = isset($_GET['user_id']) ? intval($_GET['user_id']) : 1; // Wijzig hier de standaardwaarde indien nodig
$seven_days_ago = date('Y-m-d H:i:s', strtotime('-7 days'));
$today_start = date('Y-m-d 00:00:00');
$today_end = date('Y-m-d 23:59:59');

// Functie om data van de API te krijgen met cURL
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

// Haal gegevens op uit de API
$users = getDataFromAPI('https://192.168.123.16/api/users');
$sensorData = getDataFromAPI('https://192.168.123.16/api/sensor-data');

// Zoek de gebruiker op in de verkregen gebruikerslijst
$user_data = null;
foreach ($users as $user) {
    if ($user['id'] == $userId) {
        $user_data = $user;
        break;
    }
}

// Haal de gewicht van de gebruiker op
$user_weight = $user_data['weight'] ?? 0;
$daily_water_need = $user_weight * 35; // Berekening voor dagelijkse behoefte

// Totale water consumptie van vandaag
$total_today = 0;
foreach ($sensorData as $data) {
    if ($data['user_id'] == $userId && $data['date'] >= $today_start && $data['date'] <= $today_end) {
        $total_today += intval($data['weight']);
    }
}

// Totale water consumptie van de afgelopen 7 dagen
$json_data = [];
$last_week_days = [];
for ($i = 0; $i < 7; $i++) {
    $day = date('Y-m-d', strtotime("-$i days"));
    $last_week_days[$day] = 0; // Initialiseer elke dag met 0 waterverbruik
}

// Vul het waterverbruik in voor elke dag aan de hand van de sensorData
foreach ($sensorData as $data) {
    if ($data['user_id'] == $userId && $data['date'] >= $seven_days_ago) {
        // Haal de datum zonder tijd
        $date = date('Y-m-d', strtotime($data['date']));
        if (isset($last_week_days[$date])) { 
            $last_week_days[$date] += intval($data['weight']);
        }
    }
}

// Converteer naar een array met datums en waterverbruik
$final_data = array_map(function($datum, $waterML) {
    return ['Datum' => $datum, 'waterML' => $waterML];
}, array_keys($last_week_days), $last_week_days);

$json_data = json_encode($final_data);
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
    <header class="d-flex justify-content-between align-items-center p-3">
        <div>
        <img src="img/H2flow..svg" alt="Logo" style="height: 25px;">  <!-- Voeg hier je logo toe -->
        </div>
        <div>
        <a href="settings.php?user_id=<?php echo $userId; ?>"><img src="img/settings.svg" class="gear"></a>
        </div>
    </header>

    <div class="container">
        <div class="row mb-4">
        <div class="col-12 col-md-6">
    <div class="DoelSettings" id="WelcomeBack">
        <img src="img/profile.svg" class="profileimg">
        <p>Welcome Back</p> <!-- Weergeven van de berekende doel -->
        <p><?php echo htmlspecialchars($user_data['name'] ?? ''); ?></p> <!-- Weergave van de naam van de gebruiker -->
    </div>
</div>
            <div class="col-12 col-md-6">
                <div class="DoelSettings">
                    <p class="DoelSettingsDoel">Your goal <?php echo $daily_water_need; ?> ML</p> <!-- Weergeven van de berekende doel -->
                </div>
            </div>
        </div>

        <div class="DoelSettings">
            <p>Your progress today:</p>
            <p><?php echo floor($total_today); ?> ml / <?php echo $daily_water_need; ?> ml</p>
            <div class="progress mb-4">
                <?php $percentage = ($total_today / $daily_water_need) * 100; ?>
                <div class="progress-bar" role="progressbar" style="width: <?php echo min($percentage, 100); ?>%;"
                    aria-valuenow="<?php echo min($total_today, $daily_water_need); ?>" aria-valuemin="0" aria-valuemax="<?php echo $daily_water_need; ?>">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="DoelSettings">
                    <p>Your Progress this week</p>
                    <canvas id="mijnGrafiek" class="grafiek"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script>
var data = <?php echo $json_data; ?>; // Gebruik de JSON-gegevens
var labels = data.map(row => `${row.waterML}`); 
var values = data.map(row => Math.min(row.waterML, <?php echo $daily_water_need; ?>)); // Gebruik waterverbruik

var ctx = document.getElementById('mijnGrafiek').getContext('2d');
var mijnGrafiek = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: labels,
        datasets: [{
            data: values,
            backgroundColor: '#293d7a',
            borderColor: 'rgba(0, 102, 204, 1)',
            borderWidth: 1,
            borderRadius: 10
        },
        {
            data: values.map(value => <?php echo $daily_water_need; ?> - value),
            backgroundColor: 'rgba(200, 200, 200, 0.5)',
            borderColor: 'rgba(200, 200, 200, 0.5)',
            borderWidth: 1,
            borderRadius: 10
        }]
    },
    options: {
        layout: { padding: { left: 0, right: 0, top: 0, bottom: 0 }},
        scales: {
            y: {
                beginAtZero: true,
                max: <?php echo $daily_water_need; ?>,
                stacked: true,
                ticks: { display: false },
                grid: { display: false, color: 'transparent' },
                border: { color: 'transparent', width: 0 }
            },
            x: {
                stacked: true,
                ticks: {
                    display: true,
                    font: {
                        weight: 'bold'
                    }
                },
                grid: { display: false, color: 'transparent' },
                border: { color: 'transparent', width: 0 }
            }
        },
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: function(tooltipItem) {
                        return `Datum: ${tooltipItem.label}\nWaterML: ${tooltipItem.raw}`;
                    }
                }
            }
        }
    }
});
    </script>

    <!-- Voeg Bootstrap JS en jQuery toe -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>

<?php // Sluit geen databaseverbinding meer af, omdat je geen database gebruikt. ?>