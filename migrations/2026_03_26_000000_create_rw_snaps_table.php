<?php

use Flarum\Database\Migration;
use Illuminate\Database\Schema\Blueprint;

return Migration::createTable('rw_snaps', function (Blueprint $table) {
    $table->bigIncrements('id');
    $table->unsignedInteger('user_id');
    $table->smallInteger('year');
    $table->json('data');
    $table->timestamp('generated_at');
    $table->boolean('is_public')->default(true);

    $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
    $table->unique(['user_id', 'year']);
});
