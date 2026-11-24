# Architecture POO du site HSP

Les pages de `public/` ne contiennent presque plus de SQL : elles utilisent des classes rangées dans `src/`.

```
src/
├── modele/        Database (connexion PDO unique), Session (connexion, droits, CSRF), Mail, InfosSite (mentions légales)
├── traitement/    une classe par objet métier : NewOffre, NewEvenement, NewPost, NewMedecin...
│                  (attributs privés, getters/setters, et validation des formulaires)
│                  NewUtilisateur est la classe mère de NewEtudiant, NewMedecin, NewPartenaire, NewGestionnaire (héritage)
│                  NewTraitement : classe mère de tout (hydratation)
└── repository/    une classe par table, qui contient toutes les requêtes SQL : OffreRepository, EvenementRepository...
                   Repository : classe mère (connexion PDO + transformation des lignes en objets)
```

## Le principe

1. La **page** (`public/pages/...`) reçoit la demande et appelle un **repository**.
2. Le **repository** exécute la requête SQL préparée et renvoie des **objets** `New...`.
3. L'objet porte les règles du métier (par exemple `NewEvenement::estPasse()`, `NewOffre`...) et la page l'affiche.

## Utilisation dans une page

```php
require __DIR__ . '/../../includes/config.php';   // autoload des classes + session + contrôle CSRF

Session::exigerConnexion('../../');                // contrôle d'accès

$offres = (new OffreRepository())->listerOuvertes();   // tableau d'objets NewOffre
foreach ($offres as $offre) {
    echo htmlspecialchars($offre->getTitre());
}
```

## À savoir

- `config.php` charge automatiquement les classes (`spl_autoload_register`) : pas besoin de `require` pour chaque classe. Le **nom du fichier doit être le nom de la classe** (`NewEvenement.php` contient `class NewEvenement`).
- Une ligne SQL devient un objet grâce à `NewTraitement::hydrater()` : la colonne `date_publication` appelle `setDatePublication()`.
- Les formulaires envoyés en POST doivent contenir `<?= Session::champCsrf() ?>` : `config.php` vérifie le jeton avant tout le reste.
- Session : `Session::getIdUtilisateur()`, `Session::getRole()`, `Session::estConnecte()`.
- Droits d'accès : `Session::exigerConnexion($racine)`, `Session::exigerMembre($racine)` (tout le monde sauf le gestionnaire), `Session::exigerRole(['partenaire'], $racine)`.
- Messages entre deux pages : `Session::flash('...')` puis `Session::consommerFlash()` sur la page d'arrivée.
- Fuseau horaire : fixé à `Europe/Paris` dans `config.php`.