<?php

/**
 * Classe mère de toutes les entités.
 *
 * hydrater() remplit l'objet à partir d'une ligne de la base :
 * pour chaque colonne "nom_colonne", on appelle le setter "setNomColonne()" s'il existe.
 * Exemple : ['id_offre' => 3, 'titre' => 'Stage'] -> setIdOffre(3), setTitre('Stage')
 */
abstract class Entite
{
    public function __construct(array $donnees = [])
    {
        $this->hydrater($donnees);
    }

    public function hydrater(array $donnees): void
    {
        foreach ($donnees as $colonne => $valeur) {
            $setter = 'set' . str_replace('_', '', ucwords($colonne, '_'));
            if (method_exists($this, $setter)) {
                $this->$setter($valeur);
            }
        }
    }
}
