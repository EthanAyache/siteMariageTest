import { createClient } from '@supabase/supabase-js'

const supabase = createClient(
  process.env.SUPABASE_URL,
  process.env.SUPABASE_KEY
)

export default async function handler(req, res) {
  res.setHeader('Access-Control-Allow-Origin', '*')
  
  const { token } = req.query

  if (!token) {
    return res.status(400).json({ error: 'Token manquant' })
  }

  const { data, error } = await supabase
    .from('invites')
    .select('nom, places, rsvp')
    .eq('token', token)
    .single()

  if (error || !data) {
    return res.status(404).json({ error: 'Invité introuvable' })
  }

  res.status(200).json(data)
}