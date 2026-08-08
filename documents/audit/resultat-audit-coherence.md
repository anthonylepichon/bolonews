# Résultat de l’audit de cohérence — Bolonews

Date de l’audit : 6 août 2026

## Mise à jour après corrections — 8 août 2026

La passe d’implémentation Bootstrap/SCSS et de mise en conformité avec la page
`04 - Interface utilisateur` de Figma est terminée. Les écarts suivants relevés
dans l’audit initial ont été corrigés :

- application du Design System aux pages publiques, aux formulaires, aux espaces utilisateur et administrateur ;
- séparation des articles publiés et des brouillons dans les tableaux de bord ;
- aperçu autorisé d’un brouillon pour son auteur et l’administrateur ;
- bouton d’édition dans le détail et suppression administrative dans le formulaire d’article ;
- actions de publication et de dépublication visibles selon l’état de l’article ;
- recherche et réinitialisation des articles, avec affichage des filtres actifs ;
- correction complète du comportement AJAX du like et de ses erreurs ;
- aperçu JavaScript des avatars et de l’image principale d’un article ;
- contrôle JavaScript de la confirmation des mots de passe ;
- modales Bootstrap pour les suppressions et le bannissement ;
- ajout et modification locale des catégories depuis leur page de gestion ;
- menu Bootstrap responsive et messages flash refermables ;
- option « Se souvenir de moi » configurée ;
- validation explicite de l’adresse e-mail à l’inscription ;
- redirection vers la connexion après la création d’un compte ;
- affichage du message spécifique lorsqu’un compte est suspendu ;
- formulaire de contact conservé comme vue de démonstration sans traitement, conformément à la décision du développeur.

Contrôles réussis après cette passe : compilation Sass, syntaxe Twig, PHP, YAML
et JavaScript, validation du conteneur Symfony, routes et mapping Doctrine. Les
pages publiques ont aussi été vérifiées visuellement en desktop et en mobile,
y compris le menu Bootstrap Collapse.

Les travaux restant volontairement hors de cette passe sont les tests
fonctionnels automatisés, qui nécessitent d’abord une base `bolonews_test`
configurée et isolée. Les documents de conceptualisation doivent également
être actualisés lorsque la décision d’utiliser AJAX pour la recherche des
articles remplace définitivement l’ancienne spécification GET avec rechargement.

## Périmètre contrôlé

L’audit compare l’implémentation actuelle avec les éléments de conceptualisation du projet :

- le brief et les références de la maquette Figma ;
- les maquettes HiFi exportées dans `documents/conceptualisation/Maquette Figma/HiFi` ;
- le schéma ergonomique ;
- le MPD et les images individuelles des entités ;
- les quatre onglets du document Google Sheets de spécifications ;
- les contrôleurs, entités, formulaires, repositories, templates Twig, fichiers JavaScript et configurations Symfony.

Le cahier des charges PDF est présent dans `documents/cahier des charges`, mais son texte n’a pas pu être extrait directement dans l’environnement d’audit. Son périmètre fonctionnel a été recoupé avec le brief Figma et les spécifications.

## Conclusion générale

Le socle du projet est globalement cohérent, particulièrement la base de données et les principales fonctionnalités Symfony. L’implémentation n’est toutefois pas encore entièrement conforme aux spécifications, aux maquettes et au schéma ergonomique.

Les corrections prioritaires concernent les parcours de publication, les droits d’accès aux brouillons, certains états des templates et un comportement AJAX ajouté alors que les spécifications demandent une recherche GET classique.

## Éléments cohérents

- Les entités `User`, `Article`, `Category`, `Comment` et `ArticleLike` correspondent au MPD.
- Les relations Doctrine, les clés étrangères et les règles de suppression sont cohérentes.
- La contrainte `UNIQUE (user_id, article_id)` empêche un utilisateur d’aimer plusieurs fois le même article.
- Doctrine confirme que le schéma réel de la base de données est synchronisé avec les entités.
- Les données de démonstration contrôlées comprennent 6 utilisateurs, 8 articles, 10 commentaires et 20 likes.
- Toutes les routes attendues sont déclarées.
- La syntaxe PHP est valide.
- Les 18 templates Twig sont valides.
- Le conteneur de services Symfony est valide.
- Les fichiers `assets/js/like.js` et `assets/js/article-search.js` ont une syntaxe JavaScript valide.
- Les fonctions principales existent : inscription, connexion, profil, création et modification d’article, commentaires, likes, bannissement, gestion des catégories et suppression administrative.
- Le lien « Mot de passe oublié » est absent du code, conformément à la dernière décision du développeur.

## Écarts fonctionnels importants

### 1. Prévisualisation des brouillons

Les spécifications autorisent l’auteur et l’administrateur à consulter un brouillon. La méthode `ArticleController::show()` recherche actuellement uniquement les articles publiés.

Conséquence : même l’auteur ou l’administrateur reçoit une erreur 404 lorsqu’il tente de consulter un brouillon.

### 2. Publication et dépublication

Les spécifications prévoient une méthode `ArticleController::togglePublication()` avec une route POST dédiée. Cette méthode n’existe pas : la publication et la dépublication sont traitées dans `edit()` avec le paramètre `publication_action`.

Le comportement métier est présent, mais l’architecture du code et la conception ne décrivent pas la même solution. Il faudra choisir l’une des deux architectures et harmoniser l’autre support.

### 3. Bouton d’édition dans le détail d’un article

Le template `templates/article/show.html.twig` n’affiche pas le bouton « Éditer » pour l’auteur ou l’administrateur, contrairement aux spécifications des templates.

### 4. Suppression administrative d’un article

La suppression existe dans l’espace administrateur. La conception prévoit toutefois son déclenchement dans le formulaire de modification de l’article, avec une confirmation explicite.

Le template `templates/article/form.html.twig` ne contient actuellement aucun bouton de suppression.

### 5. Navigation de l’administrateur

Le template `templates/base.html.twig` affiche simultanément « Administration » et « Mon espace » pour un administrateur. Les spécifications et la maquette demandent d’afficher « Administration » à la place de « Mon espace ».

### 6. Organisation des espaces utilisateur et administrateur

Les maquettes et les spécifications séparent les articles publiés des brouillons. Les templates actuels utilisent une seule collection :

- `templates/profile/index.html.twig` affiche une section unique « Mes articles » ;
- `templates/admin/index.html.twig` affiche une section unique « Tous les articles ».

L’espace administrateur ne présente pas non plus tous les accès prévus dans la maquette : Articles, Catégories, Utilisateurs, Mon profil et Créer un article.

### 7. Gestion des catégories

La conception prévoit l’ajout et la modification des catégories directement dans la page de gestion. L’implémentation utilise actuellement des pages séparées :

- `templates/admin_category/new.html.twig` ;
- `templates/admin_category/edit.html.twig`.

Cette solution fonctionne, mais elle ne correspond pas à la maquette, au schéma ergonomique et aux spécifications JavaScript.

## Écarts JavaScript et AJAX

### 1. Recherche des articles

Les onglets JavaScript et AJAX demandent une recherche Symfony classique en GET avec rechargement de la page. Ils précisent qu’aucun traitement AJAX ne doit être ajouté pour cette fonctionnalité.

L’implémentation actuelle contient pourtant :

- une branche AJAX dans `ArticleController::index()` ;
- le script `assets/js/article-search.js` ;
- le fragment `templates/article/_list.html.twig` destiné au retour AJAX.

Selon les spécifications actuelles, il faut conserver le formulaire GET utilisable sans JavaScript et retirer l’interception AJAX. Le traitement du like suffit à mettre en pratique AJAX dans le projet.

### 2. Réinitialisation de la recherche

Dans `article-search.js`, la croix est masquée lorsque le texte recherché est vide, même si une catégorie est active. Elle devrait rester visible dès qu’un mot-clé ou une catégorie filtre la liste.

### 3. Réponse du like

La spécification attend la propriété JSON `likeCount`, tandis que le contrôleur renvoie `likesCount`. Le contrôleur et le JavaScript sont cohérents entre eux, mais pas avec le document de conception.

### 4. Erreur lors du like

Le message portant l’attribut `data-like-error` est rendu dans la partie du template réservée au visiteur. Il n’est donc pas présent pour l’utilisateur connecté qui utilise réellement le bouton de like.

### 5. Comportements restant à réaliser

Les comportements suivants sont prévus mais ne sont pas encore développés :

- prévisualisation d’un avatar à l’inscription ;
- prévisualisation de l’image d’un article ;
- prévisualisation de l’avatar dans le profil ;
- modales Bootstrap de confirmation ;
- édition interactive des catégories ;
- menu de navigation responsive Bootstrap ;
- alertes Bootstrap pouvant être fermées.

## Formulaires et sécurité

### 1. Option « Se souvenir de moi »

Cette option figure dans la maquette et les spécifications, mais elle n’est présente ni dans `templates/security/login.html.twig` ni dans la configuration `security.yaml`.

### 2. Erreurs du formulaire de commentaire

Lorsque le commentaire est invalide, `CommentController::create()` redirige vers le détail de l’article et affiche seulement un message flash. Les erreurs détaillées du formulaire et le contenu saisi sont perdus.

Les spécifications demandent de réafficher le détail de l’article avec le formulaire invalide.

### 3. Validation de l’adresse e-mail à l’inscription

Le formulaire d’inscription utilise `EmailType`, mais ne possède pas explicitement les contraintes Symfony `NotBlank` et `Email`, contrairement au formulaire de modification du profil.

### 4. Redirection après l’inscription

Le contrôleur connecte automatiquement le nouvel utilisateur. Les spécifications prévoient une redirection vers la page de connexion.

### 5. Message lié au bannissement

Le `UserChecker` bloque correctement les comptes bannis. Le template de connexion remplace cependant toute erreur par le message générique « Adresse e-mail ou mot de passe incorrect » et ne présente donc pas le message spécifique de suspension.

## Écarts dans les documents de conceptualisation

### 1. Contact

Le schéma ergonomique et le tableur décrivent encore un formulaire de contact traité par Symfony. La dernière décision du développeur est de créer uniquement la vue, sans traitement.

Le code actuel respecte cette dernière décision. Les documents de conception doivent être actualisés.

### 2. Connexion

La maquette HiFi montre encore « Mot de passe oublié », alors que cette fonctionnalité a été retirée. Le code et les spécifications écrites respectent la décision la plus récente.

### 3. Rôle « Auteur »

La maquette de gestion des utilisateurs emploie parfois « Auteur » comme rôle. Le modèle de données ne contient que les rôles utilisateur et administrateur, et tout utilisateur connecté peut devenir auteur en créant un article.

La maquette devrait donc afficher « Utilisateur », sauf si « Auteur » devient un simple statut informatif et non un rôle Symfony.

### 4. Type du contenu d’un commentaire

Le MPD indique `TEXT` pour `comment.content`, tandis que la migration Doctrine a créé un champ `LONGTEXT`. Il faut harmoniser le MPD ou le mapping Doctrine selon le choix retenu.

### 5. MPD complet

Les images individuelles des entités contiennent correctement les colonnes, les types SQL et les contraintes. L’image du MPD complet masque toutefois les contraintes SQL et ne permet pas de vérifier seule tous les détails du modèle.

### 6. Structure Figma accessible

Au moment de l’audit, le fichier Figma en ligne ne remontait qu’une page principale intitulée `01 - Brief et références`. Les douze maquettes HiFi restent disponibles dans les exports locaux du projet.

## Organisation Twig à terminer

Les spécifications prévoient des fragments réutilisables qui n’existent pas encore :

- `_partials/_header.html.twig` ;
- `_partials/_footer.html.twig` ;
- `_partials/_article_card.html.twig` ;
- `_partials/_avatar.html.twig`.

L’interface fonctionne sans ces fragments, mais leur absence entraîne des répétitions et ne respecte pas l’organisation prévue.

## Vérifications non couvertes

Aucun test automatisé n’est actuellement présent dans le dossier `tests`. L’audit repose donc sur les contrôles statiques, les commandes Symfony, la validation Doctrine et la lecture du code.

Des tests fonctionnels devront être ajoutés après stabilisation des parcours principaux, en particulier pour :

- les droits de modification des articles ;
- la prévisualisation des brouillons ;
- la publication et la dépublication ;
- les comptes bannis ;
- les actions réservées à l’administrateur ;
- l’unicité d’un like par utilisateur et par article.

## Ordre recommandé des corrections

1. Mettre à jour les documents devenus obsolètes : Contact, mot de passe oublié, rôle Auteur et choix GET/AJAX de la recherche.
2. Corriger les parcours obligatoires : brouillons, édition, publication, dépublication et suppression.
3. Corriger les états des espaces utilisateur et administrateur.
4. Harmoniser les formulaires et les règles de sécurité.
5. Mettre en place les fragments Twig réutilisables.
6. Appliquer Bootstrap et le SCSS conformément aux maquettes.
7. Développer les comportements JavaScript encore prévus.
8. Ajouter des tests fonctionnels avant le prochain commit stable.

## Références

- Cahier des charges : `documents/cahier des charges/Exercice_Bolonews.pdf`
- Schéma ergonomique : `documents/conceptualisation/Schéma Ergonomique/Schéma ergonomique bolonews.drawio`
- MPD : `documents/conceptualisation/Modèles de données/MPD Bolonews.drawio`
- Spécifications : [Google Sheets — Spécifications Controller, Template, JS et AJAX](https://docs.google.com/spreadsheets/d/1zVeGrip0SoVdFRaNK6Wuf1unRZE8UFgBNn5muwh5hSg/edit)
- Maquette : [Figma — Bolonews](https://www.figma.com/design/qO0lol9iPLirftvy9nmrRD/Bolonews)
