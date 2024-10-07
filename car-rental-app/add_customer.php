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

// Initialisation des variables pour les valeurs par défaut
$name = '';
$email = '';
$phone = '';
$success = '';

// Traitement du formulaire lors de la soumission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération des données du formulaire
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];

    // Validation rapide (à personnaliser selon vos besoins)
    if (!empty($name) && !empty($email) && !empty($phone)) {
        try {
            // Connexion à la base de données avec PDO
            $pdo = new PDO($dsn, $user, $pass, $options);

            // Préparation de la requête d'insertion
            $stmt = $pdo->prepare('INSERT INTO customers (name, email, phone) VALUES (?, ?, ?)');
            $stmt->execute([$name, $email, $phone]);

            // Redirection vers la page de gestion des clients après l'ajout
            header('Location: customers_management.php');
            exit;

        } catch (\PDOException $e) {
            die("Erreur lors de l'ajout du client: " . $e->getMessage());
        }
    } else {
        // Message d'erreur si des champs sont vides (à personnaliser selon vos besoins)
        $error = 'Veuillez remplir tous les champs!';
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un Client</title>
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

        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 400px;
            max-width: 100%;
            text-align: center;
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
            font-weight: bold;
        }

        .form-group input {
            width: calc(100% - 10px);
            padding: 8px;
            box-sizing: border-box;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .form-group input[type="submit"] {
            width: 100%;
            background-color: #007BFF;
            color: #fff;
            border: none;
            padding: 10px;
            cursor: pointer;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }

        .form-group input[type="submit"]:hover {
            background-color: #0056b3;
        }

        .error-message {
            color: red;
            margin-top: 10px;
        }

        .success-message {
            color: green;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Ajouter un Client</h2>

        <!-- Formulaire d'ajout de client -->
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
            <div class="form-group">
                <label for="name">Nom :</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email :</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
            </div>
            <div class="form-group">
                <label for="phone">Téléphone :</label>
                <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($phone); ?>" required>
            </div>
            <div class="form-group">
                <input type="submit" value="Ajouter Client">
            </div>
        </form>

        <!-- Affichage des messages d'erreur ou de succès -->
        <?php if (!empty($error)): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <div class="success-message"><?php echo $success; ?></div>
        <?php endif; ?>
        
    </div>
</body>
</html>
