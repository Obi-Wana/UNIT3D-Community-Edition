<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('donation_transactions', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->unsignedBigInteger('donation_item_id');
            $table->bigInteger('invoice_id')->nullable();
            $table->char('order_id', 20);
            $table->bigInteger('payment_id')->nullable();
            $table->char('currency', 4)->nullable();
            $table->boolean('confirmed')->default(0);
            $table->timestamps();
        });

        Schema::create('donation_subscriptions', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->unsignedBigInteger('donation_item_id');
            $table->boolean('is_active')->default(0);
            $table->boolean('is_gifted')->default(0);
            $table->date('start_at');
            $table->date('end_at');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });

        Schema::create('donation_items', function (Blueprint $table): void {
            $table->id();
            $table->string('type', 20);
            $table->string('name', 40);
            $table->string('description')->nullable();
            $table->bigInteger('seedbonus')->nullable();
            $table->bigInteger('uploaded')->nullable();
            $table->bigInteger('invites')->nullable();
            $table->integer('days_active')->nullable();
            $table->decimal('price_usd', 6, 2);
        });

        Schema::table('donation_transactions', function (Blueprint $table): void {
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('donation_item_id')->references('id')->on('donation_items');
        });

        Schema::table('donation_subscriptions', function (Blueprint $table): void {
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('donation_item_id')->references('id')->on('donation_items');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('donation_transactions');
        Schema::dropIfExists('donation_subscriptions');
        Schema::dropIfExists('donation_items');
    }
};
