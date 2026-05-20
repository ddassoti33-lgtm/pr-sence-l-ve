<?php
session_start();

require 'config.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Si une classe est choisie
$eleves = [];
$classe_id = null;
$message = null;
$date = date('Y-m-d');
$presenceAlreadyRecorded = false;

if (isset($_POST['classe'])) {
    $classe_id = $_POST['classe'];

    $stmt = $conn->prepare("SELECT e.*, p.statut
                             FROM eleves e
                             LEFT JOIN presences p
                               ON e.id = p.eleve_id
                               AND p.date_presence = ?
                             WHERE e.classe_id = ?
                             ORDER BY e.nom, e.prenom");
    $stmt->execute([$date, $classe_id]);
    $eleves = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($eleves as $e) {
        if (!empty($e['statut'])) {
            $presenceAlreadyRecorded = true;
            break;
        }
    }
}

// Enregistrer présence
if (isset($_POST['enregistrer'])) {
    $classe_id = $_POST['classe'];

    foreach ($_POST['presence'] as $eleve_id => $statut) {
        $stmt = $conn->prepare("INSERT INTO presences (eleve_id, date_presence, statut)
                                VALUES (?, ?, ?)
                                ON DUPLICATE KEY UPDATE statut = VALUES(statut)");
        $stmt->execute([$eleve_id, $date, $statut]);
    }

    $message = $presenceAlreadyRecorded ? "Présence mise à jour !" : "Présence enregistrée !";
    $presenceAlreadyRecorded = true;
}

// Liste classes
$classes = $conn->query("SELECT * FROM classes");
?>



<!DOCTYPE html>
<html>
<head>
    <title>Présence</title>
   
</head>
<body>
 <header>
        <h1>GESTION DE PRÉSENCE DES ÉLÈVES</h1>
        <nav>
            <a href="logout.php">Déconnexion</a>
        </nav>
 </header>
<div class="header">
    <h1 align="center"> Marquer la présence</h1>
    
</div>
<div>
    <a href="acceuil.php" class="btn">⬅ Retour</a>
</div><br/><br/>


<!-- Choisir classe -->
<div class="info">
    💡 <strong>Comment ça marche :</strong><br/> 
    -Sélectionnez une classe dans la liste déroulante.<br/> 
    -Les élèves s'affichent automatiquement, puis marquez leur présence  <br/>
    -et cliquez sur "Enregistrer".
</div>

<form method="POST" id="classForm">
    <label for="classe">Choisir une classe :</label>
    <select name="classe" id="classe" required onchange="this.form.submit()">
        <option value="">-- Sélectionnez une classe --</option>
        <?php foreach ($classes as $c): ?>
            <option value="<?= $c['id'] ?>" <?= (isset($classe_id) && $classe_id == $c['id']) ? 'selected' : '' ?>>
                <?= $c['nom_classe'] ?>
            </option>
        <?php endforeach; ?>
    </select>
</form>

<?php if (!empty($eleves)): ?>

<form method="POST">
    <input type="hidden" name="classe" value="<?= $classe_id ?>">

    <?php if ($presenceAlreadyRecorded): ?>
        <div class="info">
            ✅ La présence du jour est déjà enregistrée pour cette classe. Vous pouvez mettre à jour les statuts si nécessaire.
        </div>
    <?php endif; ?>

    <table>
        <tr>
            <th>Nom</th>
            <th>Prénom</th>
            <th>Présent</th>
            <th>Absent</th>
        </tr>

        <?php foreach ($eleves as $e): ?>
        <tr>
            <td><?= $e['nom'] ?></td>
            <td><?= $e['prenom'] ?></td>

            <td>
                <input type="radio" name="presence[<?= $e['id'] ?>]" value="present" <?= (isset($e['statut']) && $e['statut'] === 'present') ? 'checked' : '' ?> required>
            </td>

            <td>
                <input type="radio" name="presence[<?= $e['id'] ?>]" value="absent" <?= (isset($e['statut']) && $e['statut'] === 'absent') ? 'checked' : '' ?> >
            </td>
        </tr>
        <?php endforeach; ?>

    </table>

    <div style="text-align:center;">
        <button name="enregistrer">Enregistrer</button>
    </div>

</form>

<?php endif; ?>

<?php if (isset($message)): ?>
    <p class="success"><?= $message ?></p>
<?php endif; ?>







 <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { text-align: center;margin-top: 100px; }
        .btn { padding: 10px 15px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; }
        .btn:hover { background: #0056b3; }
        table {  width: 80%;
                 margin: 20px auto;
                 border-collapse: collapse;
                background: white;
                box-shadow: 0 4px 8px rgba(0,0,0,0.1); }

       
        th { background: #2c5aa0;
            color: white;
             padding: 10px;
            }

             td { padding: 10px;
            text-align: center;
             border-bottom: 1px solid #ddd;}

        select, button { padding: 8px; margin: 5px; }
        .success { color: green; text-align: center; font-weight: bold; margin-top: 15px; }
        .info { background: #e3f2fd; padding: 10px; border-radius: 5px; margin: 10px 0; }
        label { font-weight: bold; }
        .centered { text-align: center; }
        button { background: #2c5aa0; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; transition: 0.3s; }
        button:hover { background: #1d3f73; transform: scale(1.03); }
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