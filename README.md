# Projet : Site de Mariage 

Ce projet a pour but de créer le meilleur site de mariage possible et de permettre aux invités de confirmer leur présence facilement.

## 🎯 Objectif du projet
Fournir une plateforme élégante, responsive et fonctionnelle pour :
- Partager le faire-part numérique et le programme de la journée.
- Récolter les réponses des invités (R.S.V.P) via un formulaire dynamique.
- Offrir une expérience utilisateur agréable avec de la musique de fond et des animations.

---

## 🚀 État d'avancement (Ce qui est déjà fait)

### 1. Structure Globale & Navigation
- **Barre de navigation sticky** : Elle disparaît intelligemment au scroll vers le bas et réapparaît au scroll vers le haut.
- **Responsive Design** : Le site s'adapte aux mobiles et tablettes (Menu adaptatif, redimensionnement des éléments).

### 2. Contenu & Fonctionnalités
- **Page de garde (Hero) & Faire-part** : Affichage des prénoms en doré avec la date du mariage. Un design de carte d'invitation plus poussé a été testé (`test.html`).
- **Lecteur de musique** : Une musique d'ambiance se lance automatiquement à l'arrivée (ou à la première interaction avec la page) avec un bouton de contrôle "Play/Pause" flottant.
- **Programme (Timeline)** : Affichage clair des différentes étapes de la journée (Cérémonie, Cocktail, Dîner).
- **Formulaire R.S.V.P fonctionnel** : 
  - Champs dynamiques (demande le nombre de personnes si l'invité répond "Oui").
  - Validation en JavaScript pour éviter les envois vides.
  - Connexion temporaire au service *FormSubmit* pour recevoir les réponses par e-mail.
- **Footer** : Personnalisé avec des étoiles de David et la mention בס״ד.

---

## ⏳ Ce qu'il reste à faire (To-Do List)

### 🎨 Design & UI/UX
- **Animations** : Ajouter des effets d'apparition (fade-in) sur les éléments au scroll.
- **Photos/Galerie** : Ajouter d'éventuelles photos des mariés ou des lieux de réception.
- **Poids des images** : `accueil.png` (2,8 Mo) et `fond.png` (2,5 Mo) gagneraient à passer en WebP.

### ⚙️ Développement & Backend
- **Envoi des e-mails** : « mot de passe oublié » utilise `mail()` de PHP, qui marche sur la plupart des hébergeurs mais pas sous WAMP en local.
- **Section Informations Pratiques** : (Optionnel) adresses, parkings, liste de mariage.

---

## 🛠 Technologies utilisées
* **Frontend** : HTML5, CSS3 (Flexbox, CSS Animations), Vanilla JavaScript.
* **Polices** : Google Fonts (`Great Vibes`, `Lato`, `WindSong`, `Cormorant Garamond`).
* **Backend** : API en PHP (dossier `api/`) et base MySQL. L'ancien Supabase n'est plus utilisé.

---

## 📁 Structure du projet

Le site en production est **`index.html`** : l'enveloppe animée qui s'ouvre sur
l'invitation complète.

| Chemin | Rôle |
|---|---|
| `index.html` | Le site public. Accepte `?token=XXX` pour personnaliser le RSVP. |
| `admin.html` | Tableau de bord : liste des invités, réponses, messages. |
| `login.html` | Connexion à l'administration, avec « Mot de passe oublié ? ». |
| `reinitialiser.html` | Page du lien reçu par e-mail pour choisir un nouveau mot de passe. |
| `api/` | API PHP : comptes, session, invités, RSVP. Lit la config dans `.env`. |
| `mysql/schema.sql` | Les tables à importer dans phpMyAdmin. |
| `scripts/creer-compte.php` | Crée un compte admin ou marié (ligne de commande). |
| `image/enveloppe/` | Assets du site : enveloppe, cachet, artwork, ornements. |
| `archive/` | Anciennes versions conservées en sauvegarde (voir plus bas). |

### Le dossier `archive/`

Rien n'a été supprimé. Les fichiers de l'ancienne version y sont conservés :

| Fichier | Ce que c'était |
|---|---|
| `index-ancien.html` | L'ancienne page d'entrée « Ouvrir l'invitation ». |
| `accueil.html` | L'ancienne page d'accueil avec la timeline. |
| `invite.html` / `invite2.html` | Les anciennes pages invité (RSVP par token). |
| `merci.html` | L'ancienne page de remerciement. |
| `test.html` | Prototype de carte d'invitation. |
| `api/` | Fonctions serverless Vercel, jamais appelées par les pages. |
| `scripts/` | Import de `invites.csv` vers Supabase. |
| `design-enveloppe-source/` | Le canvas d'origine de l'enveloppe et ses assets bruts. |

⚠️ Les chemins d'images de ces pages archivées sont relatifs à la racine : il faut
les ajuster si l'une d'elles est remise en service.

---

## 🔗 Lien avec l'administration

`index.html` lit le paramètre `token` dans l'URL, exactement le lien que génère
l'admin (`admin.html` construit `.../index.html?token=XXX`).

- **Avec un token valide** : le prénom de l'invité s'affiche, les listes de places
  sont limitées à son nombre de places, et la réponse est écrite dans la table
  `invites` (colonnes `rsvp`, `mairie`, `nb_mairie`, `houppa`, `nb_houppa`, `message`).
- **Sans token** : le site s'affiche normalement, mais la section RSVP et son
  onglet de navigation sont masqués — aucune réponse orpheline ne peut être créée.

`rsvp` vaut `decline` si l'invité refuse **les deux** événements, sinon `confirme`.

---

## 🗄 Base de données et comptes

1. Dans phpMyAdmin, sélectionner la base `mariage` et importer `mysql/schema.sql`.
   Il crée les tables `invites`, `comptes` et `reinitialisations_mdp`.
2. Copier les identifiants de la base dans le fichier `.env` à la racine (jamais dans
   le repo, il est dans `.gitignore`) :

   ```
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_NAME=mariage
   DB_USER=mariage
   DB_PASSWORD=...
   SITE_URL=https://ton-domaine.fr
   MAIL_FROM=
   ```

   Sur l'hébergeur, le `.env` n'arrive pas avec le repo : le créer à la main à la
   racine du site, ou mettre ces valeurs dans ses variables d'environnement.
   Le `.htaccess` empêche les visiteurs de le télécharger.
3. Créer les comptes. Deux rôles, qui ont tous les deux accès au tableau de bord :
   `admin` (administrateur du site) et `marie` (les mariés).

   ```bash
   php scripts/creer-compte.php ethan@exemple.fr "Ethan" admin
   php scripts/creer-compte.php salomee@exemple.fr "Salomée" marie
   ```

   Le mot de passe est demandé ensuite. Relancer la commande sur une adresse existante
   change son mot de passe. Si l'hébergeur n'a pas de terminal, ajouter `--sql` à la
   fin : le script affiche une requête à coller dans l'onglet SQL de son phpMyAdmin.

### Mot de passe oublié

Le lien « Mot de passe oublié ? » de `login.html` envoie un e-mail avec un lien vers
`reinitialiser.html`, valable 1 heure et utilisable une seule fois. Sous WAMP, PHP ne
sait pas envoyer d'e-mail : le lien est alors écrit dans le log d'erreurs PHP.

## 🖥 Lancer le site en local

Le site a besoin de PHP pour son API. Démarrer WAMP (pour MySQL), puis depuis le
dossier du projet :

```bash
php -S localhost:8000
```

Puis ouvrir **http://localhost:8000**

> ⚠️ `npx serve` ne suffit plus : il ne sait pas exécuter le PHP de `api/`.

### Pages disponibles

| Page | URL |
|---|---|
| Site (sans RSVP) | http://localhost:8000/ |
| Site personnalisé invité | http://localhost:8000/?token=XXX |
| Connexion admin | http://localhost:8000/login.html |
| Dashboard admin | http://localhost:8000/admin.html |

⚠️ Le **token** en paramètre d'URL est ce qui déclenche la section RSVP. Sans lui,
le site s'affiche mais le formulaire reste masqué. Les tokens se récupèrent depuis
la page admin, qui fournit le lien complet à copier.
