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

// Récupérer les véhicules pour la sélection
function getAllCars($pdo) {
    $stmt = $pdo->query('SELECT id, brand, model FROM cars ORDER BY brand, model');
    return $stmt->fetchAll();
}

$cars = getAllCars($pdo);

// Vérifier si le formulaire d'ajout a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $car_id = $_POST['car_id'];
    $type = $_POST['type'];
    $date = $_POST['date'];
    $cost = $_POST['cost'];

    // Ajouter la nouvelle opération d'entretien à la base de données
    $stmt = $pdo->prepare('INSERT INTO maintenance (car_id, type, date, cost) VALUES (?, ?, ?, ?)');
    $stmt->execute([$car_id, $type, $date, $cost]);

    // Redirection vers la page de gestion de l'entretien
    header('Location: maintenance_management.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter une Opération d'
    Entretien</title>
<style>
body {
font-family: Arial, sans-serif;
background-color: #f0f0f0;
display: flex;
flex-direction: column;
align-items: center;
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
        border: 1px solid #ddd;
        border-radius: 4px;
    }

    .button-container {
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
        <h2>Ajouter une Opération d'Entretien</h2>
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
        <div class="form-group">
            <label for="car_id">Véhicule</label>
            <select name="car_id" id="car_id" required>
                <?php foreach ($cars as $car): ?>
                    <option value="<?php echo $car['id']; ?>">
                        <?php echo htmlspecialchars($car['brand'] . ' ' . $car['model']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="type">Type d'Entretien</label>
            <input type="text" name="type" id="type" required>
        </div>
        <div class="form-group">
            <label for="date">Date</label>
            <input type="date" name="date" id="date" required>
        </div>
        <div class="form-group">
            <label for="cost">Coût</label>
            <input type="number" name="cost" id="cost" required>
        </div>
        <div class="button-container">
            <button type="submit">Ajouter</button>
        </div>
    </form>
</div>
</body>
</html>