<?php

namespace App\Jobs;

use App\Models\Link;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FetchLinkMetadataJob implements ShouldQueue
{
    use Queueable, Dispatchable, InteractsWithQueue, SerializesModels;

    public int $tries = 3;
    public int $timeout = 15;
    public int $backoff = 10;

    /**
     * Create a new job instance.
     */
    public function __construct(public int $linkId)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $link = Link::find($this->linkId);

        if (!$link) {
            return;
        }

        $url = $link->url;

        Log::info('JOB BEFORE HTTP', ['url' => $url, 'link_id' => $this->linkId]);

        $response = Http::timeout(10)
            ->withHeaders([
                'User-Agent' => 'LinksAppBot/1.0 (+https://example.com)',
                'Accept' => 'text/html,application/xhtml+xml',
            ])
            ->get($url);

        if (! $response->successful()) {
            $response->throw();
        }

        $html = (string) $response->body();

        $meta = $this->parseHtmlMetadata($html, $url);

        $link->fill([
            'title' => $meta['title'] ?: $link->title,
            'description' => $meta['description'] ?: $link->description,
            'image' => $meta['image'] ?: $link->image,
            'site_name' => $meta['site_name'] ?: $link->site_name,
            'favicon' => $meta['favicon'] ?: $link->favicon
        ]);

        $link->save();
    }

    private function parseHtmlMetadata(string $html, string $url): array
    {
        libxml_use_internal_errors(true);

        $dom = new \DOMDocument();
        $dom->loadHTML($html);

        $xpath = new \DOMXPath($dom);

        $title = $this->firstNodeValue($xpath, '//title');
        $description = $this->metaContent($xpath, 'name', 'description')
            ?: $this->metaContent($xpath, 'property', 'og:description');

        $image = $this->metaContent($xpath, 'property', 'og:image')
            ?: $this->metaContent($xpath, 'name', 'twitter:image');

        $siteName = $this->metaContent($xpath, 'property', 'og:site_name')
            ?: parse_url($url, PHP_URL_HOST);

        $favicon = $this->extractFavicon($xpath, $url);

        $image = $this->resolveUrl($image, $url);
        $favicon = $this->resolveUrl($favicon, $url);

        return [
            'title' => $this->clean($title),
            'description' => $this->clean($description),
            'image' => $image,
            'site_name' => $this->clean($siteName),
            'favicon' => $favicon
        ];
    }

    private function metaContent(\DOMXPath $xpath, string $attr, string $value): ?string
    {
        $nodes = $xpath->query("//meta[@{$attr}='{$value}']/@content");
        if ($nodes && $nodes->length > 0) {
            return (string) $nodes->item(0)->nodeValue;
        }

        return null;
    }

    private function firstNodeValue(\DOMXPath $xpath, string $query): ?string
    {
        $nodes = $xpath->query($query);
        if ($nodes && $nodes->length > 0) {
            return (string) $nodes->item(0)->textContent;
        }
        return null;
    }

    private function extractFavicon(\DOMXPath $xpath, string $url): ?string
    {
        $rels = [
            'icon',
            'shortcut icon',
            'apple-touch-icon',
            'apple-touch-icon-precomposed'
        ];

        foreach ($rels as $rel) {
            $nodes = $xpath->query("//link[translate(@rel,'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz')='{$rel}']/@href");
            if ($nodes && $nodes->length > 0) {
                return (string) $nodes->item(0)->nodeValue;
            }
        }

        // Fallback
        $host = parse_url($url, PHP_URL_HOST);
        $scheme = parse_url($url, PHP_URL_SCHEME) ?: 'https';

        return $host ? "{$scheme}://{$host}/favicon.ico" : null;
    }

    private function resolveUrl(?string $maybeUrl, string $baseUrl): ?string
    {
        if (! $maybeUrl) return null;

        if (preg_match('#^https?://#i', $maybeUrl)) {
            return $maybeUrl;
        }

        // Protocol-relative //example.com/...
        if (str_starts_with($maybeUrl, '//')) {
            $scheme = parse_url($baseUrl, PHP_URL_SCHEME) ?: 'https';
            return $scheme . ':' . $maybeUrl;
        }

        // Absolute path /img.png
        if (str_starts_with($maybeUrl, '/')) {
            $scheme = parse_url($baseUrl, PHP_URL_SCHEME) ?: 'https';
            $host = parse_url($baseUrl, PHP_URL_HOST);
            return $host ? "{$scheme}://{$host}{$maybeUrl}" : null;
        }

        // Relative path img.png
        $parts = parse_url($baseUrl);
        if (! $parts || empty($parts['scheme']) || empty($parts['host'])) return null;

        $path = $parts['path'] ?? '/';
        $dir = rtrim(substr($path, 0, strrpos($path, '/') ?: 0), '/');
        $dir = $dir ? $dir : '';

        return "{$parts['scheme']}://{$parts['host']}{$dir}/{$maybeUrl}";
    }

    private function clean(?string $text): ?string
    {
        if (! $text) return null;
        $text = trim(preg_replace('/\s+/', ' ', $text));
        return $text !== '' ? $text : null;
    }
}
