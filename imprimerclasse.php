<?php
session_start();

require 'config.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
$eleves = [];

if (isset($_GET['classe'])) {
    $classe_id = $_GET['classe'];

    $stmt = $conn->prepare("
        SELECT e.nom, e.prenom, e.sexe, c.nom_classe
        FROM eleves e
        JOIN classes c ON e.classe_id = c.id
        WHERE c.id = ?
        ORDER BY e.nom ASC, e.prenom ASC
    ");
    $stmt->execute([$classe_id]);
    $eleves = $stmt->fetchAll();
}

// Liste classes
$classes = $conn->query("SELECT * FROM classes");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Impression élèves</title>
    <link rel="stylesheet" href=".css">
    
</head>
<body>
     <header>
        <h1>GESTION DE PRÉSENCE DES ÉLÈVES</h1>
        <nav>
            <a href="logout.php">Déconnexion</a>
            <a href="eleve.php">Retour</a>
        </nav>
    </header>
    <h2>📄 Liste des élèves</h2>
    

<!-- Choix classe -->
<form method="GET">
    <select name="classe" required>
        <option value="">Choisir une classe</option>
        <?php foreach ($classes as $c): ?>
            <option value="<?= $c['id'] ?>">
                <?= $c['nom_classe'] ?>
            </option>
        <?php endforeach; ?>
    </select>

    <button type="submit">Afficher</button>
</form>

<?php if (!empty($eleves)): ?>

<h3 style="text-align:center;">
    Classe : <?= $eleves[0]['nom_classe'] ?>
</h3>
<table class="excel-table">
    <tr>
        <th>N°</th>
        <th>Nom</th>
        <th>Prénom</th>
        <th>Sexe</th>
        <th>Précence</th>
    </tr>

    <?php $i = 1; foreach ($eleves as $e): ?>
    <tr>
        <td><?= $i++ ?></td>
        <td><?= $e['nom'] ?></td>
        <td><?= $e['prenom'] ?></td>
        <td><?= $e['sexe'] ?></td>
        <td>présent___ absent___ </td>
    </tr>
    <?php endforeach; ?>
</table>



<div style="text-align:center; margin:20px;">
    <button onclick="window.print()">🖨️ Imprimer</button>
</div>

<?php endif; ?>







<style>
    h2{
        margin-top: 90px;
    }
.excel-table {
    width: 90%;
    margin: 20px auto;
    border-collapse: collapse;
    font-family: Arial;
}

/* Bordures complètes */
.excel-table th, 
.excel-table td {
    border: 1px solid black;
    padding: 8px;
    text-align: center;
}

/* En-tête */
.excel-table th {
    background-color: #d9e1f2;
    font-weight: bold;
    color: black;
}

/* Alternance de lignes */
.excel-table tr:nth-child(even) {
    background-color: #f2f2f2;
}

@media print {
    body {
        background: white;
    }

    .excel-table {
        width: 100%;
    }

    button, select {
        display: none;
    }
}
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


</style>

<?php include 'footer.php'; ?>

</body>
</html>