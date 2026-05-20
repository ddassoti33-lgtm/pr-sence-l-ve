<?php
session_start();
require 'config.php';

// Vérifier si le parent est connecté
if (!isset($_SESSION['parent_email'])) {
    header("Location: login_parent.php");
    exit();
}

$eleve_id = $_SESSION['eleve_id'];
$eleve_nom = $_SESSION['eleve_nom'];
$eleve_prenom = $_SESSION['eleve_prenom'];
$parent_email = $_SESSION['parent_email'];

// Récupérer les informations complètes de l'élève
$stmt = $conn->prepare("
    SELECT e.*, c.nom_classe 
    FROM eleves e
    JOIN classes c ON e.classe_id = c.id
    WHERE e.id = ?
");
$stmt->execute([$eleve_id]);
$eleve = $stmt->fetch();

// Récupérer toutes les présences de l'élève
$stmt = $conn->prepare("
    SELECT * FROM presences 
    WHERE eleve_id = ? 
    ORDER BY date_presence DESC
");
$stmt->execute([$eleve_id]);
$presences = $stmt->fetchAll();

// Calculer les statistiques
$stats = $conn->query("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN statut = 'present' THEN 1 ELSE 0 END) as presents,
        SUM(CASE WHEN statut = 'absent' THEN 1 ELSE 0 END) as absents
    FROM presences 
    WHERE eleve_id = $eleve_id
")->fetch();

// Calculer le taux de présence
$taux_presence = $stats['total'] > 0 ? round(($stats['presents'] / $stats['total']) * 100, 2) : 0;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord Parent</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
        }

        header {
            background: #2c5aa0;
            color: white;
            padding: 20px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        header h1 {
            font-size: 24px;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .user-info {
            text-align: right;
        }

        .user-info p {
            font-size: 12px;
            opacity: 0.9;
        }

        header a, header button {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
            transition: background 0.3s;
        }

        header a:hover, header button:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px 20px;
        }

        .eleve-card {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            margin-bottom: 30px;
            border-left: 5px solid #2c5aa0;
        }

        .eleve-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .info-item {
            background: #f8f9fa;
            padding: 12px;
            border-radius: 5px;
        }

        .info-label {
            color: #666;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .info-value {
            color: #333;
            font-size: 18px;
            font-weight: 600;
        }

        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }

        .stat-box {
            background: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            border-top: 4px solid #2c5aa0;
        }

        .stat-box.present {
            border-top-color: #28a745;
        }

        .stat-box.absent {
            border-top-color: #dc3545;
        }

        .stat-box.taux {
            border-top-color: #ffc107;
        }

        .stat-number {
            font-size: 32px;
            font-weight: 700;
            color: #2c5aa0;
            margin: 10px 0;
        }

        .stat-box.present .stat-number {
            color: #28a745;
        }

        .stat-box.absent .stat-number {
            color: #dc3545;
        }

        .stat-box.taux .stat-number {
            color: #ffc107;
        }

        .stat-label {
            color: #666;
            font-size: 13px;
            font-weight: 600;
        }

        .presence-section {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        }

        .presence-section h2 {
            color: #2c5aa0;
            margin-bottom: 20px;
            font-size: 20px;
            border-bottom: 2px solid #2c5aa0;
            padding-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th {
            background: #f8f9fa;
            color: #333;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            border-bottom: 2px solid #dee2e6;
            font-size: 13px;
        }

        table td {
            padding: 12px;
            border-bottom: 1px solid #dee2e6;
        }

        table tbody tr:hover {
            background: #f8f9fa;
        }

        .statut {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .statut.present {
            background: #d4edda;
            color: #155724;
        }

        .statut.absent {
            background: #f8d7da;
            color: #721c24;
        }

        .no-data {
            text-align: center;
            padding: 40px;
            color: #999;
            font-size: 16px;
        }

        .pagination {
            margin-top: 20px;
            text-align: center;
        }

        .pagination a, .pagination span {
            padding: 8px 12px;
            margin: 0 3px;
            border: 1px solid #ddd;
            border-radius: 5px;
            color: #2c5aa0;
            text-decoration: none;
            display: inline-block;
        }

        .pagination a:hover {
            background: #2c5aa0;
            color: white;
        }

        .date {
            font-size: 13px;
            color: #666;
        }

        @media (max-width: 768px) {
            .header-right {
                flex-direction: column;
                gap: 10px;
            }

            .eleve-info {
                grid-template-columns: 1fr;
            }

            .stats-container {
                grid-template-columns: 1fr;
            }

            table {
                font-size: 13px;
            }

            table th, table td {
                padding: 8px;
            }
        }
    </style>
</head>
<body>
    <header>
        <h1>📚 Gestion de Présence - Espace Parent</h1>
        <div class="header-right">
            <div class="user-info">
                <p>Connecté en tant que parent</p>
                <p style="font-size: 13px;"><?= htmlspecialchars($parent_email) ?></p>
            </div>
            <a href="logout_parent.php">Déconnexion</a>
        </div>
    </header>

    <div class="container">
        <!-- Informations de l'élève -->
        <div class="eleve-card">
            <h2 style="margin-bottom: 20px; color: #2c5aa0;">Informations de l'élève</h2>
            <div class="eleve-info">
                 <div class="info-item">
                    <div class="info-label">Nom</div>
                    <div class="info-value"><?= htmlspecialchars($eleve['nom']) ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Prénom</div>
                    <div class="info-value"><?= htmlspecialchars($eleve['prenom']) ?></div>
                </div>
            
                <div class="info-item">
                    <div class="info-label">Classe</div>
                    <div class="info-value"><?= htmlspecialchars($eleve['nom_classe']) ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Sexe</div>
                    <div class="info-value"><?= $eleve['sexe'] === 'M' ? 'Masculin' : 'Féminin' ?></div>
                </div>
            </div>
        </div>

        <!-- Statistiques -->
        <div class="stats-container">
            <div class="stat-box present">
                <div class="stat-label">Présences</div>
                <div class="stat-number"><?= $stats['presents'] ?? 0 ?></div>
            </div>
            <div class="stat-box absent">
                <div class="stat-label">Absences</div>
                <div class="stat-number"><?= $stats['absents'] ?? 0 ?></div>
            </div>
            <div class="stat-box taux">
                <div class="stat-label">Taux de Présence</div>
                <div class="stat-number"><?= $taux_presence ?>%</div>
            </div>
            <div class="stat-box">
                <div class="stat-label">Total Jours</div>
                <div class="stat-number"><?= $stats['total'] ?? 0 ?></div>
            </div>
        </div>

        <!-- Historique des présences -->
        <div class="presence-section">
            <h2>Historique des Présences</h2>
            
            <?php if (count($presences) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_slice($presences, 0, 30) as $p): ?>
                            <tr>
                                <td class="date"><?= date('d/m/Y', strtotime($p['date_presence'])) ?> (<?= strftime('%A', strtotime($p['date_presence'])) ?>)</td>
                                <td>
                                    <span class="statut <?= strtolower($p['statut']) ?>">
                                        <?= ucfirst($p['statut']) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php if (count($presences) > 30): ?>
                    <p style="margin-top: 15px; text-align: center; color: #999; font-size: 13px;">
                        Affichage des 30 derniers jours (<?= count($presences) ?> enregistrements au total)
                    </p>
                <?php endif; ?>
            <?php else: ?>
                <div class="no-data">
                    📭 Aucune présence enregistrée pour le moment.
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
