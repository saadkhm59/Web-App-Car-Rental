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

// Fonction pour récupérer toutes les opérations d'entretien
function getAllMaintenance($pdo) {
    $stmt = $pdo->query('
        SELECT maintenance.id, cars.id AS car_id, cars.brand, cars.model, maintenance.type, maintenance.date, maintenance.cost, maintenance.status
        FROM maintenance
        INNER JOIN cars ON maintenance.car_id = cars.id
        ORDER BY maintenance.date DESC
    ');
    return $stmt->fetchAll();
}

// Fonction pour ajouter un rappel d'entretien
function sendMaintenanceReminder($pdo) {
    $stmt = $pdo->query('
        SELECT cars.brand, cars.model, maintenance.type, maintenance.date
        FROM maintenance
        INNER JOIN cars ON maintenance.car_id = cars.id
        WHERE maintenance.date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
    ');
    $maintenances = $stmt->fetchAll();

    foreach ($maintenances as $maintenance) {
        // Logique pour envoyer un rappel par email ou autre méthode
        // Par exemple : echo "Rappel : Entretien prévu pour " . $maintenance['brand'] . " " . $maintenance['model'] . " le " . $maintenance['date'] . " pour " . $maintenance['type'];
    }
}

// Récupérer toutes les opérations d'entretien
$maintenances = getAllMaintenance($pdo);

// Appeler la fonction pour envoyer les rappels
sendMaintenanceReminder($pdo);

// Vérifier si le formulaire d'ajout/modification/suppression a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete'])) {
        $maintenance_id = $_POST['id'];

        // Supprimer l'opération d'entretien de la base de données
        $stmt = $pdo->prepare('DELETE FROM maintenance WHERE id = ?');
        $stmt->execute([$maintenance_id]);

        // Redirection pour éviter la réinscription lors de l'actualisation de la page
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    } elseif (isset($_POST['edit'])) {
        $maintenance_id = $_POST['id'];

        // Redirection vers la page de modification avec l'ID de l'opération d'entretien
        header('Location: edit_maintenance.php?id=' . $maintenance_id);
        exit;
    } elseif (isset($_POST['add'])) {
        $car_id = $_POST['car_id'];
        $type = $_POST['type'];
        $date = $_POST['date'];
        $cost = $_POST['cost'];

        // Ajouter la nouvelle opération d'entretien à la base de données
        $stmt = $pdo->prepare('INSERT INTO maintenance (car_id, type, date, cost) VALUES (?, ?, ?, ?)');
        $stmt->execute([$car_id, $type, $date, $cost]);

        // Redirection pour éviter la réinscription lors de l'actualisation de la page
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion de l'Entretien</title>
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
            max-width: 1200px;
        }

        .container h2 {
            margin-bottom: 20px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th, .table td {
            border: 1px solid #ddd;
            padding: 8px;
        }

        .table th {
            background-color: #007BFF;
            color: white;
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
            margin-left: 10px;
        }

        .button-container button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Gestion de l'Entretien</h2>

        <!-- Bouton pour ajouter une nouvelle opération d'entretien -->
        <div class="button-container">
            <button onclick="window.location.href='add_maintenance.php'">Ajouter une Opération d'Entretien</button>
            <button onclick="window.location.href='home.php'">Retour à l'accueil</button>
        </div>

        <!-- Liste des opérations d'entretien -->
        <div class="list-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Véhicule</th>
                        <th>Type d'Entretien</th>
                        <th>Date</th>
                        <th>Coût</th>

                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($maintenances as $maintenance): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($maintenance['id']); ?></td>
                            <td><?php echo htmlspecialchars($maintenance['brand'] . ' ' . $maintenance['model']); ?></td>
                            <td><?php echo htmlspecialchars($maintenance['type']); ?></td>
                            <td><?php echo htmlspecialchars($maintenance['date']); ?></td>
                            <td><?php echo htmlspecialchars($maintenance['cost']); ?></td>
                          
                            <td>
                                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
                                    <input type="hidden" name="id" value="<?php echo $maintenance['id']; ?>">
                                    <button type="submit" name="edit">Modifier</button>
                                    <button type="submit" name="delete" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette opération d\'entretien ?')">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
