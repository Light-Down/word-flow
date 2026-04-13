-- Wordflow — Initial Database Schema
-- Run this in your Supabase project (SQL Editor) to set up the backend.

-- ─────────────────────────────────────────────
-- licenses
-- ─────────────────────────────────────────────
CREATE TABLE public.licenses (
    id               UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    user_id          UUID UNIQUE NOT NULL REFERENCES auth.users(id),
    model            TEXT NOT NULL DEFAULT 'trial'
                         CHECK (model IN ('trial', 'byok', 'free_tier', 'credits')),
    trial_started_at TIMESTAMPTZ DEFAULT now(),
    trial_duration   INTEGER NOT NULL DEFAULT 9999,  -- days; 9999 = unlimited (Early Access)
    credits_balance  NUMERIC NOT NULL DEFAULT 0.0,
    purchase_source  TEXT,
    purchased_at     TIMESTAMPTZ,
    max_devices      INTEGER NOT NULL DEFAULT 3,
    is_active        BOOLEAN NOT NULL DEFAULT true,
    created_at       TIMESTAMPTZ NOT NULL DEFAULT now(),
    updated_at       TIMESTAMPTZ NOT NULL DEFAULT now()
);

ALTER TABLE public.licenses ENABLE ROW LEVEL SECURITY;

CREATE POLICY "User reads own license"
    ON public.licenses FOR SELECT
    USING (auth.uid() = user_id);

-- ─────────────────────────────────────────────
-- devices
-- ─────────────────────────────────────────────
CREATE TABLE public.devices (
    id          UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    user_id     UUID NOT NULL REFERENCES auth.users(id),
    device_id   TEXT NOT NULL,
    device_name TEXT,
    app_version TEXT,
    os_version  TEXT,
    last_seen   TIMESTAMPTZ NOT NULL DEFAULT now(),
    created_at  TIMESTAMPTZ NOT NULL DEFAULT now()
);

ALTER TABLE public.devices ENABLE ROW LEVEL SECURITY;

CREATE POLICY "User reads own devices"
    ON public.devices FOR SELECT
    USING (auth.uid() = user_id);

CREATE POLICY "User inserts own devices"
    ON public.devices FOR INSERT
    WITH CHECK (auth.uid() = user_id);

CREATE POLICY "User updates own devices"
    ON public.devices FOR UPDATE
    USING (auth.uid() = user_id)
    WITH CHECK (auth.uid() = user_id);

CREATE POLICY "User deletes own devices"
    ON public.devices FOR DELETE
    USING (auth.uid() = user_id);

-- ─────────────────────────────────────────────
-- app_versions
-- ─────────────────────────────────────────────
CREATE TABLE public.app_versions (
    id           UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    version      TEXT UNIQUE NOT NULL,
    min_required TEXT,
    download_url TEXT NOT NULL,
    release_notes TEXT,
    released_at  TIMESTAMPTZ NOT NULL DEFAULT now(),
    is_latest    BOOLEAN NOT NULL DEFAULT false
);

ALTER TABLE public.app_versions ENABLE ROW LEVEL SECURITY;

CREATE POLICY "Anyone reads app versions"
    ON public.app_versions FOR SELECT
    USING (true);

-- ─────────────────────────────────────────────
-- downloads
-- ─────────────────────────────────────────────
CREATE TABLE public.downloads (
    id           UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    user_id      UUID REFERENCES auth.users(id),
    app_version  TEXT,
    downloaded_at TIMESTAMPTZ NOT NULL DEFAULT now(),
    ip_hash      TEXT   -- hashed IP, not the raw address
);

ALTER TABLE public.downloads ENABLE ROW LEVEL SECURITY;

CREATE POLICY "User reads own downloads"
    ON public.downloads FOR SELECT
    USING (auth.uid() = user_id);
