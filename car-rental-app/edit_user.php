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

// Récupérer les informations de l'utilisateur à modifier
if (isset($_GET['id'])) {
    $user_id = $_GET['id'];

    // Debugging: Verify the received user_id
    if (empty($user_id)) {
        die("ID utilisateur manquant (GET) !");
    }

    $stmt = $pdo->prepare('SELECT id, username, email, role FROM users WHERE id = ?');
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    // Debugging: Check if user data was retrieved
    if (!$user) {
        die("Utilisateur non trouvé !");
    }
} else {
    die("ID utilisateur manquant !");
}

// Mettre à jour l'utilisateur
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
    if (!isset($_POST['user_id'])) {
        die("ID utilisateur manquant dans le formulaire de mise à jour !");
    }

    $user_id = $_POST['user_id'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $role = $_POST['role'];

    $stmt = $pdo->prepare('UPDATE users SET username = ?, email = ?, role = ? WHERE id = ?');
    $stmt->execute([$username, $email, $role, $user_id]);
    
    header('Location: settings.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Utilisateur</title>
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

        .form-container {
            width: 100%;
        }

        .form-container form {
            background-color: #f9f9f9;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .form-container form label {
            display: block;
            margin-bottom: 10px;
        }

        .form-container form input[type="text"],
        .form-container form input[type="email"],
        .form-container form select {
            width: calc(100% - 30px);
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .form-container form button {
            padding: 10px 15px;
            background-color: #007BFF;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .form-container form button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Modifier Utilisateur</h2>
        <div class="form-container">
            <form action="edit_user.php" method="POST">
                <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user['id']); ?>">
                <label for="username">Nom d'utilisateur :</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                <label for="email">Email :</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                <label for="role">Rôle :</label>
                <input type="text" id="role" name="role" value="<?php echo htmlspecialchars($user['role']); ?>" required>
                <button type="submit" name="update_user">Mettre à jour</button>
            </form>
        </div>
    </div>
</body>
</html>
