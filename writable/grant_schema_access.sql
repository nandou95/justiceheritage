-- Grant app user access to all JusticeHeritage schemas
DO $$
DECLARE
    sch text;
    schemas text[] := ARRAY[
        'administration',
        'audit_log',
        'juridiction',
        'localite',
        'notification',
        'plaignant',
        'plainte',
        'recours',
        'transfert',
        'verdict',
        'audience',
        'convocation',
        'public'
    ];
BEGIN
    FOREACH sch IN ARRAY schemas LOOP
        IF EXISTS (SELECT 1 FROM pg_namespace WHERE nspname = sch) THEN
            EXECUTE format('GRANT USAGE ON SCHEMA %I TO postgresql', sch);
            EXECUTE format('GRANT SELECT, INSERT, UPDATE, DELETE ON ALL TABLES IN SCHEMA %I TO postgresql', sch);
            EXECUTE format('GRANT USAGE, SELECT ON ALL SEQUENCES IN SCHEMA %I TO postgresql', sch);
            EXECUTE format('ALTER DEFAULT PRIVILEGES IN SCHEMA %I GRANT SELECT, INSERT, UPDATE, DELETE ON TABLES TO postgresql', sch);
            EXECUTE format('ALTER DEFAULT PRIVILEGES IN SCHEMA %I GRANT USAGE, SELECT ON SEQUENCES TO postgresql', sch);
            RAISE NOTICE 'Granted access on schema %', sch;
        ELSE
            RAISE NOTICE 'Schema % does not exist, skipped', sch;
        END IF;
    END LOOP;
END $$;
