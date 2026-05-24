(: Insertion :)
insert node <membre id="M010" categorieRef="C2">
              <nom>Amrani</nom>
              <prenom>Sami</prenom>
              <email>s.amrani@club.dz</email>
            </membre>
into doc("club.xml")//membres;

(: Modification :)
replace value of node doc("club.xml")//concours[@id="CO2"]/@coefficient
with "2.0";

(: Suppression :)
delete node doc("club.xml")//concours[@id="CO1"]//participant[@membreRef="M002"];
