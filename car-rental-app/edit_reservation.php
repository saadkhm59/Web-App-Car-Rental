<?php
// Connexion à la base de données
$host = 'localhost';
$db = 'car_rental';
$user = 'root';
$pass = '';

$dsn = "mysql:host=$host;dbname=$db;charset=utf8";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die("Erreur de connexion à la base de données: " . $e->getMessage());
}

// Vérifier si l'ID de réservation est spécifié dans l'URL
if (!isset($_GET['id'])) {
    die("ID de réservation non spécifié.");
}

$reservation_id = $_GET['id'];

// Récupérer les détails de la réservation à modifier
$stmt = $pdo->prepare('
    SELECT reservations.id, customers.name AS customer_name, cars.brand, cars.model, reservations.start_date, reservations.end_date, reservations.status
    FROM reservations
    INNER JOIN customers ON reservations.customer_id = customers.id
    INNER JOIN cars ON reservations.car_id = cars.id
    WHERE reservations.id = ?
');
$stmt->execute([$reservation_id]);

$reservation = $stmt->fetch();

// Vérifier si le formulaire de modification a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    // Exécuter la mise à jour de la réservation
    $stmt = $pdo->prepare('
        UPDATE reservations
        SET start_date = ?, end_date = ?
        WHERE id = ?
    ');
    $stmt->execute([$start_date, $end_date, $reservation_id]);

    // Redirection vers la gestion des réservations après la mise à jour
    header('Location: reservation_management.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier une Réservation</title>
    <style>
        /* Styles CSS */
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            padding: 20px;
        }

        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 600px;
        }

        .container h2 {
            margin-bottom: 20px;
            text-align: center;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
        }

        .form-group input, .form-group select {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
        }

        .button-container {
            margin-top: 20px;
            text-align: right;
        }

        .button-container button {
            padding: 10px 15px;
            background-color: #007BFF;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .button-container button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Modifier une Réservation</h2>

        <form action="edit_reservation.php?id=<?php echo htmlspecialchars($reservation_id); ?>" method="POST">
            <div class="form-group">
                <label for="start_date">Date de Début :</label>
                <input type="date" id="start_date" name="start_date" value="<?php echo htmlspecialchars($reservation['start_date']); ?>" required>
            </div>
            <div class="form-group">
                <label for="end_date">Date de Fin :</label>
                <input type="date" id="end_date" name="end_date" value="<?php echo htmlspecialchars($reservation['end_date']); ?>" required>
            </div>
            <div class="button-container">
                <button type="submit" name="update">Mettre à Jour</button>
            </div>
        </form>
    </div>
</body>
</html>
