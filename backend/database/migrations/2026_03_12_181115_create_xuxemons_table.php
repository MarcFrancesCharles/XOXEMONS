<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
 
    public function up() {
        Schema::create('xuxemons', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['Aigua', 'Terra', 'Aire']);
            $table->enum('size', ['Petit', 'Mitjà', 'Gran']);
            $table->string('image')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('xuxemons');
    }
};
