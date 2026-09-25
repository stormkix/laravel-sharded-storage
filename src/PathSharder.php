<?php

namespace Stormkix\ShardedStorage;

use InvalidArgumentException;

/**
 * Reine Pfadlogik ohne Abhängigkeit zum Dateisystem – leicht testbar.
 */
final class PathSharder
{
    public function __construct(
        private readonly int $depth = 2,
        private readonly int $segmentLength = 2,
    ) {
        if ($depth < 0) {
            throw new InvalidArgumentException('depth muss >= 0 sein.');
        }

        if ($segmentLength < 1) {
            throw new InvalidArgumentException('segment_length muss >= 1 sein.');
        }
    }

    /**
     * Liefert den Shard-Teil des Pfads, z. B. "a3/f9".
     *
     * Ist der Dateiname selbst schon ein ausreichend langer alphanumerischer
     * Hash, werden dessen erste Zeichen genutzt. Andernfalls (z. B. bei
     * "Mein Vertrag.pdf") wird ein Hash des Namens berechnet, damit die
     * Verteilung trotzdem gleichmäßig und reproduzierbar ist.
     */
    public function shard(string $filename): string
    {
        if ($this->depth === 0) {
            return '';
        }

        $needed = $this->depth * $this->segmentLength;
        $key = strtolower(pathinfo($filename, PATHINFO_FILENAME));

        if (strlen($key) < $needed || ! ctype_alnum($key)) {
            $key = hash('sha256', $filename);
        }

        return implode('/', str_split(substr($key, 0, $needed), $this->segmentLength));
    }

    /**
     * Vollständiger relativer Pfad, z. B. "documents/a3/f9/a3f9....pdf".
     */
    public function path(string $directory, string $filename): string
    {
        $parts = [trim($directory, '/'), $this->shard($filename), $filename];

        return implode('/', array_filter($parts, fn (string $part) => $part !== ''));
    }
}
