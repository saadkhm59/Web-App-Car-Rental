<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supprimer un véhicule</title>
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

        .button-container {
            margin-top: 20px;
            text-align: right;
        }

        .button-container button {
            padding: 10px 15px;
            background-color: #dc3545;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .button-container button:hover {
            background-color: #c82333;
        }

        .button-container .cancel-button {
            background-color: #6c757d;
            margin-right: 10px;
        }

        .button-container .cancel-button:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>
    <?php
    $dsn = "mysql:host=localhost;dbname=car_rental;charset=utf8";
    $pdo = new PDO($dsn, 'root', '');

    if (!isset($_GET['id'])) {
        die('ID du véhicule non spécifié.');
    }

    $id = $_GET['id'];
    $stmt = $pdo->prepare('SELECT * FROM cars WHERE id = ?');
    $stmt->execute([$id]);
    $vehicle = $stmt->fetch();

    if (!$vehicle) {
        die('Véhicule non trouvé.');
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $stmt = $pdo->prepare('DELETE FROM cars WHERE id = ?');
        $stmt->execute([$id]);

        $stmt = $pdo->prepare('DELETE FROM car_documents WHERE car_id = ?');
        $stmt->execute([$id]);

        header('Location: vehicle_management.php');
    }
    ?>

    <div class="container">
        <h2>Supprimer un véhicule</h2>
        <p>Êtes-vous sûr de vouloir supprimer le véhicule suivant ?</p>
        <ul>
            <li>Marque: <?php echo htmlspecialchars($vehicle['brand']); ?></li>
            <li>Modèle: <?php echo htmlspecialchars($vehicle['model']); ?></li>
            <li>Année: <?php echo htmlspecialchars($vehicle['year']); ?></li>
            <li>Immatriculation: <?php echo htmlspecialchars($vehicle['registration_number']); ?></li>
        </ul>
        <div class="button-container">
            <form action="delete_vehicle.php?id=<?php echo $id; ?>" method="POST">
                <button type="button" class="cancel-button" onclick="window.location.href='vehicle_management.php'">Annuler</button>
                <button type="submit">Supprimer</button>
            </form>
        </div>
    </div>
</body>
</html>
