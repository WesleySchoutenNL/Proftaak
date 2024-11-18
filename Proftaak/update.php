<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "phpfirsttest";

// Maak verbinding met de database
$conn = new mysqli($servername, $username, $password, $dbname);

// Controleer de verbinding
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Controleer of het formulier is ingediend
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verkrijg de gegevens van het formulier
    $id = intval($_POST['id']);
    $fullname = $conn->real_escape_string($_POST['fullname']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $height = intval($_POST['height']);
    $weight = intval($_POST['weight']);

    // Voer de UPDATE SQL-query uit
    $sql = "UPDATE user SET Fullname='$fullname', `E-mail`='$email', PhoneNumber='$phone', Height=$height, Weight=$weight WHERE ID=$id";

    if ($conn->query($sql) === TRUE) {
        echo "Gegevens bijgewerkt succesvol.";
    } else {
        echo "Fout bij het bijwerken van gegevens: " . $conn->error;
    }

    // Sluit de databaseverbinding
    $conn->close();

    // Redirect of andere acties hier, bijvoorbeeld terug naar de originele pagina.
    header("Location: settings.php?user_id=".$id); // Vergeet niet de juiste pagina hier te zetten!
    exit();
}
?>