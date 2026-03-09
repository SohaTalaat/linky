<?php

namespace Tests\Feature;

use App\Jobs\FetchLinkMetadataJob;
use App\Models\Link;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MetadataJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_metadata_job_updates_link(): void
    {
        $user = User::factory()->create();

        $link = Link::factory()->for($user)->create([
            'url' => 'https://example.com/page',
            'title' => null,
        ]);

        Http::preventStrayRequests();

        Http::fake([
            '*' => Http::response(
                '<html>
                    <head>
                      <title>Example Title</title>
                      <meta name="description" content="Example Description">
                      <meta property="og:image" content="/og.png">
                      <meta property="og:site_name" content="ExampleSite">
                      <link rel="icon" href="/favicon.ico">
                    </head>
                    <body>Hi</body>
                 </html>',
                200,
                ['Content-Type' => 'text/html; charset=UTF-8']
            ),
        ]);

        (new FetchLinkMetadataJob($link->id))->handle();

        Http::assertSent(fn($req) => str_contains($req->url(), 'example.com'));

        $link->refresh();
        $this->assertSame('Example Title', $link->title);
    }
}
