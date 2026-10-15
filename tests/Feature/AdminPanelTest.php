<?php

use App\Models\User;

it('lets admins into the panel', function () {
	$this->actingAs(User::factory()->admin()->create())
		->get('/admin/fixtures')
		->assertOk();
});

it('keeps regular fans out of the panel', function () {
	$this->actingAs(User::factory()->create())
		->get('/admin')
		->assertForbidden();
});
