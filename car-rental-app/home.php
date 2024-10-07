<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Rental - Home</title>
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

        .home-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-width: 800px;
            width: 100%;
            text-align: center;
        }

        .home-container h1 {
            color: #007BFF;
        }

        .home-container h2 {
            margin-bottom: 20px;
            color: #333;
        }

        .company-logo {
            width: 300px;
            height: auto;
            margin-bottom: 20px;
        }

        .button-container {
            margin-top: 20px;
        }

        .button-container button {
            padding: 10px 15px;
            background-color: #007BFF;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin: 5px;
            transition: background-color 0.3s ease;
        }

        .button-container button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="home-container">
        <img src="car_rental_image.jpg" alt="Car Rental" class="company-logo">
        <h1>G-ONE TOURS</h1>
        <h2>Bienvenue dans le système de location de voitures</h2>
        <div class="button-container">
            <button onclick="window.location.href='dashboard.php'">Tableau de bord</button>
            <button onclick="window.location.href='vehicle_management.php'">Gestion des véhicules</button>
            <button onclick="window.location.href='reservation_management.php'">Réservations</button>
            <button onclick="window.location.href='customers_management.php'">Gestion des clients</button>
            <button onclick="window.location.href='maintenance_management.php'">Maintenance</button>
            <button onclick="window.location.href='settings.php'">Gestion utilisateur</button>
            <button onclick="window.location.href='index.php'">Déconnexion</button>
        </div>
    </div>
</body>
</html>
