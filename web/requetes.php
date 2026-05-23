<?php
$resultat      = [];
$requeteSaisie = '';
$queryMeta     = null;
$error         = '';

$queries = [
    'categories'    => ['label' => 'Liste des catégories'],
    'membres'       => ['label' => 'Liste des membres du club'],
    'concours'      => ['label' => 'Liste des concours'],
    'scores'        => ['label' => 'Calcul des scores globaux'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['requete'])) {
    $requeteSaisie = trim($_POST['requete']);
    $xml           = simplexml_load_file("club.xml");
    $key           = strtolower($requeteSaisie);

    if ($key === 'categories') {
        $queryMeta = $queries['categories'];
        foreach($xml->categories->categorie as $c) {
            $resultat[] = "[" . (string)$c['id'] . "] " . htmlspecialchars($c->libelle);
        }
    } elseif ($key === 'membres') {
        $queryMeta = $queries['membres'] ?? null;
        foreach($xml->membres->membre as $m) {
            $resultat[] = "[" . (string)$m['id'] . "] " . htmlspecialchars($m->nom) . " " . htmlspecialchars($m->prenom) . " — " . htmlspecialchars($m->email);
        }
    } elseif ($key === 'concours') {
        $queryMeta = $queries['concours'];
        foreach($xml->listesConcours->concours as $c) {
            $resultat[] = "[" . (string)$c['id'] . "] " . htmlspecialchars($c->titre) . " (" . (string)$c->date . ") — Coef: " . (string)$c['coefficient'];
        }
    } elseif ($key === 'scores') {
        $queryMeta = $queries['scores'];
        foreach($xml->listesConcours->concours as $c) {
            $coef = floatval($c['coefficient']);
            if (isset($c->participants->participant)) {
                foreach($c->participants->participant as $p) {
                    $mRef = (string)$p['membreRef'];
                    $membreNode = $xml->xpath("//membre[@id='$mRef']");
                    $nom = isset($membreNode[0]) ? (string)$membreNode[0]->nom . " " . (string)$membreNode[0]->prenom : $mRef;
                    
                    $score = (intval($p->complexite) + intval($p->tempsExecution)) * $coef;
                    $resultat[] = "Concours: " . htmlspecialchars($c->titre) . " | Participant: " . htmlspecialchars($nom) . " | Score: " . $score . " pts";
                }
            }
        }
    } else {
        $error = "Requête inconnue ou non prise en charge par le simulateur PHP.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Club Info_Tech — XQuery Simulator</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="page-wrapper">

    <nav class="navbar">
        <a class="navbar-brand" href="index.php">
            <span>Club Info_Tech</span>
        </a>
        <div class="navbar-links">
            <a href="index.php">Concours</a>
            <a href="inscription.php">Inscription</a>
            <a href="resultats.php">Résultats</a>
            <a href="requetes.php" class="active">XQuery</a>
        </div>
    </nav>

    <main class="main fade-in">

        <div class="page-header">
            <div class="page-header-left">
                <div class="page-tag">Console</div>
                <h1>Simulateur d'interrogation XML</h1>
            </div>
        </div>

        <div class="query-container">
            
            <div class="card">
                <div class="card-header">
                    <div class="card-header-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75L22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3l-4.5 16.5" />
                        </svg>
                    </div>
                    <span class="card-header-title">Éditeur XPath / XQuery</span>
                </div>
                <div class="card-body">
                    <form method="POST" action="requetes.php" class="form-section">
                        <div class="form-field">
                            <label class="form-label">Saisir un mot-clé de collection</label>
                            <textarea class="form-input" name="requete" rows="4" style="font-size:0.9rem; padding:16px; resize:none;" placeholder="Saisissez : categories, membres, concours ou scores" required><?php echo htmlspecialchars($requeteSaisie); ?></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            Exécuter la requête
                        </button>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="card-header-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z" />
                        </svg>
                    </div>
                    <span class="card-header-title">Mots-clés acceptés</span>
                </div>
                <div class="card-body" style="padding:0;">
                    <div style="display:flex; flex-direction:column;">
                        <?php foreach($queries as $cmd => $meta): ?>
                        <div class="keyword-item" onclick="document.querySelector('textarea').value='<?php echo $cmd; ?>'">
                            <span style="font-size:0.875rem; font-weight:600; color:var(--text);"><?php echo $cmd; ?></span>
                            <span style="font-size:0.75rem; color:var(--text-light);"><?php echo $meta['label']; ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <?php if(!empty($error)): ?>
            <div class="alert alert-error" style="margin-top:24px;">
                <span><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>

        <?php if(!empty($resultat) && $queryMeta): ?>
        <div class="result-box" style="margin-top:24px;">
            <div class="result-box-header">
                <div style="width:6px; height:6px; background:var(--primary); border-radius:50%; margin-right:10px;"></div>
                <span class="result-label" style="font-size:0.875rem; font-weight:600; color:var(--text);"><?php echo $queryMeta['label']; ?></span>
                <span style="margin-left:auto; font-size:0.75rem; color:var(--text-soft);"><?php echo count($resultat); ?> résultat(s)</span>
            </div>
            <div class="result-box-body">
                <ul class="result-list">
                    <?php foreach($resultat as $ligne): ?>
                        <li><?php echo $ligne; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <?php endif; ?>

    </main>

    <footer>
        <span>Université de Skikda — Département Informatique — L3</span>
        <span>TP N°1 XML, XSD & XQuery</span>
    </footer>
</div>
</body>
</html>
