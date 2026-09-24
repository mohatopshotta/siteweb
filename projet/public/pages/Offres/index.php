<?php
session_start();
require_once '../../includes/config.php';

$type = $_GET['type'] ?? '';
$sql = "SELECT o.id_offre, o.titre, o.type_offre, o.salaire, o.date_publication,
               e.nom AS entreprise
        FROM offre o
        JOIN entreprise e ON e.id_entreprise = o.id_entreprise
        WHERE o.etat = 'ouvert'";
$params = [];

if (in_array($type, ['stage','alternance','cdd','cdi'])) {
    $sql .= " AND o.type_offre = ?";
    $params[] = $type;
}
$sql .= " ORDER BY o.date_publication DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$offres = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<h1>Offres</h1>

<form method="get">
    <select name="type" onchange="this.form.submit()">
        <option value="">Tous les types</option>
        <?php foreach (['stage','alternance','cdd','cdi'] as $t): ?>
            <option value="<?= $t ?>" <?= $type === $t ? 'selected' : '' ?>><?= strtoupper($t) ?></option>
        <?php endforeach; ?>
    </select>
</form>

<?php foreach ($offres as $o): ?>
    <div class="offre">
        <h2><a href="detail.php?id=<?= $o['id_offre'] ?>"><?= htmlspecialchars($o['titre']) ?></a></h2>
        <p><?= htmlspecialchars($o['entreprise']) ?> · <?= strtoupper($o['type_offre']) ?>
            <?= $o['salaire'] ? ' · ' . $o['salaire'] . ' €' : '' ?></p>
    </div>
<?php endforeach; ?>
