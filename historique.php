
<?php
session_start();
require 'config.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Filtres
$date = $_GET['date'] ?? date('Y-m-d');
$classe = $_GET['classe'] ?? '';
$statut = $_GET['statut'] ?? '';

// Requête de base
$sql = " SELECT e.nom, e.prenom, c.nom_classe, p.date_presence, p.statut
        FROM presences p
        JOIN eleves e ON p.eleve_id = e.id
        JOIN classes c ON e.classe_id = c.id
        WHERE 1
";

// Ajouter filtres
if (!empty($date)) {
    $sql .= " AND p.date_presence = '$date'";
}

if (!empty($classe)) {
    $sql .= " AND c.id = '$classe'";
}

if(!empty($statut)){
    $sql .= " AND p.statut = '$statut'";
}

$sql .= " ORDER BY p.date_presence DESC";

$historique = $conn->query($sql);

// Liste classes
$classes = $conn->query("SELECT * FROM classes");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Historique</title>
    <link rel="stylesheet" href=".css">
</head>
<body>
 <header>
        <h1>GESTION DE PRÉSENCE DES ÉLÈVES</h1>
        <nav>
            <a href="logout.php">Déconnexion</a>
            <a href="acceuil.php" class="btn">⬅ Retour</a>
        </nav>
    </header>
<h2>📊 Historique des présences</h2>


<!-- FILTRES -->
    <form method="GET" class="filtre-form">

        <input type="date" name="date" value="<?= $date ?>" max="<?= date('Y-m-d') ?>">

        <select name="classe">
            <option value="">Toutes les classes</option>
            <?php foreach ($classes as $c): ?>
                <option value="<?= $c['id'] ?>" <?= ($classe == $c['id']) ? 'selected' : '' ?>>
                    <?= $c['nom_classe'] ?>
                </option>

            <?php endforeach; ?>

        </select>
        <select name="statut">
            <option value="">Tous les statuts</option>
            <option value="present" <?= ($statut == 'present') ? 'selected' : '' ?>>Présent</option>
            <option value="absent" <?= ($statut == 'absent') ? 'selected' : '' ?>>Absent</option>

        </select>
        <button type="submit">Filtrer</button>

    </form>

<!-- TABLE -->
<table>
    <tr>
        <th>Nom</th>
        <th>Classe</th>
        <th>Date</th>
        <th>Statut</th>
    </tr>

    <?php foreach ($historique as $h): ?>
    <tr>
        <td><?= $h['nom'] . " " . $h['prenom'] ?></td>
        <td><?= $h['nom_classe'] ?></td>
        <td><?= $h['date_presence'] ?></td>
        <td>
            <?= $h['statut'] == 'present' 
                ? '<span class="present">Présent</span>' 
                : '<span class="absent">Absent</span>' ?>
        </td>
    </tr>
    <?php endforeach; ?>

</table>








<style>/* FORMULAIRE FILTRE */
h2{
    margin-top: 90px;
}
.filtre-form {
    width: 20%;
    margin: 20px auto;
    text-align: center;
}

.filtre-form input,
.filtre-form select {
    padding:  10px 0px;
    margin: 10px;
    border-radius: 5px;
    border: 1px solid #ccc;
}

.filtre-form button {
    padding: 10px 10px;
    background: #2c5aa0;
    color: white;
    border: none;
    border-radius: 5px;
}

/* STATUT */
.present {
    color: green;
    font-weight: bold;
}

.absent {
    color: red;
    font-weight: bold;
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
a{
   
    color: white;
}
</style>

<?php include 'footer.php'; ?>

</body>
</html>