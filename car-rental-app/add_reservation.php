<?php
ob_start(); // Démarre la temporisation de la sortie
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter une réservation avec client optionnel</title>
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
    <div class="container">
        <h2>Ajouter une réservation avec client optionnel</h2>
        <form action="add_reservation.php" method="POST" onsubmit="return calculateCost();">
            <div class="form-group">
                <label for="existing_customer">Client existant (optionnel)</label>
                <select id="existing_customer" name="existing_customer">
                    <option value="">Choisir un client existant</option>
                    <?php
                    // Connexion à la base de données
                    $dsn = "mysql:host=localhost;dbname=car_rental;charset=utf8";
                    $pdo = new PDO($dsn, 'root', '');

                    // Récupérer la liste des clients existants
                    $customersStmt = $pdo->query('SELECT id, name FROM customers');
                    $customers = $customersStmt->fetchAll();

                    foreach ($customers as $customer) {
                        echo '<option value="' . htmlspecialchars($customer['id']) . '">' . htmlspecialchars($customer['name']) . '</option>';
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label for="name">Nom du client (nouveau)</label>
                <input type="text" id="name" name="name">
            </div>
            <div class="form-group">
                <label for="email">Email du client (nouveau)</label>
                <input type="email" id="email" name="email">
            </div>
            <div class="form-group">
                <label for="phone">Téléphone du client (nouveau)</label>
                <input type="text" id="phone" name="phone">
            </div>
            <hr>
            <div class="form-group">
                <label for="car_id">Véhicule</label>
                <select id="car_id" name="car_id" required onchange="updateCarPrice()">
                    <option value="">Choisir un véhicule</option>
                    <?php
                    // Récupérer la liste des véhicules disponibles avec leur prix par jour
                    $carsStmt = $pdo->query('SELECT id, brand, model, price_per_day FROM cars WHERE status = "Disponible"');
                    $cars = $carsStmt->fetchAll();

                    foreach ($cars as $car) {
                        echo '<option value="' . htmlspecialchars($car['id']) . '" data-price="' . htmlspecialchars($car['price_per_day']) . '">' . htmlspecialchars($car['brand']) . ' ' . htmlspecialchars($car['model']) . '</option>';
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label for="start_date">Date de début</label>
                <input type="date" id="start_date" name="start_date" required onchange="calculateCost()">
            </div>
            <div class="form-group">
                <label for="end_date">Date de fin</label>
                <input type="date" id="end_date" name="end_date" required onchange="calculateCost()">
            </div>
            <div class="form-group">
                <label for="cost">Coût de la réservation</label>
                <input type="text" id="cost" name="cost" readonly>
            </div>
            <div class="button-container">
                <button type="submit">Ajouter Réservation</button>
            </div>
        </form>
    </div>

    <script>
        function updateCarPrice() {
            const carSelect = document.getElementById('car_id');
            const selectedOption = carSelect.options[carSelect.selectedIndex];
            const pricePerDay = selectedOption.getAttribute('data-price');
            carSelect.setAttribute('data-price-per-day', pricePerDay);
            calculateCost();
        }

        function calculateCost() {
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;
            const pricePerDay = document.getElementById('car_id').getAttribute('data-price-per-day');

            if (startDate && endDate && pricePerDay) {
                const start = new Date(startDate);
                const end = new Date(endDate);
                const timeDiff = end - start;
                const days = Math.ceil(timeDiff / (1000 * 60 * 60 * 24)) + 1; // Inclure le jour de début et de fin
                const cost = days * pricePerDay;
                document.getElementById('cost').value = cost.toFixed(2);
            }
            return true;
        }
    </script>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer les données du formulaire pour la réservation
    $car_id = $_POST['car_id'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $cost = $_POST['cost']; // Assurez-vous que le formulaire envoie bien le coût

    // Vérifier la disponibilité de la voiture pour les dates spécifiées
    $stmt = $pdo->prepare('
        SELECT COUNT(*) as count
        FROM reservations
        WHERE car_id = :car_id
        AND ((start_date <= :start_date AND end_date >= :start_date)
             OR (start_date <= :end_date AND end_date >= :end_date)
             OR (:start_date <= start_date AND :end_date >= end_date))
    ');
    $stmt->execute([
        'car_id' => $car_id,
        'start_date' => $start_date,
        'end_date' => $end_date
    ]);
    $result = $stmt->fetchColumn();

    if ($result > 0) {
        echo '<p style="color: red;">La voiture sélectionnée est déjà réservée pour cette période. Veuillez choisir une autre voiture ou ajuster les dates.</p>';
    } else {
        // Vérifier si un client existant a été choisi
        if (!empty($_POST['existing_customer'])) {
            $customer_id = $_POST['existing_customer'];
        } else {
            // Récupérer les données du formulaire pour le nouveau client
            $name = $_POST['name'];
            $email = $_POST['email'];
            $phone = $_POST['phone'];

            // Insertion du nouveau client dans la base de données
            $stmt = $pdo->prepare('INSERT INTO customers (name, email, phone) VALUES (?, ?, ?)');
            $stmt->execute([$name, $email, $phone]);

            // Récupérer l'ID du client inséré
            $customer_id = $pdo->lastInsertId();
        }

        // Insertion de la réservation dans la base de données
        $stmt = $pdo->prepare('INSERT INTO reservations (customer_id, car_id, start_date, end_date, cost, status) VALUES (?, ?, ?, ?, ?, "En cours")');
        $stmt->execute([$customer_id, $car_id, $start_date, $end_date, $cost]);

        // Mettre à jour le statut de la voiture en fonction de la date actuelle
        $current_date = date('Y-m-d');
        if ($current_date >= $start_date && $current_date <= $end_date) {
            // Si nous sommes dans la période de réservation
            $stmt = $pdo->prepare('UPDATE cars SET status = "En location" WHERE id = ?');
            $stmt->execute([$car_id]);
        } else {
            // Si nous ne sommes pas dans la période de réservation
            $stmt = $pdo->prepare('UPDATE cars SET status = "Disponible" WHERE id = ?');
            $stmt->execute([$car_id]);
        }

        // Redirection vers la page de gestion des réservations après l'ajout
        header('Location: reservation_management.php');
        exit();
    }
}
?>

</body>
</html>

<?php
ob_end_flush(); // Envoie le tampon de sortie et désactive la temporisation de sortie
?>
