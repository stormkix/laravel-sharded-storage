# Laravel Sharded Storage

Verteilt gespeicherte Dateien per Hash-Sharding auf Unterverzeichnisse:

```
storage/app/documents/a3/f9/a3f9c2e1...pdf
```

Mit den Standardwerten (2 Ebenen à 2 Zeichen) entstehen bis zu 65.536 Verzeichnisse,
bei 10 Mio. Dateien also ca. 150 Dateien pro Ordner.

## Installation

```bash
composer require stormkix/laravel-sharded-storage
php artisan vendor:publish --tag=sharded-storage-config   # optional
```

Service Provider und Facade werden per Package-Discovery automatisch registriert.

## Verwendung

```php
use Stormkix\ShardedStorage\Facades\ShardedStorage;

// Upload speichern -> "documents/a3/f9/a3f9....pdf"
$path = ShardedStorage::store($request->file('upload'), 'documents');

// oder per Makro direkt am UploadedFile
$path = $request->file('upload')->storeSharded('documents');

// andere Disk
$path = ShardedStorage::disk('s3')->store($file, 'documents');

// eigenen Dateinamen vorgeben (landet trotzdem im passenden Shard)
$path = ShardedStorage::storeAs($file, 'documents', 'rechnung-2026-001.pdf');

// rohen Inhalt speichern, z. B. generierte PDFs
$path = ShardedStorage::put($pdfBinary, 'pdf', 'reports');
```

**Wichtig:** Den zurückgegebenen Pfad in der Datenbank speichern. Lesen, Löschen,
URLs usw. laufen danach ganz normal über `Storage::disk(...)->get($path)`.

## Konfiguration

| Schlüssel        | Standard  | Bedeutung                                           |
|------------------|-----------|-----------------------------------------------------|
| `disk`           | `null`    | Disk; `null` = Standard-Disk                        |
| `depth`          | `2`       | Anzahl Verzeichnisebenen                            |
| `segment_length` | `2`       | Zeichen pro Ebene                                   |
| `naming`         | `random`  | `random` oder `content` (Deduplizierung per Hash)   |
| `hash_algorithm` | `sha256`  | Algorithmus für `content`                           |

`depth` und `segment_length` sollten nach dem Produktivstart nicht mehr geändert
werden, da bestehende Pfade sonst nicht mehr zur Berechnung passen (unkritisch,
solange die Pfade in der DB gespeichert sind).

## Tests

```bash
composer install
composer test
```
