<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubdomainsCloudflareConfigTable extends Migration
{
    public function up()
    {
        Schema::create('subdomains_cloudflare_config', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->string('api_key');
            $table->boolean('proxy_records');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('subdomains_cloudflare_config');
    }
}