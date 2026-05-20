<?php
session_start();
require 'config.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Ajouter élève
if (isset($_POST['ajouter'])) {
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $sexe = $_POST['sexe'];
    $classe = $_POST['classe'];
    $code = bin2hex(random_bytes(4));
    $email_parent = $_POST['email_parent'];

    $stmt = $conn->prepare("INSERT INTO eleves (nom, prenom, sexe, classe_id, code_parent, email_parent) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$nom, $prenom, $sexe, $classe, $code, $email_parent]);
}



// Supprimer élève
if (isset($_GET['supprimer'])) {
    $id = $_GET['supprimer'];
    $conn->query("DELETE FROM eleves WHERE id = $id");
}

// Recherche élèves
$recherche = isset($_POST['recherche']) ? trim($_POST['recherche']) : '';

// Liste élèves
if ($recherche !== '') {
    $stmt = $conn->prepare("
        SELECT e.*, c.nom_classe 
        FROM eleves e
        JOIN classes c ON e.classe_id = c.id
        WHERE e.nom LIKE ? OR e.prenom LIKE ?
        ORDER BY e.nom, e.prenom
    ");
    $search_term = "%{$recherche}%";
    $stmt->execute([$search_term, $search_term]);
    $eleves = $stmt->fetchAll();
} else {
    $eleves = $conn->query("
        SELECT e.*, c.nom_classe 
        FROM eleves e
        JOIN classes c ON e.classe_id = c.id
        ORDER BY e.nom, e.prenom
    ");
}

// Liste classes (pour select)
$classes = $conn->query("SELECT * FROM classes");


// Modifier élève
$eleve_modif = null;

if (isset($_GET['modifier'])) {
    $id = $_GET['modifier'];

    $stmt = $conn->prepare("SELECT * FROM eleves WHERE id = ?");
    $stmt->execute([$id]);
    $eleve_modif = $stmt->fetch();
}
if (isset($_POST['modifier'])) {
    $id = $_POST['id'];
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $sexe = $_POST['sexe'];
    $classe = $_POST['classe'];
    $email_parent = $_POST['email_parent'];

    $stmt = $conn->prepare(" UPDATE eleves 
       
        SET nom = ?, prenom = ?, sexe = ?, classe_id = ?, email_parent = ?
        WHERE id = ?");

    $stmt->execute([$nom, $prenom, $sexe, $classe, $email_parent, $id]);

    header("Location: eleve.php");
}
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
<h2>Gestion des élèves</h2>



<form method="POST" style="display: flex; gap: 10px; margin-bottom: 20px;">
    <input type="text" name="recherche" placeholder="rechercher un élève par nom ou prénom... " value="<?= htmlspecialchars($recherche) ?>">
    <button type="submit" style="padding: 10px 20px; background: #2c5aa0; color: white; border: none; border-radius: 5px; cursor: pointer;">Rechercher</button>
    <?php if ($recherche !== ''): ?>
        <a href="eleve.php" style="padding: 10px 20px; background: #666; color: white; text-decoration: none; border-radius: 5px; display: flex; align-items: center;">Réinitialiser</a>
    <?php endif; ?>
</form>

<form method="POST">
    <h3>Remplissez le formulaire ci-dessous pour ajouter ou modifier un élève </h3>
    <input type="hidden" name="id" value="<?= $eleve_modif['id'] ?? '' ?>">

    <input type="text" name="nom" placeholder="Nom"
        value="<?= $eleve_modif['nom'] ?? '' ?>" required>

    <input type="text" name="prenom" placeholder="Prénom"
        value="<?= $eleve_modif['prenom'] ?? '' ?>" required>

    <input type="text" name="email_parent" placeholder="Email du parent"
        value="<?= $eleve_modif['email_parent'] ?? '' ?>" required>

    <!-- Sexe -->
    <select name="sexe" required>
        <option value="">Choisir sexe</option>
        <option value="M" <?= (isset($eleve_modif) && $eleve_modif['sexe']=='M') ? 'selected' : '' ?>>Masculin</option>
        <option value="F" <?= (isset($eleve_modif) && $eleve_modif['sexe']=='F') ? 'selected' : '' ?>>Féminin</option>
    </select>

    
    <!-- Classe -->
    <select name="classe" required>
        <option value="">Choisir classe</option>
        <?php foreach ($classes as $c): ?>
            <option value="<?= $c['id'] ?>"
                <?= (isset($eleve_modif) && $eleve_modif['classe_id']==$c['id']) ? 'selected' : '' ?>>
                <?= $c['nom_classe'] ?>
            </option>
        <?php endforeach; ?>
    </select>

    <button name="<?= isset($eleve_modif) ? 'modifier' : 'ajouter' ?>">
        <?= isset($eleve_modif) ? 'Modifier' : 'Ajouter' ?>
    </button>

</form>


<table border="1">
<tr>
    <th>Nom</th>
    <th>Prénom</th>
    <th>Sexe</th>
    <th>Classe</th>
    <th>Email du parent</th>
    <th>Action</th>
</tr>

<?php foreach ($eleves as $e): ?>
<tr>
    <td><?= $e['nom'] ?></td>
    <td><?= $e['prenom'] ?></td>
    <td><?= $e['sexe'] ?></td>
    <td><?= $e['nom_classe'] ?></td>
    <td><?= $e['email_parent'] ?></td>
    <td>

    <a href="?modifier=<?= $e['id'] ?>" style="background:orange;">Modifier</a>
    <a href="?supprimer=<?= $e['id'] ?>">Supprimer</a>
</td>
        
</tr>
<?php endforeach; ?>

</table>

<a href="imprimerclasse.php" class="donn">Imprimer la liste d'une classe</a>
<?php include 'footer.php'; ?>
<style>
    div a {
        margin-left: 50px;
    }
    .donn{
        display: block;
        width: 250px;
        margin: 20px auto;
        padding: 10px;
        text-align: center;
        background: #2c5aa0;
        color: white;
        text-decoration: none;
        border-radius: 5px;
    }

</style>