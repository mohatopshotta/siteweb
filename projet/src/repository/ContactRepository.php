<?php

class ContactRepository extends Repository
{
    public function creer(?int $idUtilisateur, string $nom, string $email, string $sujet, string $message): void
    {
        $req = $this->pdo->prepare(
            'INSERT INTO message_contact (id_utilisateur, nom, email, sujet, message) VALUES (?, ?, ?, ?, ?)'
        );
        $req->execute([$idUtilisateur, $nom, $email, $sujet, $message]);
    }

    public function lister(): array
    {
        return $this->pdo->query('SELECT * FROM message_contact ORDER BY date_message DESC, id_message DESC')->fetchAll();
    }

    public function supprimer(int $idMessage): void
    {
        $req = $this->pdo->prepare('DELETE FROM message_contact WHERE id_message = ?');
        $req->execute([$idMessage]);
    }
}
