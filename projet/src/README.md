# Architecture POO du site HSP

Les pages de `public/` ne contiennent plus de SQL : elles utilisent des classes rangées dans `src/`.

```
src/
├── Core/       Database (connexion PDO unique), Session (connexion / droits d'accès), Mail
├── Entite/     une classe par table : Offre, Evenement, Post... (attributs privés + getters/setters)
│               Utilisateur est abstraite -> Etudiant, Medecin, Partenaire, Gestionnaire (héritage)
└── Manager/    une classe par table qui contient toutes les requêtes SQL : OffreManager, EvenementManager...
```

## Utilisation dans une page

```php
require __DIR__ . '/../../includes/config.php';   // autoload + session

Session::exigerMembre('../../');                    // contrôle d'accès

$offres = (new OffreManager())->listerOuvertes();   // tableau d'objets NewOffre
foreach ($offres as $offre) {
    echo htmlspecialchars($offre->getTitre());
}
```

- `config.php` charge automatiquement les classes (`spl_autoload_register`) : pas besoin de `require` pour chaque classe.
- Une ligne SQL devient un objet grâce à `Entite::hydrater()` : la colonne `date_publication` appelle `setDatePublication()`.
- Session : une seule convention `$_SESSION['id_utilisateur']` et `$_SESSION['role']`, accessible avec `Session::getIdUtilisateur()` et `Session::getRole()`.
- Droits : `Session::exigerConnexion()`, `Session::exigerMembre()` (tout le monde sauf le gestionnaire), `Session::exigerRole(['partenaire'], $racine)`.
