<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des véhicules</title>
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
            margin: 5px;
        }

        .button-container button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <?php
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

    // Fetch all vehicles
    $vehiclesStmt = $pdo->query('SELECT * FROM cars');
    $vehicles = $vehiclesStmt->fetchAll();
    ?>

    <div class="container">
        <h2>Gestion des véhicules</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Marque</th>
                    <th>Modèle</th>
                    <th>Année</th>
                    <th>Immatriculation</th>
                    <th>Prix par jour</th>
                  
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($vehicles as $vehicle): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($vehicle['id']); ?></td>
                        <td><?php echo htmlspecialchars($vehicle['brand']); ?></td>
                        <td><?php echo htmlspecialchars($vehicle['model']); ?></td>
                        <td><?php echo htmlspecialchars($vehicle['year']); ?></td>
                        <td><?php echo htmlspecialchars($vehicle['registration_number']); ?></td>
                        <td><?php echo htmlspecialchars($vehicle['price_per_day']); ?></td>
                  
                        <td>
                            <button onclick="window.location.href='edit_vehicle.php?id=<?php echo $vehicle['id']; ?>'">Modifier</button>
                            <button onclick="deleteVehicle(<?php echo $vehicle['id']; ?>)">Supprimer</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="button-container">
            <button onclick="window.location.href='add_vehicle.php'">Ajouter un véhicule</button>
            <button onclick="window.location.href='home.php'">Retour</button>
        </div>
    </div>

    <script>
        function deleteVehicle(id) {
            if (confirm('Êtes-vous sûr de vouloir supprimer ce véhicule ?')) {
                window.location.href = 'delete_vehicle.php?id=' + id;
            }
        }
    </script>
</body>
</html>
