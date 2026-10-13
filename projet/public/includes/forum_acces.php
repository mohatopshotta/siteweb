<?php

function forum_droits(int $id_section, ?string $role): array
{
    $lire = false;
    $creer = false;

    if ($id_section === 1) {
        $lire = true;
        $creer = $role !== null;
    } elseif ($id_section === 2) {
        $lire = in_array($role, ['medecin', 'partenaire', 'gestionnaire'], true);
        $creer = in_array($role, ['medecin', 'partenaire'], true);
    } elseif ($id_section === 3) {
        $lire = in_array($role, ['etudiant', 'medecin', 'gestionnaire'], true);
        $creer = $role === 'etudiant';
    }

    return ['lire' => $lire, 'creer' => $creer];
}

function forum_date(string $date): string
{
    return date('d/m/Y H:i', strtotime($date));
}
