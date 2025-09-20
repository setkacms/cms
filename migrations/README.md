Plugin migrations
-----------------

Core migrations of the CMS live directly under this directory and are discovered via the default `@app/migrations` path.

- Place plugin-specific migrations under a dedicated directory per plugin.
- The console `migrate` command already aggregates plugin paths via `PluginRegistry`.
- Recommended layout for a plugin package:

```
PluginRoot/
  src/
  migrations/
    mYYYYMMDD_HHMMSS_plugin_init.php
```

Expose the path via either a static method `migrationsPath(): string` or a `MIGRATIONS_PATH` constant on your plugin class so `MigrateController` can discover it.
