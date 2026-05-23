<?php
$xml = simplexml_load_file("club.xml");

$concours_list = [];
if (isset($xml->listesConcours->concours)) {
    foreach ($xml->listesConcours->concours as $c) {
        $concours_list[] = $c;
    }
}

usort($concours_list, function($a, $b) {
    return strcmp((string)$a->date, (string)$b->date);
});

$count = count($concours_list);
$totalMembres = isset($xml->membres->membre) ? count($xml->membres->membre) : 0;

$totalPart = 0;
if (isset($xml->listesConcours->concours)) {
    foreach ($xml->listesConcours->concours as $c) {
        $totalPart += count($c->participants->participant);
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Club Info_Tech — Concours</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="page-wrapper">

    <nav class="navbar">
        <a class="navbar-brand" href="index.php">
            <span>Club Info_Tech</span>
        </a>
        <div class="navbar-links">
            <a href="index.php" class="active">Concours</a>
            <a href="inscription.php">Inscription</a>
            <a href="resultats.php">Résultats</a>
            <a href="requetes.php">XQuery</a>
        </div>
    </nav>

    <main class="main fade-in">

        <div class="page-header">
            <div class="page-header-left">
                <div class="page-tag">Tableau de bord</div>
                <h1>Liste des concours</h1>
            </div>
            <span class="page-count"><?php echo $count; ?> concours enregistrés</span>
        </div>

        <div class="stats-row">
            <div class="stat-card">
                <span class="stat-label">Concours actifs</span>
                <div class="stat-value"><?php echo $count; ?> <span>événements</span></div>
            </div>
            <div class="stat-card">
                <span class="stat-label">Membres inscrits</span>
                <div class="stat-value"><?php echo $totalMembres; ?> <span>adhérents</span></div>
            </div>
            <div class="stat-card">
                <span class="stat-label">Participations</span>
                <div class="stat-value"><?php echo $totalPart; ?> <span>scores</span></div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="card-header-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-6.75a1.125 1.125 0 0 0-1.125 1.125v3.375m9 0ZM9 10.5h.008v.008H9V10.5ZM15 10.5h.008v.008H15V10.5Z" />
                    </svg>
                </div>
                <span class="card-header-title">Concours disponibles</span>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Titre</th>
                            <th>Date</th>
                            <th>Catégorie</th>
                            <th>Coefficient</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $i = 1;
                    foreach($concours_list as $c):
                        $date = new DateTime($c->date);
                        $catRef = (string)$c['categorieRef'];
                        $categorieNode = $xml->xpath("//categorie[@id='$catRef']");
                        $libelleCat = isset($categorieNode[0]) ? (string)$categorieNode[0]->libelle : "Inconnue";
                        $coef = (string)$c['coefficient'];
                    ?>
                        <tr>
                            <td><span class="td-id"><?php echo str_pad($i++, 2, '0', STR_PAD_LEFT); ?></span></td>
                            <td><span class="td-main"><?php echo htmlspecialchars($c->titre); ?></span></td>
                            <td><span><?php echo $date->format('d M Y'); ?></span></td>
                            <td><span class="badge badge-gray"><?php echo htmlspecialchars($libelleCat); ?></span></td>
                            <td><span class="badge badge-success">×<?php echo htmlspecialchars($coef); ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </main>

    <footer>
        <span>Université de Skikda — Département Informatique — L3</span>
        <span>TP N°1 XML, XSD & XQuery</span>
    </footer>
</div>
</body>
</html>
