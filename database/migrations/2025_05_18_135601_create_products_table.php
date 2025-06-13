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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('sku');
            $table->string('name');
            $table->date('expiry_date');
            $table->integer('quantity');
            $table->string('code')->unique()->default(0);
            $table->string('category');
            $table->text('description')->nullable();
            $table->boolean('sent')->default(0);
            $table->boolean('sent2')->default(0);
            $table->boolean('fast')->default(0);
            $table->decimal('unit_price', 10, 2);
            $table->string('image_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
