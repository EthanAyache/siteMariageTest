import { createClient } from '@supabase/supabase-js'

const supabase = createClient(
  process.env.SUPABASE_URL,
  process.env.SUPABASE_KEY
)

export default async function handler(req, res) {
  res.setHeader('Access-Control-Allow-Origin', '*')

  if (req.method !== 'POST') {
    return res.status(405).json({ error: 'Méthode non autorisée' })
  }

  const { token, rsvp } = JSON.parse(req.body)

  const { error } = await supabase
    .from('invites')
    .update({ rsvp })
    .eq('token', token)

  if (error) {
    return res.status(500).json({ error: 'Erreur mise à jour' })
  }

  res.status(200).json({ success: true })
}