<?php
require 'config.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if(!isset($_POST['email'], $_POST['password'], $_POST['code'])) {
            die("Tous les champs sont requis");
        };
$email = $_POST['email'];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);
$code = $_POST['code'];}

// vérifier code
$sql = "SELECT * FROM eleves WHERE code_parent=? AND code_used=FALSE";
$stmt = $conn->prepare($sql);
$stmt->execute([$code]);

$eleve = $stmt->fetch();

if ($eleve) {

    // créer parent

    $sql2 = "INSERT INTO parents (email, mot_de_passe) VALUES (?, ?)";
    $stmt2 = $conn->prepare($sql2);
    $stmt2->execute([$email, $password]);

    $id_parent = $conn->lastInsertId();

    // lier élève au parent
    $sql3 = "UPDATE eleves SET id_parent=?, code_used=TRUE WHERE id_eleve=?";
    $stmt3 = $conn->prepare($sql3);
    $stmt3->execute([$id_parent, $eleve['id_eleve']]);

    echo "Inscription réussie";
} else {
    echo "Code invalide ou déjà utilisé";
}
?>
<form method="POST" action="inscription_parent.php">
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Mot de passe" required>
    <input type="text" name="code" placeholder="Code fourni par l'école" required>
    <button type="submit">S'inscrire</button>
</form>  