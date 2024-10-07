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

// Vérifier si un ID de client est passé en paramètre
if (!isset($_GET['id'])) {
    header('Location: customers_management.php');
    exit;
}

$customer_id = $_GET['id'];

// Récupérer les informations actuelles du client
$stmt = $pdo->prepare('SELECT * FROM customers WHERE id = ?');
$stmt->execute([$customer_id]);
$customer = $stmt->fetch();

// Vérifier si le formulaire de modification a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['submit'])) {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];

        // Mettre à jour les informations du client dans la base de données
        $stmt = $pdo->prepare('UPDATE customers SET name = ?, email = ?, phone = ? WHERE id = ?');
        $stmt->execute([$name, $email, $phone, $customer_id]);

        // Redirection vers la page de gestion des clients après la modification
        header('Location: customers_management.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Client</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .form-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
        }

        .form-group input {
            width: calc(100% - 10px);
            padding: 8px;
            box-sizing: border-box;
        }

        .form-group button {
            padding: 10px 15px;
            background-color: #007BFF;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 10px;
        }

        .form-group button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Modifier Client</h2>
        <form action="<?php echo $_SERVER['PHP_SELF'] . '?id=' . $customer_id; ?>" method="POST">
            <div class="form-group">
                <label for="name">Nom :</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($customer['name']); ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email :</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($customer['email']); ?>" required>
            </div>
            <div class="form-group">
                <label for="phone">Téléphone :</label>
                <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($customer['phone']); ?>" required>
            </div>
            <div class="form-group">
                <button type="submit" name="submit">Enregistrer les Modifications</button>
            </div>
        </form>
    </div>
</body>
</html>
