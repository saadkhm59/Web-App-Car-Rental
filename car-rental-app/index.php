<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Rental - Login</title>
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

        .login-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            width: 100%;
        }

        .login-container h2 {
            margin-bottom: 20px;
        }

        .login-container form div {
            margin-bottom: 15px;
        }

        .login-container label {
            display: block;
            margin-bottom: 5px;
        }

        .login-container input {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
        }

        .login-container button {
            width: 100%;
            padding: 10px;
            background-color: #007BFF;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .login-container button:hover {
            background-color: #0056b3;
        }

        .error {
            color: red;
        }
    </style>
</head>
<body>
    <?php
    $error = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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

        $username = $_POST['username'];
        $password = $_POST['password'];

        $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ? AND password = ?');
        $stmt->execute([$username, $password]);
        $user = $stmt->fetch();

        if ($user) {
            header('Location: home.php');
            exit;
        } else {
            $error = 'Nom d\'utilisateur ou mot de passe incorrect';
        }
    }
    ?>

    <div class="login-container">
        <h2>Connexion</h2>
        <form method="POST" action="">
            <div>
                <label for="username">Nom d'utilisateur :</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div>
                <label for="password">Mot de passe :</label>
                <input type="password" id="password" name="password" required>
            </div>
            <?php if ($error): ?>
                <p class="error"><?php echo $error; ?></p>
            <?php endif; ?>
            <button type="submit">Se connecter</button>
        </form>
    </div>
</body>
</html>
