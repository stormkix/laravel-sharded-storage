<?php

return [

    /*
    | Disk aus config/filesystems.php. null = Standard-Disk der Anwendung.
    */
    'disk' => env('SHARDED_STORAGE_DISK'),

    /*
    | Anzahl der Verzeichnisebenen und Zeichen pro Ebene.
    | depth=2, segment_length=2  ->  ab/cd/abcd1234....pdf
    | (bei Hex-Namen 256 x 256 = 65.536 Verzeichnisse)
    */
    'depth' => 2,
    'segment_length' => 2,

    /*
    | Dateinamen-Strategie:
    |  'random'  -> zufälliger 40-Zeichen-Name (wie Laravels hashName())
    |  'content' -> Hash des Dateiinhalts; identische Dateien werden
    |               nur einmal gespeichert (Deduplizierung)
    */
    'naming' => 'random',

    /*
    | Hash-Algorithmus für 'content'-Naming (siehe hash_algos()).
    */
    'hash_algorithm' => 'sha256',

];
