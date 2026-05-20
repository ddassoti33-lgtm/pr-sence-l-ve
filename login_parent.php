<?php
session_start();
require 'config.php';

$message = '';
$error = '';

// Si le parent est déjà connecté
if (isset($_SESSION['parent_email'])) {
    header("Location: parent_dashboard.php");
    exit();
}

// Traiter la connexion
if (isset($_POST['connexion'])) {
    $email_parent = trim($_POST['email_parent']);
    
    if (empty($email_parent)) {
        $error = "Veuillez entrer l'email de votre enfant";
    } else {
        // Vérifier si cet email existe dans la table élèves
        $stmt = $conn->prepare("SELECT id, nom, prenom, email_parent, classe_id FROM eleves WHERE email_parent = ?");
        $stmt->execute([$email_parent]);
        $eleve = $stmt->fetch();
        
        if ($eleve) {
            // Créer une session pour le parent
            $_SESSION['parent_email'] = $email_parent;
            $_SESSION['eleve_id'] = $eleve['id'];
            $_SESSION['eleve_nom'] = $eleve['nom'];
            $_SESSION['eleve_prenom'] = $eleve['prenom'];
            $_SESSION['eleve_classe_id'] = $eleve['classe_id'];
            
            header("Location: parent_dashboard.php");
            exit();
        } else {
            $error = "Email non trouvé. Veuillez vérifier l'email de votre enfant.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Parent - Gestion de Présence</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .container {
            width: 100%;
            max-width: 450px;
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }

        .logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo h1 {
            color: #2c5aa0;
            font-size: 28px;
            margin-bottom: 10px;
        }

        .logo p {
            color: #666;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
            font-size: 14px;
        }

        input[type="email"],
        input[type="text"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        input[type="email"]:focus,
        input[type="text"]:focus {
            outline: none;
            border-color: #2c5aa0;
            background-color: #f8f9ff;
        }

        button {
            width: 100%;
            padding: 12px;
            background: #2c5aa0;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
            margin-top: 20px;
        }

        button:hover {
            background: #1e4080;
        }

        .message {
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 14px;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .footer-text {
            text-align: center;
            margin-top: 20px;
            color: #666;
            font-size: 13px;
        }

        .footer-text a {
            color: #2c5aa0;
            text-decoration: none;
            font-weight: 600;
        }

        .footer-text a:hover {
            text-decoration: underline;
        }

        .info-box {
            background: #e8f4f8;
            border-left: 4px solid #2c5aa0;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            font-size: 13px;
            color: #0c5460;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <h1>📚 Gestion de Présence</h1>
            <p>Espace Parent</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="message error">
                ⚠️ <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <div class="info-box">
            Connectez-vous en entrant l'adresse email associée au compte de votre enfant.
        </div>

        <form method="POST">
            <div class="form-group">
                <label for="email_parent"> votre email associé  :</label>
                <input type="email" id="email_parent" name="email_parent" placeholder="exemple@email.com" required>
            </div>

            <button type="submit" name="connexion">Se Connecter</button>
        </form>

        <div class="footer-text">
            <p>Besoin d'aide ? Contactez l'établissement.</p>
            <a href="acceuil.php">← je suis administrateur</a>
        </div>
    </div>
</body>
</html>
