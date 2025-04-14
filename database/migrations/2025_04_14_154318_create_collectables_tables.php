<?php

declare(strict_types=1);

/**
 * NOTICE OF LICENSE.
 *
 * UNIT3D Community Edition is open-sourced software licensed under the GNU Affero General Public License v3.0
 * The details is bundled with this project in the file LICENSE.txt.
 *
 * @project    UNIT3D Community Edition
 *
 * @author     HDVinnie <hdinnovations@protonmail.com>
 * @license    https://www.gnu.org/licenses/agpl-3.0.en.html/ GNU Affero General Public License v3.0
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('collectible_categories', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('collectibles', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('category_id')->references('id')->on('collectible_categories');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            // $table->unsignedInteger('total_amount')->nullable(); // Tracked by amount of records per item in collectible_items
            $table->boolean('resell')->default(false);
            $table->float('price');
            $table->timestamps();
        });

        Schema::create('collectible_requirements', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('collectible_id')->references('id')->on('collectibles');
            $table->unsignedBigInteger('min_uploaded')->nullable();
            $table->unsignedBigInteger('min_seedsize')->nullable();
            $table->unsignedBigInteger('min_avg_seedtime')->nullable();
            $table->decimal('min_ratio', 4, 2)->nullable();
            $table->unsignedBigInteger('min_age')->nullable();
            $table->timestamps();
        });

        Schema::create('collectible_items', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('collectible_id')->references('id')->on('collectibles');
            $table->unsignedInteger('user_id')->nullable()->references('id')->on('users');
            $table->boolean('active')->default(false);
            $table->timestamps();
        });

        Schema::create('collectible_offers', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('collectible_id')->references('id')->on('collectibles');
            $table->unsignedInteger('user_id')->references('id')->on('users');
            $table->float('price');
            $table->dateTime('filled_when')->nullable();
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
        });

        Schema::create('collectible_transactions', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('collectible_id')->references('id')->on('collectible_items');
            $table->unsignedInteger('seller_id')->references('id')->on('users');
            $table->unsignedInteger('buyer_id')->references('id')->on('users');
            $table->float('price');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collectible_transactions');

        Schema::dropIfExists('collectible_offers');

        Schema::dropIfExists('collectible_items');

        Schema::dropIfExists('collectible_requirements');

        Schema::dropIfExists('collectibles');

        Schema::dropIfExists('collectible_categories');
    }
};
