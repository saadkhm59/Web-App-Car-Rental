<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier un véhicule</title>
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
        }

        .button-container button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <?php
    $dsn = "mysql:host=localhost;dbname=car_rental;charset=utf8";
    $pdo = new PDO($dsn, 'root', '');

    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
        $carId = $_GET['id'];
        $stmt = $pdo->prepare('SELECT * FROM cars WHERE id = ?');
        $stmt->execute([$carId]);
        $car = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$car) {
            die('Véhicule non trouvé.');
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $carId = $_POST['id'];
        $brand = $_POST['brand'];
        $model = $_POST['model'];
        $year = $_POST['year'];
        $registration_number = $_POST['registration_number'];
        $price_per_day = $_POST['price_per_day'];
        $status = $_POST['status'];
        $maintenance_due = $_POST['maintenance_due'];
        $documents = $_FILES['documents'];

        $stmt = $pdo->prepare('UPDATE cars SET brand = ?, model = ?, year = ?, registration_number = ?, price_per_day = ?, status = ?, maintenance_due = ? WHERE id = ?');
        $stmt->execute([$brand, $model, $year, $registration_number, $price_per_day, $status, $maintenance_due, $carId]);

        if ($documents['name'][0] != '') {
            $uploadDir = 'uploads/';
            foreach ($documents['tmp_name'] as $index => $tmpName) {
                $fileName = $carId . '_' . basename($documents['name'][$index]);
                $uploadFilePath = $uploadDir . $fileName;
                move_uploaded_file($tmpName, $uploadFilePath);

                $stmt = $pdo->prepare('INSERT INTO car_documents (car_id, document_path) VALUES (?, ?)');
                $stmt->execute([$carId, $uploadFilePath]);
            }
        }

        header('Location: vehicle_management.php');
    }
    ?>

    <div class="container">
        <h2>Modifier un véhicule</h2>
        <form action="" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($car['id']); ?>">
            <div class="form-group">
                <label for="brand">Marque</label>
                <input type="text" id="brand" name="brand" value="<?php echo htmlspecialchars($car['brand']); ?>" required>
            </div>
            <div class="form-group">
                <label for="model">Modèle</label>
                <input type="text" id="model" name="model" value="<?php echo htmlspecialchars($car['model']); ?>" required>
            </div>
            <div class="form-group">
                <label for="year">Année</label>
                <input type="number" id="year" name="year" value="<?php echo htmlspecialchars($car['year']); ?>" required>
            </div>
            <div class="form-group">
                <label for="registration_number">Immatriculation</label>
                <input type="text" id="registration_number" name="registration_number" value="<?php echo htmlspecialchars($car['registration_number']); ?>" required>
            </div>
            <div class="form-group">
                <label for="price_per_day">Prix par jour</label>
                <input type="number" id="price_per_day" name="price_per_day" value="<?php echo htmlspecialchars($car['price_per_day']); ?>" required>
            </div>
            
          
          
            <div class="button-container">
                <button type="submit">Mettre à jour</button>
            </div>
        </form>
    </div>
</body>
</html>
