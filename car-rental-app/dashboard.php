<?php
include 'db.php';

// Total vehicles
$totalVehiclesStmt = $pdo->query('SELECT COUNT(*) AS total FROM cars');
$totalVehicles = $totalVehiclesStmt->fetch()['total'];

// Vehicles currently rented
$rentedVehiclesStmt = $pdo->query('SELECT COUNT(*) AS total FROM cars WHERE status = "En location"');
$rentedVehicles = $rentedVehiclesStmt->fetch()['total'];

// Available vehicles
$availableVehiclesStmt = $pdo->query('SELECT COUNT(*) AS total FROM cars WHERE status = "Disponible"');
$availableVehicles = $availableVehiclesStmt->fetch()['total'];

// Maintenance reminders
$maintenanceStmt = $pdo->query('SELECT COUNT(*) AS total FROM cars WHERE maintenance_due <= CURDATE()');
$maintenanceReminders = $maintenanceStmt->fetch()['total'];

// Fetch rental statistics for chart (group by month)
$rentalsPerMonthStmt = $pdo->query('
    SELECT MONTH(rental_date) AS month, COUNT(*) AS rentals
    FROM rentals
    WHERE rental_date >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)
    GROUP BY MONTH(rental_date)
    ORDER BY month
');

$rentalsPerMonth = $rentalsPerMonthStmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate monthly rentals for the current year
$currentYearRentals = [];
foreach ($rentalsPerMonth as $row) {
    $currentYearRentals[$row['month']] = $row['rentals'];
}

$months = range(1, 12); // 12 months in a year
foreach ($months as $month) {
    if (!isset($currentYearRentals[$month])) {
        $currentYearRentals[$month] = 0;
    }
}
ksort($currentYearRentals); // Sort by month number
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Rental - Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

        .dashboard-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 1200px;
        }

        .dashboard-container h2 {
            margin-bottom: 20px;
        }

        .stats-container {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
        }

        .stats-box {
            background-color: #007BFF;
            color: #fff;
            padding: 20px;
            border-radius: 8px;
            width: 24%;
            margin-bottom: 20px;
            text-align: center;
            cursor: pointer;
        }

        .stats-box:hover {
            background-color: #0056b3;
        }

        .stats-box h3 {
            margin: 0;
            font-size: 24px;
        }

        .stats-box p {
            margin: 5px 0 0;
            font-size: 18px;
        }

        .chart-container {
            margin-top: 20px;
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

        .button-container button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <h2>Tableau de Bord</h2>
        <div class="stats-container">
            <div class="stats-box" onclick="window.location.href='rented_vehicles.php'">
                <h3><?php echo $rentedVehicles; ?></h3>
                <p>Véhicules en location</p>
            </div>
            <div class="stats-box" onclick="window.location.href='available_vehicles.php'">
                <h3><?php echo $availableVehicles; ?></h3>
                <p>Véhicules disponibles</p>
            </div>
            <div class="stats-box" onclick="window.location.href='maintenance_vehicles.php'">
                <h3><?php echo $maintenanceReminders; ?></h3>
                <p>Rappels de maintenance</p>
            </div>
            <div class="stats-box">
                <h3><?php echo $totalVehicles; ?></h3>
                <p>Total des véhicules</p>
            </div>
        </div>
        <div class="chart-container">
            <canvas id="rentalsChart"></canvas>
        </div>
        <div class="button-container">
            <button onclick="window.location.href='home.php'">Retour</button>
            <button onclick="window.location.href='index.php'">Déconnexion</button>
        </div>
    </div>

    <script>
        const ctx = document.getElementById('rentalsChart').getContext('2d');
        const rentalsData = {
            labels: [
                <?php
                foreach (array_keys($currentYearRentals) as $month) {
                    echo '"' . date('F', mktime(0, 0, 0, $month, 10)) . '", ';
                }
                ?>
            ],
            datasets: [{
                label: 'Locations par mois',
                data: [
                    <?php
                    foreach (array_values($currentYearRentals) as $rentals) {
                        echo $rentals . ', ';
                    }
                    ?>
                ],
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        };

        const rentalsChart = new Chart(ctx, {
            type: 'bar',
            data: rentalsData,
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 30 // Set the maximum value for y-axis to 30
                    }
                }
            }
        });
    </script>
</body>
</html>
