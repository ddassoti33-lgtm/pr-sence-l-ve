<?php
session_start();

// Détruire la session du parent
session_destroy();

// Rediriger vers la page de connexion
header("Location: login_parent.php");
exit();
?>
