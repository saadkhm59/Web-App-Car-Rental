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

// Fonction pour récupérer tous les clients avec leurs locations
function getAllCustomers($pdo) {
    $stmt = $pdo->query('
        SELECT customers.id, customers.name, customers.email, customers.phone, COUNT(reservations.id) AS reservations_count
        FROM customers
        LEFT JOIN reservations ON customers.id = reservations.customer_id
        GROUP BY customers.id
        ORDER BY customers.name ASC
    ');
    return $stmt->fetchAll();
}

// Récupérer tous les clients
$customers = getAllCustomers($pdo);

// Vérifier si le formulaire de suppression a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete'])) {
        $customer_id = $_POST['id'];

        // Supprimer les réservations associées à ce client
        $stmtDeleteReservations = $pdo->prepare('DELETE FROM reservations WHERE customer_id = ?');
        $stmtDeleteReservations->execute([$customer_id]);

        // Supprimer le client de la base de données
        $stmtDeleteCustomer = $pdo->prepare('DELETE FROM customers WHERE id = ?');
        $stmtDeleteCustomer->execute([$customer_id]);

        // Redirection pour éviter la réinscription lors de l'actualisation de la page
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    } elseif (isset($_POST['edit'])) {
        $customer_id = $_POST['id'];

        // Redirection vers la page de modification avec l'ID du client
        header('Location: edit_customer.php?id=' . $customer_id);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Clients</title>
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
        <h2>Gestion des Clients</h2>

        <!-- Bouton pour ajouter un nouveau client -->
        <div class="button-container">
            <button onclick="window.location.href='add_customer.php'">Ajouter un Client</button>
            <button onclick="window.location.href='home.php'">Retour à l'accueil</button>
        </div>

        <!-- Liste des clients -->
        <div class="list-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th>Nombre de Réservations</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($customers as $customer): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($customer['id']); ?></td>
                            <td><?php echo htmlspecialchars($customer['name']); ?></td>
                            <td><?php echo htmlspecialchars($customer['email']); ?></td>
                            <td><?php echo htmlspecialchars($customer['phone']); ?></td>
                            <td><?php echo htmlspecialchars($customer['reservations_count']); ?></td>
                            <td>
                                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
                                    <input type="hidden" name="id" value="<?php echo $customer['id']; ?>">
                                    <button type="submit" name="edit">Modifier</button>
                                    <button type="submit" name="delete" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce client?')">Supprimer</button>
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
