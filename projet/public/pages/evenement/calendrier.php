<?php
require __DIR__ . '/../../includes/config.php';

Session::exigerConnexion('../../');

$repo = new EvenementRepository();
$evenement = $repo->trouverParId((int)($_GET['id'] ?? 0));

if ($evenement === null || $evenement->getDateEvenement() === null) {
    http_response_code(404);
    die('Événement introuvable ou sans date.');
}

// Pas d'heure de fin en base : on compte 2 h par défaut
$debut = new DateTime($evenement->getDateEvenement(), new DateTimeZone('Europe/Paris'));
$fin   = (clone $debut)->modify('+2 hours');

// Un fichier .ics veut les heures en UTC
$debut->setTimezone(new DateTimeZone('UTC'));
$fin->setTimezone(new DateTimeZone('UTC'));

// Échappe les caractères spéciaux du format iCalendar
function icsTexte(string $t): string
{
    $t = str_replace(["\\", ";", ",", "\r\n", "\n", "\r"], ["\\\\", "\\;", "\\,", "\\n", "\\n", "\\n"], $t);
    return $t;
}

$lignes = [
    'BEGIN:VCALENDAR',
    'VERSION:2.0',
    'PRODID:-//GDH Hopital Sud Paris//Evenements//FR',
    'CALSCALE:GREGORIAN',
    'BEGIN:VEVENT',
    'UID:evenement-' . $evenement->getIdEvenement() . '@gdh-hopital-sud-paris',
    'DTSTAMP:' . gmdate('Ymd\THis\Z'),
    'DTSTART:' . $debut->format('Ymd\THis\Z'),
    'DTEND:' . $fin->format('Ymd\THis\Z'),
    'SUMMARY:' . icsTexte($evenement->getTitre()),
    'LOCATION:' . icsTexte($evenement->getLieu()),
    'DESCRIPTION:' . icsTexte($evenement->getDescription()),
    'END:VEVENT',
    'END:VCALENDAR',
];

header('Content-Type: text/calendar; charset=utf-8');
header('Content-Disposition: attachment; filename="evenement-' . $evenement->getIdEvenement() . '.ics"');
echo implode("\r\n", $lignes) . "\r\n";