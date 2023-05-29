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
        Schema::create('blog_users', function (Blueprint $table) {
            $table->id();
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
        Schema::create('blog_posts', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->string('slug')->index();
            $table->string('title');
            $table->text('summary')->nullable();
            $table->mediumText('body')->nullable();
            $table->dateTime('published_at')->nullable()->comment('yayına sunduğu tarih');
            $table->dateTime('approved_at')->nullable()->comment('editör yayınladığı tarih');
            $table->foreignId('approved_by')->nullable()->constrained('blog_users')->nullOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('blog_users')->nullOnDelete();
            $table->string('featured_image')->nullable();
            $table->string('featured_image_caption')->nullable();
            $table->foreignId('blogger_id')->nullable()->constrained('blog_users')->nullOnDelete();
            $table->json('meta')->nullable();
            $table->integer('view_count')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('blog_tags', function (Blueprint $table) {
            $table->id();
            $table->string('slug');
            $table->string('name');
            $table->foreignId('blogger_id')->nullable()->constrained('blog_users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index('created_at');
            $table->unique(['slug', 'blogger_id']);
        });

        Schema::create('blog_topics', function (Blueprint $table) {
            $table->id();
            $table->string('slug');
            $table->string('name');
            $table->string('banner')->nullable();
            $table->foreignId('blogger_id')->nullable()->constrained('blog_users')->nullOnDelete();
            $table->boolean('is_active')->default(false);
            $table->integer('order')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->index('created_at');
            $table->unique(['slug', 'blogger_id']);
        });

        Schema::create('blog_posts_tags', function (Blueprint $table) {
            $table->foreignId('post_id')->nullable()->constrained('blog_posts')->cascadeOnDelete();
            $table->foreignId('tag_id')->nullable()->constrained('blog_tags')->cascadeOnDelete();
            $table->unique(['post_id', 'tag_id']);
        });

        Schema::create('blog_posts_topics', function (Blueprint $table) {
            $table->foreignId('post_id')->nullable()->constrained('blog_posts')->cascadeOnDelete();
            $table->foreignId('topic_id')->nullable()->constrained('blog_topics')->cascadeOnDelete();
            $table->unique(['post_id', 'topic_id']);
        });

        Schema::create('blog_views', function (Blueprint $table) {
            $table->increments('id');
            $table->char('post_id', 36);
            $table->string('ip')->nullable();
            $table->text('agent')->nullable();
            $table->string('referer')->nullable();
            $table->timestamps();
            $table->index('created_at');
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
        Schema::dropIfExists('blog_users');
    }
}
