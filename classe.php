<?php
session_start();
require 'config.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Ajouter classe
if (isset($_POST['ajouter'])) {
    $nom = $_POST['nom'];

    $stmt = $conn->prepare("INSERT INTO classes (nom_classe) VALUES (?)");
    $stmt->execute([$nom]);
}

// Supprimer
if (isset($_GET['supprimer'])) {
    $id = $_GET['supprimer'];
    $conn->query("DELETE FROM classes WHERE id = $id");
}

// Liste classes
$classes = $conn->query("SELECT * FROM classes");
?>

   

 <header>
        <h1>GESTION DE PRÉSENCE DES ÉLÈVES</h1>
        <nav>
            <a href="logout.php">Déconnexion</a>
    <a href="acceuil.php" class="btn">⬅ Retour</a>
        </nav>
    </header>
<link rel="stylesheet" href=".css">
<style>
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
    text-decoration: none;
    color: white;
}
h2{
    margin-top: 90px;
}
</style>
<h2>Gestion des classes</h2>



<form method="POST">
    <input type="text" name="nom" placeholder="Nom classe" required>
    <button name="ajouter">Ajouter</button>
</form>

<table border="1">
<tr>
   
    <th>Nom</th>
    <th>Action</th>
</tr>

<?php foreach ($classes as $c): ?>
<tr>

    <td><?= $c['nom_classe'] ?></td>
    <td>
        <a href="?supprimer=<?= $c['id'] ?>">Supprimer</a>
    </td>
</tr>
<?php endforeach; ?>

</table>
<?php include 'footer.php'; ?>

