<?php

use Flarum\Database\Migration;
use Illuminate\Database\Schema\Blueprint;

return Migration::createTable('rw_community', function (Blueprint $table) {
    $table->bigIncrements('id');
    $table->smallInteger('year')->unique();
    $table->json('data');
    $table->timestamp('generated_at');
});
