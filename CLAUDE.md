# Projektkontext: laravel-sharded-storage

## Ziel
Wiederverwendbares Laravel-Composer-Package, das gespeicherte Dateien per
Hash-Sharding auf Unterverzeichnisse verteilt (z. B. `documents/a3/f9/a3f9....pdf`),
damit bei zehntausenden bis Millionen Dateien kein einzelnes Verzeichnis überläuft.
Soll öffentlich über Packagist veröffentlicht werden.

## Architektur
- `src/PathSharder.php` – reine Pfadlogik ohne Laravel-Abhängigkeit.
  Nutzt die ersten Zeichen des Dateinamens als Shard, wenn der Name ein
  ausreichend langer alphanumerischer Hash ist; sonst sha256 des Namens.
- `src/ShardedStorage.php` – speichert über Laravels Filesystem (jede Disk):
  `store()`, `storeAs()`, `put()`, `pathFor()`, `disk()` (gibt Klon zurück).
  Naming-Strategien: `random` (hashName, 40 Zeichen) oder `content`
  (Inhalts-Hash, dedupliziert – existierende Datei wird nicht neu geschrieben).
- `src/ShardedStorageServiceProvider.php` – Singletons, Config-Publishing
  (Tag `sharded-storage-config`), Makro `UploadedFile::storeSharded()`.
- `src/Facades/ShardedStorage.php` – Facade.
- `config/sharded-storage.php` – disk, depth (2), segment_length (2), naming, hash_algorithm.

## Konventionen
- Vendor `stormkix`, Namespace `Stormkix\ShardedStorage`.
- Der zurückgegebene relative Pfad wird in der Datenbank gespeichert;
  Lesen/Löschen läuft danach über `Storage::disk(...)`.
- PHP ^8.2, Laravel 11–13, Tests mit Orchestra Testbench + PHPUnit.
- Tests: `composer test`. Keine Tests mit `UploadedFile::fake()->image()`
  (benötigt GD), stattdessen `->create()`.
- CI: `.github/workflows/tests.yml` (Matrix PHP 8.2–8.4 × Laravel 11–13).

## Offene Punkte
1. GitHub-Repo anlegen, pushen, `v0.1.0` oder `v1.0.0` taggen, bei Packagist einreichen.
2. Idee: Artisan-Befehl, der bestehende flach gespeicherte Dateien in die
   Shard-Struktur migriert und Pfade in der DB aktualisiert.
