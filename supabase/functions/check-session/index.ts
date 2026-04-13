import "jsr:@supabase/functions-js/edge-runtime.d.ts";
import { createClient } from "jsr:@supabase/supabase-js@2";

Deno.serve(async (req: Request) => {
  const corsHeaders = {
    'Access-Control-Allow-Origin': '*',
    'Access-Control-Allow-Headers': 'authorization, x-client-info, apikey, content-type',
  };

  if (req.method === 'OPTIONS') {
    return new Response('ok', { headers: corsHeaders });
  }

  try {
    const supabase = createClient(
      Deno.env.get('SUPABASE_URL')!,
      Deno.env.get('SUPABASE_ANON_KEY')!,
      { global: { headers: { Authorization: req.headers.get('Authorization')! } } }
    );

    // Resolve user from JWT
    const { data: { user }, error: authError } = await supabase.auth.getUser();
    if (authError || !user) {
      return new Response(JSON.stringify({ valid: false, reason: 'unauthorized' }), {
        status: 401,
        headers: { ...corsHeaders, 'Content-Type': 'application/json' },
      });
    }

    // Load license
    const { data: license, error: licenseError } = await supabase
      .from('licenses')
      .select('*')
      .eq('user_id', user.id)
      .single();

    // Load latest app version (shared for all response paths)
    const { data: latestVersion } = await supabase
      .from('app_versions')
      .select('version, download_url, release_notes, min_required')
      .eq('is_latest', true)
      .single();

    if (licenseError || !license) {
      // First launch — create a byok license (Early Access: everyone gets full access)
      const { data: newLicense } = await supabase
        .from('licenses')
        .insert({
          user_id: user.id,
          model: 'byok',
          is_active: true,
          trial_started_at: new Date().toISOString()
        })
        .select()
        .single();

      return new Response(JSON.stringify({
        valid: true,
        model: 'byok',
        trial_expired: false,
        trial_days_left: 0,
        max_devices: newLicense?.max_devices ?? 3,
        latest_version: latestVersion?.version ?? null,
        download_url: latestVersion?.download_url ?? null,
        release_notes: latestVersion?.release_notes ?? null,
        min_required: latestVersion?.min_required ?? null,
      }), {
        headers: { ...corsHeaders, 'Content-Type': 'application/json' },
      });
    }

    // Calculate trial status
    let trialExpired = false;
    let trialDaysLeft = 0;

    if (license.model === 'trial') {
      const startedAt = new Date(license.trial_started_at);
      const expiresAt = new Date(startedAt.getTime() + license.trial_duration * 24 * 60 * 60 * 1000);
      const now = new Date();
      trialExpired = now > expiresAt;
      trialDaysLeft = trialExpired
        ? 0
        : Math.ceil((expiresAt.getTime() - now.getTime()) / (1000 * 60 * 60 * 24));
    }

    return new Response(JSON.stringify({
      valid: license.is_active,
      model: license.model,
      trial_expired: trialExpired,
      trial_days_left: trialDaysLeft,
      credits_balance: license.credits_balance,
      max_devices: license.max_devices ?? 3,
      latest_version: latestVersion?.version ?? null,
      download_url: latestVersion?.download_url ?? null,
      release_notes: latestVersion?.release_notes ?? null,
      min_required: latestVersion?.min_required ?? null,
    }), {
      headers: { ...corsHeaders, 'Content-Type': 'application/json' },
    });

  } catch (err) {
    return new Response(JSON.stringify({ valid: false, reason: 'server_error' }), {
      status: 500,
      headers: { ...corsHeaders, 'Content-Type': 'application/json' },
    });
  }
});
