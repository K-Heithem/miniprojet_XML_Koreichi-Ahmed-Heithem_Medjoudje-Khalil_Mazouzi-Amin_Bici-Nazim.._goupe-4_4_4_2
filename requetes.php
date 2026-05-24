<?php
$requeteSaisie = '';
$resultat = '';
$error = '';

$examples = [
    'Insert membre' => <<<'XQ'
insert node
  <membre id="M005" categorieRef="C2">
    <nom>Amrani</nom>
    <prenom>Sami</prenom>
    <email>s.amrani@club.dz</email>
  </membre>
into doc("club.xml")//membres
XQ,
    'Modifier email' => <<<'XQ'
replace value of node doc("club.xml")//membre[@id="M003"]/email
with "new.email@club.dz"
XQ,
    'Modifier nom' => <<<'XQ'
replace value of node doc("club.xml")//membre[@id="M003"]/nom
with "Benali"
XQ,
    'Delete membre' => <<<'XQ'
delete node doc("club.xml")//membre[@id="M005"]
XQ,
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['requete'])) {
    $requeteSaisie = trim($_POST['requete']);

    if ($requeteSaisie === '') {
        $error = "Veuillez saisir une requête XQuery.";
    } else {
        $projectRoot = realpath(__DIR__ . "/..");
        $queryFile = tempnam($projectRoot, 'xquery_');
        file_put_contents($queryFile, $requeteSaisie);

        $basex = 'basex';
        $basexPath = 'C:\\Program Files (x86)\\BaseX\\bin\\basex.bat';
        if (is_file($basexPath)) {
            $basex = $basexPath;
        }

        $command = '"' . $basex . '" -u "' . $queryFile . '"';
        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = proc_open($command, $descriptors, $pipes, $projectRoot);

        if (is_resource($process)) {
            fclose($pipes[0]);
            $output = stream_get_contents($pipes[1]);
            $stderr = stream_get_contents($pipes[2]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            $exitCode = proc_close($process);

            if ($exitCode === 0) {
                $resultat = trim($output);
                if ($resultat === '') {
                    $resultat = "Requête exécutée avec succès. Aucun résultat affiché.";
                }
            } else {
                $error = trim($stderr);
                if ($error === '') {
                    $error = "Erreur pendant l'exécution de la requête.";
                }
            }
        } else {
            $error = "Impossible de lancer BaseX.";
        }

        if (is_file($queryFile)) {
            unlink($queryFile);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Club Info_Tech — XQuery</title>
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
                <h1>Exécuter une requête XQuery</h1>
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
                    <span class="card-header-title">Éditeur XQuery</span>
                </div>
                <div class="card-body">
                    <form method="POST" action="requetes.php" class="form-section">
                        <div class="form-field">
                            <label class="form-label">Requête XQuery</label>
                            <textarea class="form-input" name="requete" rows="13" style="font-size:0.9rem; padding:16px; resize:vertical; font-family:Consolas, monospace;" placeholder="Exemple : doc(&quot;club.xml&quot;)//membre/nom/text()" required><?php echo htmlspecialchars($requeteSaisie); ?></textarea>
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
                    <span class="card-header-title">Exemples</span>
                </div>
                <div class="card-body" style="padding:16px;">
                    <div style="display:flex; flex-direction:column; gap:12px;">
                        <?php foreach($examples as $label => $query): ?>
                        <button type="button" class="keyword-item" style="border:1px solid var(--border); border-radius:var(--radius-sm); text-align:left; background:var(--white); padding:12px;" onclick="document.querySelector('textarea').value=<?php echo json_encode($query); ?>">
                            <span style="font-size:0.875rem; font-weight:600; color:var(--text); margin-bottom:8px; display:block;"><?php echo htmlspecialchars($label); ?></span>
                            <pre style="margin:0; white-space:pre-wrap; font-family:Consolas, monospace; font-size:0.75rem; color:var(--text-light); line-height:1.45;"><?php echo htmlspecialchars($query); ?></pre>
                        </button>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <?php if(!empty($error)): ?>
            <div class="alert alert-error" style="margin-top:24px;">
                <span><?php echo nl2br(htmlspecialchars($error)); ?></span>
            </div>
        <?php endif; ?>

        <?php if(!empty($resultat)): ?>
        <div class="result-box" style="margin-top:24px;">
            <div class="result-box-header">
                <div style="width:6px; height:6px; background:var(--primary); border-radius:50%; margin-right:10px;"></div>
                <span class="result-label" style="font-size:0.875rem; font-weight:600; color:var(--text);">Résultat</span>
            </div>
            <div class="result-box-body">
                <pre style="margin:0; white-space:pre-wrap; font-family:Consolas, monospace; font-size:0.875rem;"><?php echo htmlspecialchars($resultat); ?></pre>
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
