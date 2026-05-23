<?php
$xml = simplexml_load_file("club.xml");
$concoursChoisi = $_GET['concours'] ?? '';

$participants_scores = [];
$maxScore = -1;
$titreConcours = "";

if (!empty($concoursChoisi)) {
    $concoursNode = $xml->xpath("//concours[@id='$concoursChoisi']");
    if (isset($concoursNode[0])) {
        $titreConcours = (string)$concoursNode[0]->titre;
        $coef = floatval($concoursNode[0]['coefficient']);
        
        if (isset($concoursNode[0]->participants->participant)) {
            foreach ($concoursNode[0]->participants->participant as $p) {
                $mRef = (string)$p['membreRef'];
                $membreNode = $xml->xpath("//membre[@id='$mRef']");
                $nomComplet = isset($membreNode[0]) ? (string)$membreNode[0]->nom . " " . (string)$membreNode[0]->prenom : "Membre Inconnu";
                
                $comp = intval($p->complexite);
                $temps = intval($p->tempsExecution);
                $score = ($comp + $temps) * $coef;
                
                $participants_scores[] = [
                    'nom' => $nomComplet,
                    'score' => $score,
                    'complexite' => $comp,
                    'temps' => $temps
                ];
                
                if ($score > $maxScore) {
                    $maxScore = $score;
                }
            }
        }
        usort($participants_scores, fn($a, $b) => $b['score'] <=> $a['score']);
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Club Info_Tech — Résultats</title>
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
            <a href="resultats.php" class="active">Résultats</a>
            <a href="requetes.php">XQuery</a>
        </div>
    </nav>

    <main class="main fade-in">

        <div class="page-header">
            <div class="page-header-left">
                <div class="page-tag">Classement</div>
                <h1>Résultats des concours</h1>
            </div>
        </div>

        <div class="card" style="margin-bottom:24px;">
            <div class="card-header">
                <div class="card-header-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                    </svg>
                </div>
                <span class="card-header-title">Sélectionner un événement</span>
            </div>
            <div class="card-body">
                <form method="GET" action="resultats.php" class="form-section">
                    <div class="form-field">
                        <select class="form-select" name="concours" onchange="this.form.submit()">
                            <option value="">— Choisir un concours —</option>
                            <?php foreach($xml->listesConcours->concours as $c): ?>
                                <option value="<?php echo $c['id']; ?>" <?php echo $concoursChoisi == $c['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($c->titre); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>
            </div>
        </div>

        <?php if (!empty($concoursChoisi)): ?>
            
            <?php if (empty($participants_scores)): ?>
                <div class="card">
                    <div class="empty-state">
                        <p class="empty-state-text">Aucun participant n'est encore inscrit à ce concours.</p>
                    </div>
                </div>
            <?php else: ?>

                <?php $vainqueur = $participants_scores[0]; ?>
                
                <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap:24px; margin-bottom:24px;">
                    <div class="stat-card" style="border-left: 3px solid var(--warning);">
                        <span class="stat-label">Premier rang (Vainqueur)</span>
                        <div class="stat-value" style="color:var(--warning);"><?php echo $vainqueur['score']; ?> <span>pts</span></div>
                        <p style="margin-top:4px; font-size:0.875rem; color:var(--text); font-weight:600;"><?php echo htmlspecialchars($vainqueur['nom']); ?></p>
                    </div>
                    <div class="stat-card">
                        <span class="stat-label">Total des candidatures</span>
                        <div class="stat-value"><?php echo count($participants_scores); ?> <span>visibles</span></div>
                        <p style="margin-top:4px; font-size:0.875rem; color:var(--text-light);"><?php echo htmlspecialchars($titreConcours); ?></p>
                    </div>
                </div>

                <div class="card">
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Position</th>
                                    <th>Nom & Prénom</th>
                                    <th>Complexité</th>
                                    <th>Temps</th>
                                    <th>Score Final</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php 
                            $rank = 1;
                            foreach($participants_scores as $p): 
                                $isVainqueur = ($p['score'] === $maxScore && $maxScore > 0);
                            ?>
                                <tr style="<?php echo $isVainqueur ? 'background: var(--warning-bg);' : ''; ?>">
                                    <td><span class="td-id"><?php echo str_pad($rank++, 2, '0', STR_PAD_LEFT); ?></span></td>
                                    <td>
                                        <span class="td-main"><?php echo htmlspecialchars($p['nom']); ?></span>
                                        <?php if($isVainqueur): ?>
                                            <span class="badge badge-warning" style="margin-left:8px;">Premier</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><span><?php echo $p['complexite']; ?>/100</span></td>
                                    <td><span><?php echo $p['temps']; ?> ms</span></td>
                                    <td><strong style="color: <?php echo $isVainqueur ? 'var(--warning)' : 'var(--text)'; ?>;"><?php echo $p['score']; ?> pts</strong></td>
                                    <td>
                                        <?php if($p['score'] >= 300): ?>
                                            <span class="badge badge-success">Elite</span>
                                        <?php elseif($p['score'] >= 150): ?>
                                            <span class="badge badge-primary">Excellent</span>
                                        <?php else: ?>
                                            <span class="badge badge-gray">Admis</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            <?php endif; ?>

        <?php else: ?>
            <div class="card">
                <div class="empty-state">
                    <p class="empty-state-text">Sélectionnez un concours pour générer le classement officiel.</p>
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
