<?php
session_start();
require 'config.php';


// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}





$date = $_GET['date'] ?? date('Y-m-d');
$classe = $_GET['classe'] ?? '';

// Total élèves
$nbEleves = $conn->query("SELECT COUNT(*) FROM eleves")->fetchColumn();

// Total classes
$nbClasses = $conn->query("SELECT COUNT(*) FROM classes")->fetchColumn();

// Présents aujourd’hui
$nbPresent = $conn->query("
    SELECT COUNT(*) 
    FROM presences 
    WHERE date_presence = CURDATE() AND statut = 'present'
")->fetchColumn();

// Absents aujourd’hui
$nbAbsent = $conn->query("
    SELECT COUNT(*) 
    FROM presences 
    WHERE date_presence = CURDATE() AND statut = 'absent'
")->fetchColumn();

// Dernières absences
$absences = $conn->query("
    SELECT e.nom, e.prenom, c.nom_classe, p.date_presence
    FROM presences p
    JOIN eleves e ON p.eleve_id = e.id
    JOIN classes c ON e.classe_id = c.id
    WHERE p.statut = 'absent' and p.date_presence = CURDATE()
    ORDER BY  e.nom 
   
    
");







?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>gestion de presence</title>
   
</head>
<body>
   

    <!-- HEADER -->
    <header>
        <h1>GESTION DE PRÉSENCE DES ÉLÈVES</h1>
        <nav>
            <a href="logout.php">Déconnexion</a>
        </nav>
    </header>

    <!-- TITRE -->
    <section class="welcome">
        <h2>Bienvenue <?= $_SESSION['username'] ?> !</h2>
        <p>Gérez facilement la présence des élèves</p>
    </section>

    <!-- CARDS -->
   <section class="cards">


    <div class="card blue">
        <h3>Total Classes</h3>
        <p><?= $nbClasses ?></p>
    </div>

    
    <div class="card green">
        <h3>Total Élèves</h3>
        <p><?= $nbEleves ?></p>
    </div>

    <div class="card orange">
        <h3>Présents Aujourd’hui</h3>
        <p><?= $nbPresent ?></p>
    </div>

    <div class="card red">
        <h3>Absents Aujourd’hui</h3>
        <p><?= $nbAbsent ?></p>
    </div>

</section>

    <!-- ACTIONS -->
    <section class="actions">
        <button><a href="classe.php">Gérer les Classes</a></button>
        <button><a href="eleve.php">Gérer les Élèves</a></button>
        <button><a href="presence.php">Marquer Présence</a></button>
        <button><a href="historique.php">Voir Historique</a></button>
    </section>


    <!-- TABLE -->
    <section class="table-section">
    <h3>📌 Dernières Absences </h3>

    <table>
        <tr>
            <th>Nom</th>
            <th>Classe</th>
            <th>Date</th>
        </tr>

        <?php if ($absences->rowCount() > 0): ?>
            <?php foreach ($absences as $a): ?>
            <tr>
                <td><?= $a['nom'] . "   " . $a['prenom'] ?></td>
                <td><?= $a['nom_classe'] ?></td>
                <td><?= $a['date_presence'] ?></td>
            </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="3">Aucune absence enregistrée aujourd'hui</td>
            </tr>
        <?php endif; ?>
                               
    </table>
</section>

<style>
body {
    font-family: Arial, sans-serif;
    margin: 0;
    background: #f4f6f9;
}

/* HEADER */
header {

    background: #2c5aa0;
    color: white;
    display: flex;
    justify-content: space-between;
    padding: 15px 30px;
    position: fixed;
    top: 0;
    left: 0;
    width: 98%;
    height: 60px;
    
}

header nav a {
    color: white;
    margin-left: 20px;
}

/* TITRE */
.welcome {
    text-align: center;
    margin: 30px 0;
    margin-top: 100px;
}

/* CARDS */
.cards {
    display: flex;
    justify-content: space-around;
    padding: 20px;
}

.card {
    padding: 20px;
    border-radius: 8px;
    color: white;
    width: 20%;
    text-align: center;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.green { background: #28a745; }
.blue { background: #007bff; }
.orange { background: #fd7e14; }
.red { background: #dc3545; }

/* ACTIONS */
.actions {
    text-align: center;
    margin: 20px;
}

.actions button {
    padding: 10px 20px;
    margin: 10px;
    border: none;
    background: #e0e3e7;
    color: white;
    border-radius: 5px;
    cursor: pointer;
}

.actions button:hover {
    background: #89b8f3;
}



/* TABLE */
.table-section {
    width: 80%;
    margin: auto;
    background: white;
    padding: 20px;
    border-radius: 8px;
}

table {
    width: 100%;
    border-collapse: collapse;
}

table th, table td {
    border: 1px solid #ddd;
    padding: 10px;
    text-align: center;
}

table th {
    background: #2c5aa0;
    color: white;
}
</style>





<?php include 'footer.php'; ?>

</body>
</html> 



