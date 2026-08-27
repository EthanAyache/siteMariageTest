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
- **Vérifier le projet Supabase** : l'URL `ingtrfzjxbtjzisctuyo.supabase.co` ne résout plus (voir la section Administration).
- **Règles RLS** : la clé publiable est visible dans le HTML ; ce sont les policies de la table `invites` qui protègent réellement les données.
- **Section Informations Pratiques** : (Optionnel) adresses, parkings, liste de mariage.

---

## 🛠 Technologies utilisées
* **Frontend** : HTML5, CSS3 (Flexbox, CSS Animations), Vanilla JavaScript.
* **Polices** : Google Fonts (`Great Vibes`, `Lato`, `WindSong`, `Cormorant Garamond`).
* **Backend (à venir)** : Supabase pour la gestion de la base de données.
* **Environnement** : Node.js (via `package.json` pour la gestion des dépendances).

---

## 📁 Structure du projet

Le site en production est **`index.html`** : l'enveloppe animée qui s'ouvre sur
l'invitation complète.

| Chemin | Rôle |
|---|---|
| `index.html` | Le site public. Accepte `?token=XXX` pour personnaliser le RSVP. |
| `admin.html` | Tableau de bord : liste des invités, réponses, messages. |
| `login.html` | Connexion à l'administration (Supabase Auth). |
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

## 🖥 Lancer le site en local

Le site est en HTML/CSS/JS pur : il n'y a **pas de build** à faire. Il suffit d'un
petit serveur statique (ouvrir les fichiers en double-clic via `file://` casserait
Supabase et l'autoplay de la musique).

Depuis le dossier du projet :

```bash
npx serve -l 5173 .
```

Puis ouvrir **http://localhost:5173**

> Le port 5173 est utilisé plutôt que le 3000 pour éviter les conflits avec
> d'autres projets. N'importe quel port libre fait l'affaire.

### Pages disponibles

| Page | URL |
|---|---|
| Site (sans RSVP) | http://localhost:5173/ |
| Site personnalisé invité | http://localhost:5173/?token=XXX |
| Connexion admin | http://localhost:5173/login.html |
| Dashboard admin | http://localhost:5173/admin.html |

⚠️ Le **token** en paramètre d'URL est ce qui déclenche la section RSVP. Sans lui,
le site s'affiche mais le formulaire reste masqué. Les tokens se récupèrent depuis
la page admin, qui fournit le lien complet à copier.

ℹ️ Aucune variable d'environnement n'est nécessaire : les pages appellent Supabase
directement depuis le navigateur avec la clé publiable.

