-- ============================================================================
--  Site de mariage Salomée & Ethan — schéma de la base (MySQL / MariaDB)
--
--  Version MySQL de supabase/schema.sql, pour l'hébergeur.
--
--  À importer dans phpMyAdmin : sélectionner la base « mariage » à gauche,
--  puis onglet « Importer » (ou coller ce fichier dans l'onglet « SQL »).
-- ============================================================================

-- Sélectionne la base (évite l'erreur #1046 « Aucune base n'a été
-- sélectionnée »). À adapter si la base porte un autre nom (DB_NAME du .env).
USE mariage;

-- ── Table des invités ───────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS invites (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,

  -- identité, saisie par l'administration
  nom         VARCHAR(255) NOT NULL,
  email       VARCHAR(255) NULL,
  places      INT UNSIGNED NOT NULL DEFAULT 1,
  token       VARCHAR(64)  NOT NULL,

  -- réponse de l'invité
  rsvp        ENUM('en attente', 'confirme', 'decline') NOT NULL DEFAULT 'en attente',
  mairie      ENUM('oui', 'non') NULL,
  nb_mairie   INT UNSIGNED NOT NULL DEFAULT 0,
  houppa      ENUM('oui', 'non') NULL,
  nb_houppa   INT UNSIGNED NOT NULL DEFAULT 0,
  message     TEXT         NULL,

  PRIMARY KEY (id),
  UNIQUE KEY invites_token_uq (token),
  KEY invites_nom_idx (nom)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Pas de fonctions ni de policies ici, contrairement à Supabase : la lecture
-- par token, l'enregistrement de la réponse et l'accès admin sont gérés par
-- l'API PHP (dossier api/), qui lit les identifiants dans le .env.

-- ── Comptes qui accèdent à l'administration ─────────────────────────────────
-- role « admin » : administrateur du site ; role « marie » : les mariés.
-- Les deux ont accès au tableau de bord. Les comptes se créent avec
-- scripts/creer-compte.php (jamais de mot de passe en clair ici).
CREATE TABLE IF NOT EXISTS comptes (
  id                  INT UNSIGNED NOT NULL AUTO_INCREMENT,
  created_at          DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  nom                 VARCHAR(255) NOT NULL,
  email               VARCHAR(255) NOT NULL,
  mot_de_passe        VARCHAR(255) NOT NULL,   -- empreinte password_hash()
  role                ENUM('admin', 'marie') NOT NULL DEFAULT 'marie',
  derniere_connexion  DATETIME     NULL,

  PRIMARY KEY (id),
  UNIQUE KEY comptes_email_uq (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Liens « mot de passe oublié » ───────────────────────────────────────────
-- On ne garde que l'empreinte SHA-256 du jeton envoyé par e-mail : quelqu'un
-- qui lit la base ne peut pas s'en servir. Valable 1 h, une seule fois.
CREATE TABLE IF NOT EXISTS reinitialisations_mdp (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  compte_id   INT UNSIGNED NOT NULL,
  token_hash  CHAR(64)     NOT NULL,
  expire_le   DATETIME     NOT NULL,
  utilise_le  DATETIME     NULL,

  PRIMARY KEY (id),
  UNIQUE KEY reinit_token_uq (token_hash),
  KEY reinit_compte_idx (compte_id),
  CONSTRAINT reinit_compte_fk FOREIGN KEY (compte_id)
    REFERENCES comptes (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Exemple d'invités (à supprimer avant la mise en production) ─────────────
-- INSERT INTO invites (nom, places, email, token) VALUES
--   ('Marie Dupont',   2, 'marie@gmail.com', LEFT(MD5(RAND()), 16)),
--   ('Famille Martin', 5, NULL,              LEFT(MD5(RAND()), 16));
