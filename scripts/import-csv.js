import { createClient } from '@supabase/supabase-js'
import { parse } from 'csv-parse/sync'
import { readFileSync } from 'fs'
import { randomBytes } from 'crypto'

const supabase = createClient(
  'https://ingtrfzjxbtjzisctuyo.supabase.co',  // déjà bon
  'sb_publishable_bzP_bkQglIR0yTB4kZiITg_wFnq6m3h'  // ← remplace ça par ta publishable key
)

const csv = readFileSync('./invites.csv', 'utf-8')
const invites = parse(csv, { columns: true, skip_empty_lines: true })

for (const invite of invites) {
  const token = randomBytes(8).toString('hex')
  
  const { error } = await supabase.from('invites').insert({
    nom: invite.nom,
    places: parseInt(invite.places),
    email: invite.email || null,
    token
  })

  if (error) {
    console.error(`Erreur pour ${invite.nom}:`, error.message)
  } else {
    console.log(`✅ ${invite.nom} → site-mariage-three.vercel.app/invite.html?token=${token}`)
  }
}