<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * Présentation : service générique chargé d'enregistrer les images téléversées.
 *
 * Les formulaires conservent leurs propres règles de validation. Ce service
 * centralise uniquement la création d'un nom sûr, unique et le déplacement du
 * fichier vers le dossier choisi par le contrôleur.
 */
final class ImageUploader
{
    // -----------------------
    // ATTRIBUTS
    // -----------------------

    private SluggerInterface $slugger;

    // -----------------------
    // METHODES
    // -----------------------

    /**
     * Rôle : Reçoit automatiquement le service Symfony qui sécurise les noms de fichiers.
     * Paramètre : `$slugger` (SluggerInterface) : le service qui transforme le nom d'origine en nom sûr.
     * Retour : Aucun retour ; la dépendance est conservée dans l'attribut `$slugger`.
     */
    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
    }

    /**
     * Rôle : Enregistre une image avec un nom sûr et unique dans le dossier demandé.
     * Paramètre : `$imageFile` (UploadedFile) : le fichier reçu ; `$destinationDirectory` (string) : le dossier de destination.
     * Retour : Le nom du fichier enregistré, à conserver dans l'entité concernée.
     */
    public function upload(
        UploadedFile $imageFile,
        string $destinationDirectory
    ): string {
        // Le nom envoyé par le navigateur est nettoyé afin d'éviter les
        // caractères problématiques dans le système de fichiers.
        $originalFilename = pathinfo(
            $imageFile->getClientOriginalName(),
            PATHINFO_FILENAME
        );

        $safeFilename = $this->slugger
            ->slug($originalFilename)
            ->lower();

        // Symfony détermine l'extension à partir du contenu réel du fichier.
        $extension = $imageFile->guessExtension();

        if ($extension === null) {
            throw new FileException(
                'Impossible de déterminer l’extension de l’image.'
            );
        }

        // L'identifiant unique empêche qu'une nouvelle image écrase un fichier
        // existant qui porterait le même nom d'origine.
        $filename = sprintf(
            '%s-%s.%s',
            $safeFilename,
            uniqid(),
            $extension
        );

        $imageFile->move(
            $destinationDirectory,
            $filename
        );

        return $filename;
    }
}
