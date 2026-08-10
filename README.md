# Bolonews

Bolonews est une application web d’actualité développée avec Symfony dans le cadre de la formation **Développeur Web Full Stack — Niveau 5 (Bac+2)**.

Le projet met en pratique la conception d’une application selon l’architecture MVC, la persistance des données avec Doctrine, la création de formulaires et leur validation, l’authentification, la gestion des autorisations ainsi que des interactions JavaScript et AJAX.

## Fonctionnalités

### Partie publique

- consultation de la page d’accueil avec articles mis en avant et derniers articles publiés ;
- consultation de la liste et du détail des articles ;
- recherche par mots clés et filtrage des articles par catégorie ;
- consultation des commentaires et du nombre de mentions « J’aime » ;
- affichage d’une page de contact ;
- création d’un compte et connexion.

### Espace utilisateur

- modification du profil, du pseudonyme et de l’avatar ;
- création et modification d’un article ;
- enregistrement d’un article comme brouillon ;
- publication et dépublication de ses articles ;
- ajout d’un commentaire sur un article publié ;
- ajout ou retrait d’une mention « J’aime » en AJAX.

### Espace administrateur

- consultation et modération des articles ;
- publication, dépublication et suppression d’un article ;
- recherche et gestion des comptes utilisateurs ;
- bannissement, réactivation ou suppression d’un compte selon les droits ;
- création, modification et suppression des catégories.

## Technologies utilisées

- **PHP 8.2 ou supérieur** ;
- **Symfony 7.4** ;
- **Doctrine ORM** et Doctrine Migrations ;
- **MySQL 8.4** ;
- **Twig** ;
- **Symfony Form**, Validator et Security ;
- **Bootstrap 5.3.8** (expérimentation) ;
- **SCSS** avec SymfonyCasts SassBundle (expérimentation) ;
- **JavaScript**, AJAX, Stimulus (expérimentation) et Symfony UX Turbo(expérimentation) ;
- **AssetMapper**, sans npm !(expérimentation)

## Architecture et modèle de données

L’application respecte l’architecture MVC de Symfony :

- les entités et les repositories représentent les données et leur accès ;
- les contrôleurs reçoivent les requêtes, appliquent les règles métier et préparent les réponses ;
- les templates Twig génèrent les pages HTML ;
- les FormType centralisent la structure et le traitement des formulaires ;
- les contraintes Validator sécurisent les données côté serveur.

Le modèle de données repose sur cinq entités principales :

- `User` : compte, authentification, rôles, profil et bannissement ;
- `Category` : classement des articles ;
- `Article` : contenu, auteur, catégorie, image et état de publication ;
- `Comment` : commentaire associé à un article et à son auteur ;
- `ArticleLike` : association unique entre un utilisateur et un article aimé.

## Sécurité

La sécurité repose sur le composant Symfony Security :

- authentification par adresse électronique et mot de passe haché ;
- protection CSRF des formulaires et des actions sensibles ;
- distinction entre `ROLE_USER` et `ROLE_ADMIN` ;
- contrôle de l’auteur avant la modification d’un article ;
- protection des brouillons contre les accès non autorisés ;
- blocage de la connexion d’un compte banni ;
- utilisation de requêtes POST pour les actions qui modifient les données.

## Prérequis

Avant l’installation, vérifier la présence de :

- PHP 8.2 ou supérieur avec les extensions nécessaires à Symfony et MySQL ;
- Composer ;
- MySQL 8 ;
- Symfony CLI, recommandé pour lancer le serveur local ;
- Git.

## Installation locale

### 1. Récupérer le projet

```bash
git clone https://github.com/anthonylepichon/bolonews.git
cd bolonews
```

### 2. Installer les dépendances PHP

```bash
composer install
```

Composer installe Symfony, Doctrine, Bootstrap et les autres dépendances déclarées dans `composer.json`. Les scripts Symfony installent également les ressources gérées par AssetMapper.

### 3. Configurer la base de données

Créer un fichier `.env.local` à la racine du projet, puis adapter la connexion :

```dotenv
DATABASE_URL="mysql://UTILISATEUR:MOT_DE_PASSE@127.0.0.1:3306/bolonews?serverVersion=8.4.3&charset=utf8mb4"
```

Le fichier `.env.local` est ignoré par Git. Les identifiants réels ne doivent jamais être enregistrés dans `.env` ni publiés sur GitHub.

### 4. Créer la base et les tables

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

La première commande crée la base vide. La seconde applique les migrations versionnées afin de créer les tables et leurs relations.

### 5. Compiler le SCSS

Pour une compilation ponctuelle :

```bash
php bin/console sass:build
```

Pendant le développement, la surveillance automatique peut être lancée dans un terminal séparé :

```bash
php bin/console sass:build --watch
```

Bootstrap fournit la grille, les composants et les classes utilitaires. Le SCSS du projet adapte ensuite ces éléments au Design System de Bolonews.

### 6. Lancer l’application

```bash
symfony server:start
```

Le terminal indique l’adresse locale à ouvrir dans le navigateur, généralement `https://127.0.0.1:8000`.

Pour arrêter le serveur :

```bash
symfony server:stop
```

## Vérifications utiles

```bash
php bin/console lint:twig templates
php bin/console lint:yaml config
php bin/console lint:container
php bin/console doctrine:schema:validate
```

Ces commandes vérifient respectivement les templates Twig, la configuration YAML, les services Symfony et la cohérence entre les entités Doctrine et la base de données.

## Organisation des principaux dossiers

```text
assets/         JavaScript et styles SCSS
config/         configuration de Symfony et de ses composants
migrations/     historique versionné du schéma de base de données
public/         point d’entrée public, images et ressources compilées
src/Controller/ contrôleurs et routes de l’application
src/Entity/     entités Doctrine
src/Form/       formulaires Symfony
src/Repository/ requêtes d’accès aux données
templates/      vues Twig
tests/          tests automatisés
```

Les supports de cours et certains documents de travail sont conservés localement et volontairement exclus du dépôt Git.

## Conception UI/UX

La conception a été réalisée avant le codage : wireframes, Design System, interfaces définitives, schéma ergonomique, spécifications, MCD et MPD.

La maquette de référence est disponible dans [Figma — Bolonews](https://www.figma.com/design/qO0lol9iPLirftvy9nmrRD/Bolonews?node-id=3-4&t=qvOLK2Qz2vJvxlE3-1).

## Démarche de réalisation

Le développement a suivi cet ordre général :

1. analyse du cahier des charges ;
2. conception UI/UX et définition des parcours ;
3. spécifications des contrôleurs, templates, comportements JavaScript et échanges AJAX ;
4. réalisation du MCD et du MPD ;
5. configuration de MySQL et de Doctrine ;
6. création des entités, relations et migrations ;
7. création des contrôleurs, routes et templates Twig ;
8. création des FormType et des règles de validation ;
9. mise en place de l’authentification et des autorisations ;
10. intégration de Bootstrap et personnalisation SCSS ;
11. ajout des interactions JavaScript et AJAX ;
12. vérifications techniques et corrections de cohérence.

## Statut du projet

Bolonews est un projet pédagogique. La page de contact est actuellement une vue de démonstration sans traitement métier. Les tests fonctionnels automatisés pourront être complétés dans une prochaine étape.

## Auteur

**Anthony LE PICHON**
Projet Symfony créé dans le cadre d’une formation Développeur Web Full Stack — Niveau 5 (Bac+2).
