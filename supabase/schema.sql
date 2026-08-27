-- ============================================================================
--  Site de mariage Salomée & Ethan — schéma de la base
--
--  Reconstitué depuis le code : index.html (site invité), admin.html
--  (tableau de bord), archive/invite.html et archive/scripts/import-csv.js.
--
--  À exécuter tel quel dans le SQL Editor du projet Supabase.
-- ============================================================================

-- ── Table des invités ───────────────────────────────────────────────────────
create table if not exists public.invites (
  id          bigint generated always as identity primary key,
  created_at  timestamptz not null default now(),

  -- identité, saisie par l'administration
  nom         text    not null,
  email       text,
  places      integer not null default 1 check (places >= 1),
  token       text    not null unique,

  -- réponse de l'invité
  rsvp        text    not null default 'en attente'
              check (rsvp in ('en attente', 'confirme', 'decline')),
  mairie      text    check (mairie in ('oui', 'non')),
  nb_mairie   integer not null default 0 check (nb_mairie >= 0),
  houppa      text    check (houppa in ('oui', 'non')),
  nb_houppa   integer not null default 0 check (nb_houppa >= 0),
  message     text
);

create index if not exists invites_token_idx on public.invites (token);
create index if not exists invites_nom_idx   on public.invites (nom);

alter table public.invites enable row level security;

-- ── Accès administrateur ────────────────────────────────────────────────────
-- Seul un utilisateur connecté (Authentication > Users) voit la liste complète.
drop policy if exists "admin acces complet" on public.invites;
create policy "admin acces complet"
  on public.invites for all
  to authenticated
  using (true)
  with check (true);

-- Aucune policy pour le rôle anon : un visiteur ne peut PAS lire la table.
-- Il passe obligatoirement par les deux fonctions ci-dessous, qui exigent
-- de connaître le token. Cela évite qu'une clé publiable visible dans le
-- HTML permette d'aspirer toute la liste des invités.

-- ── Lecture d'un seul invité, par son token ─────────────────────────────────
create or replace function public.get_invite(p_token text)
returns table (
  nom text, places integer, rsvp text,
  mairie text, nb_mairie integer,
  houppa text, nb_houppa integer, message text
)
language sql
security definer
set search_path = public
as $$
  select i.nom, i.places, i.rsvp,
         i.mairie, i.nb_mairie,
         i.houppa, i.nb_houppa, i.message
  from public.invites i
  where i.token = p_token;
$$;

-- ── Enregistrement de la réponse ────────────────────────────────────────────
create or replace function public.repondre_invite(
  p_token     text,
  p_mairie    text,
  p_nb_mairie integer,
  p_houppa    text,
  p_nb_houppa integer,
  p_message   text
)
returns void
language plpgsql
security definer
set search_path = public
as $$
declare
  v_places integer;
begin
  select places into v_places from public.invites where token = p_token;
  if v_places is null then
    raise exception 'Token inconnu';
  end if;

  if p_mairie not in ('oui', 'non') or p_houppa not in ('oui', 'non') then
    raise exception 'Réponse invalide';
  end if;

  update public.invites set
    mairie    = p_mairie,
    houppa    = p_houppa,
    -- on borne le nombre de personnes au quota de l'invité
    nb_mairie = case when p_mairie = 'oui'
                     then least(greatest(coalesce(p_nb_mairie, 0), 1), v_places) else 0 end,
    nb_houppa = case when p_houppa = 'oui'
                     then least(greatest(coalesce(p_nb_houppa, 0), 1), v_places) else 0 end,
    message   = left(coalesce(p_message, ''), 1000),
    rsvp      = case when p_mairie = 'non' and p_houppa = 'non'
                     then 'decline' else 'confirme' end
  where token = p_token;
end;
$$;

revoke all on function public.get_invite(text) from public;
revoke all on function public.repondre_invite(text, text, integer, text, integer, text) from public;
grant execute on function public.get_invite(text) to anon, authenticated;
grant execute on function public.repondre_invite(text, text, integer, text, integer, text) to anon, authenticated;

-- ── Exemple d'invités (à supprimer avant la mise en production) ─────────────
-- insert into public.invites (nom, places, email, token) values
--   ('Marie Dupont',   2, 'marie@gmail.com', encode(gen_random_bytes(8), 'hex')),
--   ('Famille Martin', 5, null,              encode(gen_random_bytes(8), 'hex'));
