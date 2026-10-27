<?php

abstract class NewTraitement
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
