<?php

// connexion.php
session_start();

// Établir une connexion à la base de données
require 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = $_POST['user'];
    $mdp = $_POST['mdp'];
    $password = sha1($mdp);

    // Préparation de la requête
    $stmt = $conn->prepare("SELECT password FROM users WHERE username = ? ");
    $stmt->execute([$user]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        // Vérifier le mot de passe
        if (sha1($mdp) === $result['password']) {
            // Authentification réussie
            $_SESSION['username'] = $user; // Stocke le nom d'utilisateur dans la session
            header("Location: acceuil.php"); // Redirige vers la page d'accueil
            exit();
        } else {
            // Mot de passe incorrect
            $error = "Identifiant ou mot de passe incorrect.";
        }
    } else {
        // Nom d'utilisateur n'existe pas
        $error = "Identifiant ou mot de passe incorrect.";
    }
}

?>




<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Connexion</title>

<!-- ICONS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">


</head>

<body> <h2>connectez-vous pour gerer la présence</h2>

<form method="POST">
   
    <h1> connexion </h1>
    

    <div class="input-group">
        <i class="fa fa-user"></i>
        <input type="text" name="user" placeholder="Nom d'utilisateur" required>
    </div>

    <div class="input-group"> 
        <i class="fa fa-lock"></i>
        <input type="password" name="mdp" placeholder="Mot de passe" required>
    </div>

    <button type="submit" name="envoi">Se connecter</button>

    <?php if (isset($error)) { ?>
        <p class="error"><?= $error ?></p>
    <?php } ?>
   
    <div class="parent-link">
        <p>Vous êtes parent ? <a href="login_parent.php">Cliquez ici</a></p>
    </div>

    <div class="footer">
        Gestion de livraisons © 2026
    </div>

</form>


<style>
body {
    font-family: Arial, sans-serif;
    background-image: url('images/kenny-eliason-zFSo6bnZJTw-unsplash.jpg');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    height: 100vh;
}

/* CARTE */
form {
    background: white;
    padding: 40px;
    border-radius: 10px;
    width: 320px;
   
     background: rgba(255, 255, 255, 0.5);
}

/* TITRE */
h1 {
    text-align: center;
    margin-bottom: 20px;
    color: #2c5aa0;
}
h2 {
    text-align: center;
    margin-top: 0px;
    display: flex;
   
}

.subtitle {
    text-align: center;
    margin-bottom: 20px;
    color: #555;
    font-size: 14px;
}

/* INPUT GROUP */
.input-group {
    position: relative;
    margin-bottom: 20px;
    margin-right: 40px;
}

.input-group i {
    position: absolute;
    top: 12px;
    left: 5px;
    color: #888;
}

.input-group input {
    width: 100%;
    padding: 10px 10px 10px 25px;
    border: 1px solid #ccc;
    border-radius: 5px;
}

/* FOCUS */
input:focus {
    border-color: #2c5aa0;
    outline: none;
}

/* BUTTON */
button {
    width: 50%;
    padding: 10px;
    background: #2c5aa0;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-weight: bold;
    transition: 0.3s;
    margin-left: 75px;
}

button:hover {
    background: #1d3f73;
    transform: scale(1.03);
}

/* ERREUR */
.error {
    color: red;
    text-align: center;
    margin-top: 10px;
}

.parent-link {
    text-align: center;
    margin-top: 15px;
    font-size: 14px;
}

.parent-link p {
    margin: 0;
    color: #333;
}

.parent-link a {
    color: #2c5aa0;
    text-decoration: none;
    font-weight: bold;
    transition: color 0.3s;
}

.parent-link a:hover {
    color: #1d3f73;
    text-decoration: underline;
}

.footer {
    text-align: center;
    font-size: 12px;
    margin-top: 15px;
    color: #777;
    bottom: 0;
    width: 100%;
     
    }



</style>

</body>
</html>




 