<?php

namespace App\Support;

use Illuminate\Support\Str;
use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

/** Menyaring HTML dari CKEditor. Editor bisa dilewati, jadi hanya server yang bisa dipercaya. */
class KontenArtikel
{
    public const MAKS_PANJANG = 200000;

    /** Hanya format yang tersedia di toolbar editor yang boleh tersimpan. */
    private const ELEMEN = [
        'p' => ['class'],
        'h2' => ['class'],
        'h3' => ['class'],
        'h4' => ['class'],
        'strong' => [],
        'b' => [],
        'i' => [],
        'em' => [],
        'u' => [],
        's' => [],
        'br' => [],
        'hr' => [],
        'ul' => [],
        'ol' => [],
        'li' => [],
        'blockquote' => [],
        'a' => ['href', 'target', 'rel'],
        'figure' => ['class'],
        'figcaption' => [],
        'img' => ['src', 'alt'],
        'table' => [],
        'thead' => [],
        'tbody' => [],
        'tr' => [],
        'th' => ['colspan', 'rowspan'],
        'td' => ['colspan', 'rowspan'],
    ];

    /** Tag lain dari teks yang ditempel dibuang, tetapi tulisannya tetap ada. */
    private const DILEPAS = ['div', 'span', 'font', 'h1', 'h5', 'h6', 'section', 'article', 'header', 'footer', 'main'];

    /** Tanpa daftar ini, class bebas bisa menimpa tampilan halaman, misalnya "fixed inset-0". */
    private const KELAS = ['image', 'image-style-side', 'table', 'ta-left', 'ta-center', 'ta-right', 'ta-justify'];

    public static function bersihkan(?string $html): string
    {
        $config = (new HtmlSanitizerConfig())
            ->allowLinkSchemes(['http', 'https', 'mailto', 'tel'])
            ->allowMediaSchemes(['http', 'https'])
            ->forceAttribute('a', 'rel', 'noopener noreferrer')
            ->withMaxInputLength(self::MAKS_PANJANG);

        foreach (self::ELEMEN as $elemen => $atribut) {
            $config = $config->allowElement($elemen, $atribut);
        }

        foreach (self::DILEPAS as $elemen) {
            $config = $config->blockElement($elemen);
        }

        $bersih = (new HtmlSanitizer($config))->sanitize((string) $html);

        return preg_replace_callback('/ class="([^"]*)"/', function ($cocok) {
            $kelas = array_intersect(explode(' ', html_entity_decode($cocok[1])), self::KELAS);

            return $kelas ? ' class="' . implode(' ', $kelas) . '"' : '';
        }, $bersih);
    }

    public static function kosong(?string $html): bool
    {
        $teks = str_replace(['&nbsp;', "\u{00A0}"], ' ', strip_tags((string) $html, '<img><table><hr>'));

        return trim($teks) === '';
    }

    /** Cuplikan teks biasa untuk daftar artikel. */
    public static function ringkasan(?string $html, int $panjang = 140): string
    {
        // Akhir blok diberi spasi supaya kata dari dua paragraf tidak menyambung.
        $teks = preg_replace('#(</(?:p|h2|h3|h4|li|blockquote|figcaption|td|th)>|<br\s*/?>|<(?:ul|ol)>)#', ' $1 ', (string) $html);
        $teks = html_entity_decode(strip_tags($teks), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return Str::limit(Str::squish(str_replace("\u{00A0}", ' ', $teks)), $panjang, '...');
    }

    /** Path gambar di storage yang diunggah lewat editor. */
    public static function gambar(?string $html): array
    {
        preg_match_all('/<img[^>]+src="([^"]+)"/', (string) $html, $cocok);

        return collect($cocok[1])
            ->map(fn ($src) => FileUpload::path(html_entity_decode($src)))
            ->filter(fn ($path) => $path && Str::startsWith($path, 'artikel/isi/'))
            ->unique()
            ->values()
            ->all();
    }
}
