<?php
// Connexion à la base de données et définition des fonctions

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

// Fonction pour récupérer toutes les réservations avec détails et coût
function getAllReservations($pdo) {
    $stmt = $pdo->query('
        SELECT reservations.id, customers.name AS customer_name, cars.id AS car_id, cars.brand, cars.model, reservations.start_date, reservations.end_date, reservations.cost, reservations.status
        FROM reservations
        INNER JOIN customers ON reservations.customer_id = customers.id
        INNER JOIN cars ON reservations.car_id = cars.id
        ORDER BY reservations.start_date DESC
    ');
    return $stmt->fetchAll();
}

// Fonction pour mettre à jour les statuts des véhicules après la période de location
function updateCarStatuses($pdo) {
    $current_date = date('Y-m-d');
    $stmt = $pdo->prepare('
        UPDATE cars 
        SET status = "Disponible"
        WHERE id IN (
            SELECT car_id 
            FROM reservations 
            WHERE end_date < :current_date AND status = "En cours"
        )
    ');
    $stmt->execute(['current_date' => $current_date]);
}

// Appeler la fonction pour mettre à jour les statuts des véhicules
updateCarStatuses($pdo);

// Récupérer toutes les réservations avec détails et coût
$reservations = getAllReservations($pdo);

// Vérifier si le formulaire d'annulation a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['cancel'])) {
        $reservation_id = $_POST['id'];

        // Récupérer le car_id avant de supprimer la réservation
        $stmt = $pdo->prepare('SELECT car_id, start_date, end_date FROM reservations WHERE id = ?');
        $stmt->execute([$reservation_id]);
        $reservation = $stmt->fetch();
        $car_id = $reservation['car_id'];
        $start_date = $reservation['start_date'];
        $end_date = $reservation['end_date'];

        // Suppression de la réservation dans la base de données
        $stmt = $pdo->prepare('DELETE FROM reservations WHERE id = ?');
        $stmt->execute([$reservation_id]);

        // Vérification s'il y a d'autres réservations en cours pour ce véhicule dans la même période
        $stmt = $pdo->prepare('
            SELECT COUNT(*)
            FROM reservations
            WHERE car_id = ? AND (
                (start_date <= ? AND end_date >= ?)
                OR (start_date <= ? AND end_date >= ?)
                OR (? <= start_date AND ? >= end_date)
            )
        ');
        $stmt->execute([$car_id, $start_date, $start_date, $end_date, $end_date, $start_date, $end_date]);
        $count = $stmt->fetchColumn();

        // Si aucune autre réservation en cours dans la période, mettre à jour le statut du véhicule à "Disponible"
        if ($count == 0) {
            $stmt = $pdo->prepare('UPDATE cars SET status = "Disponible" WHERE id = ?');
            $stmt->execute([$car_id]);
        }

        // Redirection pour éviter la réinscription lors de l'actualisation de la page
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    } elseif (isset($_POST['edit'])) {
        $reservation_id = $_POST['id'];

        // Redirection vers la page de modification avec l'ID de réservation
        header('Location: edit_reservation.php?id=' . $reservation_id);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Réservations</title>
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
            margin-left: 10px;
        }

        .button-container button:hover {
            background-color: #0056b3;
        }

        .filter-container {
            margin-bottom: 20px;
        }

        .filter-container select {
            padding: 8px;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Gestion des Réservations</h2>

        <!-- Bouton pour ajouter une nouvelle réservation -->
        <div class="button-container">
            <button onclick="window.location.href='add_reservation.php'">Ajouter une Réservation</button>
            <button onclick="window.location.href='home.php'">Retour à l'accueil</button>
        </div>

        <!-- Liste des réservations -->
        <div class="list-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom du Client</th>
                        <th>Véhicule Réservé</th>
                        <th>Date de Début</th>
                        <th>Date de Fin</th>
                        <th>Coût</th> <!-- Nouvelle colonne ajoutée -->
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reservations as $reservation): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($reservation['id']); ?></td>
                            <td><?php echo htmlspecialchars($reservation['customer_name']); ?></td>
                            <td><?php echo htmlspecialchars($reservation['brand'] . ' ' . $reservation['model']); ?></td>
                            <td><?php echo htmlspecialchars($reservation['start_date']); ?></td>
                            <td><?php echo htmlspecialchars($reservation['end_date']); ?></td>
                            <td><?php echo htmlspecialchars($reservation['cost']); ?></td> <!-- Affichage du coût -->
                            <td>
                                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
                                    <input type="hidden" name="id" value="<?php echo $reservation['id']; ?>">
                                    <button type="submit" name="edit">Modifier</button>
                                </form>
                                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
                                    <input type="hidden" name="id" value="<?php echo $reservation['id']; ?>">
                                    <button type="submit" name="cancel" onclick="return confirm('Êtes-vous sûr de vouloir annuler cette réservation?')">Annuler</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Votre JavaScript existant
    </script>

</body>
</html>
