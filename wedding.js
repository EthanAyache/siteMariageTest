(() => { 'use strict'; const el = id => document.getElementById(id);
            var SUPABASE_URL = 'https://cdxtqfhnechqomkpediw.supabase.co';
            var SUPABASE_ANON_KEY = 'sb_publishable_YnYkAoVGyWkGHnH-Cd-Ddg_wuvbL6Os';

            var form = el('rsvpForm'), done = el('rsvpDone'), rsvpSection = el('rsvp');
            var currentToken = null, currentPlaces = 1;

            function hideRsvp() {
                rsvpSection.hidden = true;
                if (el('navRsvp')) el('navRsvp').hidden = true;

            }

            /* remplit un select de 1 à N places */
            function fillPlaces(select, places) {
                select.innerHTML = '';
                var opt0 = document.createElement('option');
                opt0.value = ''; opt0.textContent = 'Nombre de personnes';
                opt0.disabled = true; opt0.selected = true;
                select.appendChild(opt0);
                for (var i = 1; i <= places; i++) {
                    var o = document.createElement('option');
                    o.value = i;
                    o.textContent = i + (i > 1 ? ' personnes' : ' personne');
                    select.appendChild(o);
                }
            }

            /* le compteur n'apparaît que si la réponse est « oui » */
            function bindToggle(name, wrapId) {
                Array.prototype.forEach.call(document.querySelectorAll('input[name="' + name + '"]'), function (r) {
                    r.addEventListener('change', function () {
                        el(wrapId).hidden = (r.value !== 'oui');
                    });
                });
            }
            bindToggle('mairie', 'wrapNbMairie');
            bindToggle('houppa', 'wrapNbHouppa');

            function showThanks(reponse) {
                form.hidden = true;
                done.hidden = false;
                if (reponse === 'decline') {
                    el('merciTitre').textContent = 'Merci pour votre réponse';
                    el('merciTexte').innerHTML = 'Nous regrettons de ne pas vous compter parmi nous.<br />Vous serez dans nos pensées ce jour-là.';
                } else {
                    el('merciTitre').textContent = 'Merci !';
                    el('merciTexte').innerHTML = 'Votre réponse a bien été enregistrée.<br />Salomée &amp; Ethan ont hâte de vous retrouver.';
                }
            }

            var supa = null;
            try {
                if (window.supabase) supa = window.supabase.createClient(SUPABASE_URL, SUPABASE_ANON_KEY);
            } catch (e) { supa = null; }

            function loadInvite() {
                var token = new URLSearchParams(window.location.search).get('token');
                if (!token) { hideRsvp(); return; } if (!supa) { hideRsvp(); el('rsvpNotice').textContent = 'Le service de réponse est indisponible. Rechargez la page ou contactez les mariés.'; return; }

                /* get_invite : fonction SECURITY DEFINER. Le rôle anon n'a aucun
                   accès direct à la table, il faut connaître le token. */
                supa.rpc('get_invite', { p_token: token })
                    .then(function (res) {
                        var inv = res.data && res.data.length ? res.data[0] : null;
                        if (res.error || !inv) { hideRsvp(); el('rsvpNotice').textContent = 'Ce lien invité est invalide ou temporairement indisponible. Contactez les mariés.'; return; }
                        currentToken = token;
                        currentPlaces = inv.places || 1;

                        rsvpSection.hidden = false; el('navRsvp').hidden = false; el('rsvpNotice').hidden = true;
                        el('rsvpGreeting').textContent = 'Cher(e) ' + inv.nom + ', merci de confirmer votre présence.';
                        fillPlaces(el('nbMairie'), currentPlaces);
                        fillPlaces(el('nbHouppa'), currentPlaces);

                        if (inv.rsvp === 'confirme' || inv.rsvp === 'decline') showThanks(inv.rsvp);
                    })
                    .catch(function () { hideRsvp(); el('rsvpNotice').textContent = 'Impossible de charger votre invitation. Réessayez dans quelques instants.'; });
            }
            loadInvite();

            function fieldError(id, msg) {
                var e = el(id);
                e.textContent = msg;
                e.hidden = false;
            }

            form.addEventListener('submit', function (e) {
                e.preventDefault();
                el('errMairie').hidden = true;
                el('errHouppa').hidden = true;
                el('errGlobal').hidden = true;

                var mairie = document.querySelector('input[name="mairie"]:checked');
                var houppa = document.querySelector('input[name="houppa"]:checked');
                var ok = true;

                if (!mairie) { fieldError('errMairie', 'Merci de sélectionner une option.'); ok = false; }
                else if (mairie.value === 'oui' && !el('nbMairie').value) { fieldError('errMairie', 'Merci de préciser le nombre de personnes.'); ok = false; }

                if (!houppa) { fieldError('errHouppa', 'Merci de sélectionner une option.'); ok = false; }
                else if (houppa.value === 'oui' && !el('nbHouppa').value) { fieldError('errHouppa', 'Merci de préciser le nombre de personnes.'); ok = false; }

                if (!ok || !currentToken || !supa) {
                    if (ok && !currentToken) fieldError('errGlobal', 'Lien invalide : réponse impossible à enregistrer.');
                    return;
                }

                var btn = el('rsvpSubmit');
                btn.disabled = true;
                btn.textContent = 'Envoi en cours…';

                /* décliné seulement si les deux événements sont refusés.
                   Le serveur recalcule cette valeur : c'est lui qui fait foi. */
                var reponse = (mairie.value === 'non' && houppa.value === 'non') ? 'decline' : 'confirme';

                supa.rpc('repondre_invite', {
                    p_token: currentToken,
                    p_mairie: mairie.value,
                    p_nb_mairie: mairie.value === 'oui' ? parseInt(el('nbMairie').value, 10) : 0,
                    p_houppa: houppa.value,
                    p_nb_houppa: houppa.value === 'oui' ? parseInt(el('nbHouppa').value, 10) : 0,
                    p_message: el('guestMessage').value
                })
                    .then(function (res) {
                        if (res.error) throw res.error;
                        showThanks(reponse);
                    })
                    .catch(function () {
                        fieldError('errGlobal', "Une erreur est survenue, merci de réessayer.");
                        btn.disabled = false;
                        btn.textContent = 'Envoyer ma réponse';
                    });
            });


const music = el('bgMusic'), button = el('musicBtn'); music.volume = .4; const paint = () => { button.setAttribute('aria-pressed', String(!music.paused)); button.textContent = music.paused ? '♫ Musique' : 'Ⅱ Pause'; }; button.addEventListener('click', async () => { if(music.paused) { try { await music.play(); } catch { el('musicStatus').textContent = 'La musique est indisponible.'; } } else music.pause(); paint(); }); music.addEventListener('play',paint); music.addEventListener('pause',paint);
const target = new Date('2027-06-13T18:30:00+02:00'); function countdown(){ const days = Math.max(0,Math.ceil((target-new Date())/86400000)); el('days').textContent=days; el('countLabel').textContent=days ? 'jours avant notre oui' : 'Le grand jour est arrivé'; } countdown(); setInterval(countdown,60000);
})();
(() => { const intro=document.getElementById('envelopeIntro'); document.body.classList.add('sealed'); let opening=false; function finish(){intro.classList.add('opened');document.body.classList.remove('sealed');document.getElementById('hero-title').setAttribute('tabindex','-1');document.getElementById('hero-title').focus({preventScroll:true});setTimeout(()=>intro.hidden=true,750)} function open(skip){if(opening)return; opening=true; if(skip||matchMedia('(prefers-reduced-motion: reduce)').matches){finish();return}intro.classList.add('opening');setTimeout(finish,1700)} document.getElementById('openInvitation').onclick=()=>open(false);document.getElementById('waxSeal').onclick=()=>open(false);document.getElementById('skipIntro').onclick=()=>open(true);document.addEventListener('keydown',e=>{if(e.key==='Escape'&&!intro.hidden)open(true)}); })();
