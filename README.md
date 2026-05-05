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
- **Harmonisation visuelle** : Fusionner le design de l'invitation de `test.html` avec la page principale `accueil.html`.
- **Animations** : Ajouter des effets d'apparition (Fade-in) sur les éléments au scroll pour rendre le site plus vivant.
- **Photos/Galerie** : Ajouter d'éventuelles photos des mariés ou des lieux de réception.

### ⚙️ Développement & Backend
- **Intégration Supabase** : Remplacer *FormSubmit* par une vraie base de données (le package `@supabase/supabase-js` est déjà installé) pour stocker les R.S.V.P et gérer une vraie liste d'invités sécurisée.
- **Page de remerciement (`merci.html`)** : Créer et designer la page vers laquelle les invités sont redirigés après avoir validé le formulaire.
- **Section Informations Pratiques** : (Optionnel) Ajouter une section "Infos" pour lister les adresses, les parkings, ou une liste de mariage.

---

## 🛠 Technologies utilisées
* **Frontend** : HTML5, CSS3 (Flexbox, CSS Animations), Vanilla JavaScript.
* **Polices** : Google Fonts (`Great Vibes`, `Lato`, `WindSong`, `Cormorant Garamond`).
* **Backend (à venir)** : Supabase pour la gestion de la base de données.
* **Environnement** : Node.js (via `package.json` pour la gestion des dépendances).
