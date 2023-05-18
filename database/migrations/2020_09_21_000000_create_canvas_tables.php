<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCanvasTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('blog_posts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('slug');
            $table->string('title');
            $table->text('summary')->nullable();
            $table->text('body')->nullable();
            $table->dateTime('published_at')->nullable()->comment('yayına sunduğu tarih');
            $table->dateTime('approved_at')->nullable()->comment('editör yayınladığı tarih');
            $table->uuid('approved_by')->nullable()->index();
            $table->string('featured_image')->nullable();
            $table->string('featured_image_caption')->nullable();
            $table->uuid('user_id')->index();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['slug', 'user_id']);
        });

        Schema::create('blog_tags', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('slug');
            $table->string('name');
            $table->uuid('user_id')->index();
            $table->timestamps();
            $table->softDeletes();
            $table->index('created_at');
            $table->unique(['slug', 'user_id']);
        });

        Schema::create('blog_topics', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('slug');
            $table->string('name');
            $table->uuid('user_id')->index();
            $table->timestamps();
            $table->softDeletes();
            $table->index('created_at');
            $table->unique(['slug', 'user_id']);
        });

        Schema::create('blog_posts_tags', function (Blueprint $table) {
            $table->uuid('post_id');
            $table->uuid('tag_id');
            $table->unique(['post_id', 'tag_id']);
        });

        Schema::create('blog_posts_topics', function (Blueprint $table) {
            $table->uuid('post_id');
            $table->uuid('topic_id');
            $table->unique(['post_id', 'topic_id']);
        });

        Schema::create('blog_views', function (Blueprint $table) {
            $table->increments('id');
            $table->uuid('post_id')->index();
            $table->string('ip')->nullable();
            $table->text('agent')->nullable();
            $table->string('referer')->nullable();
            $table->timestamps();
            $table->index('created_at');
        });

        Schema::create('blog_visits', function (Blueprint $table) {
            $table->increments('id');
            $table->uuid('post_id');
            $table->string('ip')->nullable();
            $table->text('agent')->nullable();
            $table->string('referer')->nullable();
            $table->timestamps();
        });

        Schema::create('blog_users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('username')->unique()->nullable();
            $table->string('password');
            $table->text('summary')->nullable();
            $table->string('avatar')->nullable();
            $table->tinyInteger('dark_mode')->nullable();
            $table->tinyInteger('digest')->nullable();
            $table->string('locale')->nullable();
            $table->tinyInteger('role')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('blog_posts');
        Schema::dropIfExists('blog_tags');
        Schema::dropIfExists('blog_topics');
        Schema::dropIfExists('blog_posts_tags');
        Schema::dropIfExists('blog_posts_topics');
        Schema::dropIfExists('blog_views');
        Schema::dropIfExists('blog_visits');
        Schema::dropIfExists('blog_users');
    }
}
