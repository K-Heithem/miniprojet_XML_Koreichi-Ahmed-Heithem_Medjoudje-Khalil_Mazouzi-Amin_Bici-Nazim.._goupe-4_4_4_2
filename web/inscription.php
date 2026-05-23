<?php
$xml     = simplexml_load_file("club.xml");
$message = "";
$error   = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['membreRef'])) {
    $membreRef      = $_POST['membreRef'];
    $concoursId     = $_POST['concoursId'];
    $complexite     = trim($_POST['complexite'] ?? '0');
    $tempsExecution = trim($_POST['tempsExecution'] ?? '0');

    if (intval($complexite) < 0 || intval($complexite) > 100) {
        $error = "La complexité doit être comprise entre 0 et 100.";
    } elseif (intval($tempsExecution) <= 0) {
        $error = "Le temps d'exécution doit être strictement supérieur à 0.";
    } else {
        $targetConcours = $xml->xpath("//concours[@id='$concoursId']/participants");
        
        if (isset($targetConcours[0])) {
            $newParticipant = $targetConcours[0]->addChild('participant');
            $newParticipant->addAttribute('membreRef', $membreRef);
            $newParticipant->addChild('complexite', htmlspecialchars($complexite));
            $newParticipant->addChild('tempsExecution', htmlspecialchars($tempsExecution));
            
            $xml->asXML("club.xml");
            
            $membreInfos = $xml->xpath("//membre[@id='$membreRef']");
            $nomMembre = isset($membreInfos[0]) ? $membreInfos[0]->nom . " " . $membreInfos[0]->prenom : $membreRef;
            
            $message = "Inscription de <strong>" . htmlspecialchars($nomMembre) . "</strong> enregistrée avec succès !";
        } else {
            $error = "Une erreur est survenue : concours introuvable.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Club Info_Tech — Inscription</title>
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
            <a href="inscription.php" class="active">Inscription</a>
            <a href="resultats.php">Résultats</a>
            <a href="requetes.php">XQuery</a>
        </div>
    </nav>

    <main class="main fade-in" style="display: flex; flex-direction: column; justify-content: center; align-items: center;">

        <div class="page-header" style="text-align: center; width: 100%; justify-content: center; margin-bottom: 40px;">
            <div style="display: flex; flex-direction: column; align-items: center;">
                <div class="page-tag">Formulaire</div>
                <h1 style="text-align: center;">S'inscrire à un concours</h1>
            </div>
        </div>

        <div class="card" style="width: 100%; max-width: 550px; margin: 0 auto 32px auto;">
            <div class="card-header">
                <div class="card-header-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                    </svg>
                </div>
                <span class="card-header-title">Nouveau dossier d'inscription</span>
            </div>
            <div class="card-body">

                <?php if (!empty($message)): ?>
                    <div class="alert alert-success">
                        <span><?php echo $message; ?></span>
                    </div>
                <?php endif; ?>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-error">
                        <span><?php echo htmlspecialchars($error); ?></span>
                    </div>
                <?php endif; ?>

                <form method="POST" class="form-section">

                    <div class="form-field">
                        <label class="form-label">Membre du Club</label>
                        <select class="form-select" name="membreRef" required>
                            <option value="">— Choisir un membre —</option>
                            <?php foreach($xml->membres->membre as $m): ?>
                                <option value="<?php echo $m['id']; ?>">
                                    <?php echo htmlspecialchars($m->nom . " " . $m->prenom); ?> (<?php echo $m['id']; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-field">
                        <label class="form-label">Concours ciblé</label>
                        <select class="form-select" name="concoursId" required>
                            <option value="">— Choisir un concours —</option>
                            <?php foreach($xml->listesConcours->concours as $c): 
                                $catRef = (string)$c['categorieRef'];
                                $categorieNode = $xml->xpath("//categorie[@id='$catRef']");
                                $libelleCat = isset($categorieNode[0]) ? (string)$categorieNode[0]->libelle : "Inconnue";
                            ?>
                                <option value="<?php echo $c['id']; ?>">
                                    <?php echo htmlspecialchars($c->titre); ?> — [<?php echo htmlspecialchars($libelleCat); ?>]
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-field">
                        <label class="form-label">Score de Complexité (0 à 100)</label>
                        <input class="form-input" type="number" name="complexite" min="0" max="100" placeholder="Ex: 80" required>
                    </div>

                    <div class="form-field">
                        <label class="form-label">Temps d'exécution (ms)</label>
                        <input class="form-input" type="number" name="tempsExecution" min="1" placeholder="Ex: 25" required>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 10px;">
                        Confirmer l'inscription
                    </button>

                </form>
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
