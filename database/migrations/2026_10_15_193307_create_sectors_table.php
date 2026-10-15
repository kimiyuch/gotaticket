<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		Schema::create('sectors', function (Blueprint $table) {
			$table->id();
			$table->string('code')->nullable();
			$table->string('name');
			$table->string('kind');
			$table->unsignedSmallInteger('sort')->default(0);
			$table->boolean('is_active')->default(true);
			$table->timestamps();
		});

		Schema::create('sector_user', function (Blueprint $table) {
			$table->foreignId('sector_id')->constrained()->cascadeOnDelete();
			$table->foreignId('user_id')->constrained()->cascadeOnDelete();
			$table->primary(['sector_id', 'user_id']);
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('sector_user');
		Schema::dropIfExists('sectors');
	}
};
