<?php

use App\Models\User;

it('gives the public pages a description, canonical URL and link preview', function (string $route) {
	$this->get(route($route))
		->assertOk()
		->assertSee('<meta name="robots" content="index, follow">', escape: false)
		->assertSee('<link rel="canonical" href="'.route($route).'">', escape: false)
		->assertSee('<meta property="og:image" content="'.asset('images/og.png').'">', escape: false)
		->assertSee('<meta name="twitter:card" content="summary_large_image">', escape: false);

	expect(public_path('images/og.png'))->toBeFile();
})->with(['home', 'offers.index']);

it('keeps private pages out of search results', function () {
	$this->get(route('login'))
		->assertSee('<title>Einloggen · Got a ticket?</title>', escape: false)
		->assertSee('<meta name="robots" content="noindex, nofollow">', escape: false);

	$this->actingAs(User::factory()->create())
		->get(route('dashboard'))
		->assertSee('<meta name="robots" content="noindex, nofollow">', escape: false);
});

it('serves robots.txt and a sitemap with the public pages', function () {
	$this->get('/robots.txt')->assertOk()->assertSee('Sitemap: '.route('sitemap'));

	$this->get('/sitemap.xml')
		->assertOk()
		->assertHeader('Content-Type', 'application/xml')
		->assertSee(route('offers.index'));
});

it('only serves the image source page during local development', function () {
	$this->get('/og-image')->assertNotFound();
});
