(: Q1 :)
let $doc := doc("club.xml")
return
<membres>
  {
    for $m in $doc//membre
    let $cat := $doc//categorie[@id = $m/@categorieRef]
    return
      <membre id="{$m/@id}">
        <nomComplet>{concat($m/prenom, ' ', $m/nom)}</nomComplet>
        <email>{$m/email/text()}</email>
        <categorie>{$cat/libelle/text()}</categorie>
      </membre>
  }
</membres>

(: Q2 :)
let $doc := doc("club.xml")
return
<concoursList>
  {
    for $c in $doc//concours
    let $cat := $doc//categorie[@id = $c/@categorieRef]
    order by xs:date($c/date) ascending
    return
      <concours id="{$c/@id}">
        <titre>{$c/titre/text()}</titre>
        <date>{$c/date/text()}</date>
        <coefficient>{string($c/@coefficient)}</coefficient>
        <categorie>{$cat/libelle/text()}</categorie>
      </concours>
  }
</concoursList>

(: Q3 :)
let $doc := doc("club.xml")
return
<scores>
  {
    for $c in $doc//concours
    return
      <concours titre="{$c/titre/text()}">
        {
          for $p in $c/participants/participant
          let $m := $doc//membre[@id = $p/@membreRef]
          let $comp := xs:integer($p/complexite)
          let $time := xs:integer($p/tempsExecution)
          let $coef := xs:decimal($c/@coefficient)
          let $score := round((($comp + $time) * $coef) * 100) div 100
          return
            <participant>
              <nomComplet>{concat($m/prenom, ' ', $m/nom)}</nomComplet>
              <complexite>{$comp}</complexite>
              <tempsExecution>{$time}</tempsExecution>
              <score>{$score}</score>
            </participant>
        }
      </concours>
  }
</scores>

(: Q4 :)
let $doc := doc("club.xml")
return
<vainqueurs>
  {
    for $c in $doc//concours
    let $coef := xs:decimal($c/@coefficient)
    let $scores :=
      for $p in $c/participants/participant
      let $m := $doc//membre[@id = $p/@membreRef]
      let $score := (xs:integer($p/complexite) + xs:integer($p/tempsExecution)) * $coef
      return
        <part_score>
          <nom>{$m/nom/text()}</nom>
          <prenom>{$m/prenom/text()}</prenom>
          <score>{$score}</score>
        </part_score>
    let $max_score := max($scores/score)
    return
      <concours id="{$c/@id}" titre="{$c/titre/text()}">
        {
          for $s in $scores
          where $s/score = $max_score
          return
            <vainqueur>
              <nom>{$s/nom/text()}</nom>
              <prenom>{$s/prenom/text()}</prenom>
              <score>{round($s/score * 100) div 100}</score>
            </vainqueur>
        }
      </concours>
  }
</vainqueurs>

(: Q5 :)
declare variable $categorie as xs:string external := "Intelligence Artificielle";
let $doc := doc("club.xml")
let $cat := $doc//categorie[libelle = $categorie]
return
<membresCategorie categorie="{$categorie}">
  {
    for $m in $doc//membre[@categorieRef = $cat/@id]
    order by $m/nom ascending, $m/prenom ascending
    return
      <membre id="{$m/@id}">
        <nom>{$m/nom/text()}</nom>
        <prenom>{$m/prenom/text()}</prenom>
        <email>{$m/email/text()}</email>
      </membre>
  }
</membresCategorie>
