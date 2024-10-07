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

// Fonction pour obtenir les performances des véhicules
function getVehiclePerformance($pdo) {
    $stmt = $pdo->query('
        SELECT cars.id, cars.brand, cars.model, SUM(reservations.kilometers) AS total_distance, SUM(reservations.fuel_used) AS total_fuel
        FROM cars
        INNER JOIN reservations ON cars.id = reservations.car_id
        GROUP BY cars.id
        ORDER BY total_distance DESC
    ');
    return $stmt->fetchAll();
}

// Fonction pour obtenir les rapports financiers
function getFinancialReports($pdo) {
    $stmt = $pdo->query('
        SELECT DATE_FORMAT(date, "%Y-%m") AS month, SUM(amount) AS total_revenue, 
               (SELECT SUM(cost) FROM maintenance WHERE DATE_FORMAT(maintenance.date, "%Y-%m") = DATE_FORMAT(payments.date, "%Y-%m")) AS total_maintenance_cost
        FROM payments
        GROUP BY month
        ORDER BY month DESC
    ');
    return $stmt->fetchAll();
}

// Obtenir les données pour les rapports
$vehiclePerformance = getVehiclePerformance($pdo);
$financialReports = getFinancialReports($pdo);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapports et Statistiques</title>
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
            margin-bottom: 20px;
        }

        .table th, .table td {
            border: 1px solid #ddd;
            padding: 8px;
        }

        .table th {
            background-color: #007BFF;
            color: white;
        }

        .chart-container {
            width: 100%;
            height: 400px;
            margin-bottom: 20px;
        }

        .button-container {
            text-align: right;
            margin-top: 20px;
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="container">
        <h2>Rapports et Statistiques</h2>

        <!-- Rapport sur les performances des véhicules -->
        <h3>Performances des Véhicules</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Marque</th>
                    <th>Modèle</th>
                    <th>Distance Totale</th>
                    <th>Consommation de Carburant Totale</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($vehiclePerformance as $performance): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($performance['id']); ?></td>
                        <td><?php echo htmlspecialchars($performance['brand']); ?></td>
                        <td><?php echo htmlspecialchars($performance['model']); ?></td>
                        <td><?php echo htmlspecialchars($performance['total_distance']); ?></td>
                        <td><?php echo htmlspecialchars($performance['total_fuel']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Rapport financier -->
        <h3>Rapports Financiers</h3>
        <div class="chart-container">
            <canvas id="financialChart"></canvas>
        </div>
        <table class="table">
            <thead>
                <tr>
                    <th>Mois</th>
                    <th>Revenu Total</th>
                    <th>Coût de Maintenance Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($financialReports as $report): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($report['month']); ?></td>
                        <td><?php echo htmlspecialchars($report['total_revenue']); ?></td>
                        <td><?php echo htmlspecialchars($report['total_maintenance_cost']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="button-container">
            <button onclick="window.location.href='home.php'">Retour à l'accueil</button>
        </div>
    </div>

    <script>
        // Graphique pour les rapports financiers
        const ctx = document.getElementById('financialChart').getContext('2d');
        const financialChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_column($financialReports, 'month')); ?>,
                datasets: [
                    {
                        label: 'Revenu Total',
                        data: <?php echo json_encode(array_column($financialReports, 'total_revenue')); ?>,
                        backgroundColor: 'rgba(0, 123, 255, 0.5)',
                        borderColor: 'rgba(0, 123, 255, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Coût de Maintenance Total',
                        data: <?php echo json_encode(array_column($financialReports, 'total_maintenance_cost')); ?>,
                        backgroundColor: 'rgba(255, 99, 132, 0.5)',
                        borderColor: 'rgba(255, 99, 132, 1)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html>
