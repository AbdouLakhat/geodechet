<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class SignalementNotifier
{
    /** Envoie au citoyen un e-mail lorsque son signalement passe en cours ou est traité. */
    public function notifierChangementStatut(int $signalementId, ?string $ancienStatut, string $nouveauStatut): void
    {
        // Ignore les statuts inchangés et ceux qui ne nécessitent pas d’e-mail.
        if ($ancienStatut === $nouveauStatut || ! in_array($nouveauStatut, ['en cours', 'traité'], true)) {
            return;
        }

        $signalement = DB::table('signalements as s')
            ->leftJoin('unites as u', 'u.id', '=', 's.unite_id')
            ->where('s.id', $signalementId)
            ->first(['s.id', 's.type_dechet', 's.prenom', 's.email', 'u.nom as unite']);

        if (! $signalement || ! $signalement->email) {
            return;
        }

        $types = [
            'ordures_menageres' => 'Ordures ménagères',
            'encombrants' => 'Encombrants',
            'gravats' => 'Gravats',
            'dechets_verts' => 'Déchets verts',
            'autre' => 'Autre',
        ];

        if ($nouveauStatut === 'en cours') {
            $titre = 'Votre signalement est pris en charge';
            $texte = 'Bonne nouvelle : votre signalement est pris en charge. Une équipe va se rendre sur place. Vous recevrez un dernier e-mail quand le dépôt sera traité.';
            $couleur = '#1565c0';
            $statut = 'En cours';
            $sujet = 'GéoDéchet : signalement n° '.$signalement->id.' pris en charge';
        } else {
            $titre = 'Votre signalement a été traité';
            $texte = 'Le dépôt que vous avez signalé a été traité. Merci de votre aide pour garder la commune propre.';
            $couleur = '#2e7d32';
            $statut = 'Traité';
            $sujet = 'GéoDéchet : signalement n° '.$signalement->id.' traité';
        }

        $infos = [
            'titre' => $titre,
            'texte' => $texte,
            'statut' => $statut,
            'couleur' => $couleur,
            'prenom' => $signalement->prenom,
            'numero' => $signalement->id,
            'type' => $types[$signalement->type_dechet] ?? $signalement->type_dechet,
            'unite' => $signalement->unite ?? 'Hors des Unités',
        ];

        // Une panne de messagerie ne doit pas annuler le changement de statut en base.
        rescue(function () use ($infos, $signalement, $sujet): void {
            Mail::send('emails.citoyen', $infos, function ($message) use ($signalement, $sujet): void {
                $message->to($signalement->email)->subject($sujet);
            });
        });
    }
}
