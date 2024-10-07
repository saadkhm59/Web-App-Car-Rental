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

// Fonction pour récupérer tous les utilisateurs
function getAllUsers($pdo) {
    $stmt = $pdo->query('SELECT id, username, email, role FROM users');
    return $stmt->fetchAll();
}

// Fonction pour récupérer les paramètres généraux
function getSettings($pdo) {
    $stmt = $pdo->query('SELECT * FROM settings WHERE id = 1');
    return $stmt->fetch();
}

// Ajout d'un utilisateur
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = "admin";
    
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare('INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)');
    $stmt->execute([$username, $email, $hashed_password, $role]);
    
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Modification d'un utilisateur
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
    
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Suppression d'un utilisateur
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    $user_id = $_POST['user_id'];

    $stmt = $pdo->prepare('DELETE FROM users WHERE id = ?');
    $stmt->execute([$user_id]);
    
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Récupération de tous les utilisateurs
$users = getAllUsers($pdo);

// Récupération des paramètres généraux
$settings = getSettings($pdo);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paramètres</title>
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

        .form-container {
            width: 100%;
            max-width: 600px;
            margin-top: 20px;
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
        .form-container form input[type="password"],
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
        .button-container {
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
        }

        .button-container button {
            padding: 10px 15px;
            background-color: #007BFF;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .user-list {
            width: 100%;
            max-width: 600px;
            margin-top: 20px;
        }

        .user-list table {
            width: 100%;
            border-collapse: collapse;
        }

        .user-list table th, .user-list table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        .user-list table th {
            background-color: #007BFF;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Paramètres</h2>

        <!-- Gestion des utilisateurs -->
        <div class="form-container">
            <h3>Gestion des Utilisateurs</h3>
            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
                <label for="username">Nom d'utilisateur :</label>
                <input type="text" id="username" name="username" required>
                <label for="email">Email :</label>
                <input type="email" id="email" name="email" required>
                <label for="password">Mot de passe :</label>
                <input type="password" id="password" name="password" required>
  
                <button type="submit" name="add_user">Ajouter Utilisateur</button>
            </form>

            <!-- Liste des utilisateurs -->
            <div class="user-list">
                <h3>Liste des Utilisateurs</h3>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom d'utilisateur</th>
                            <th>Email</th>
      
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['id']); ?></td>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
              
                                <td>
                          
                                    <!-- Bouton pour la suppression -->
                                    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST" style="display:inline-block;">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <button type="submit" name="delete_user" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur?')">Supprimer</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="button-container">
            <button onclick="window.location.href='home.php'">Retour</button>
        </div>
    </div>
</body>
</html>
