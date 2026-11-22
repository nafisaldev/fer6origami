<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Roles & Permissions (RBAC)
        if (!Schema::hasTable('roles')) {
            Schema::create('roles', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('description')->nullable();
                $table->boolean('is_system')->default(false);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('permissions')) {
            Schema::create('permissions', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('module');
                $table->string('description')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('permission_role')) {
            Schema::create('permission_role', function (Blueprint $table) {
                $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
                $table->foreignId('role_id')->constrained()->cascadeOnDelete();
                $table->primary(['permission_id', 'role_id']);
            });
        }

        // Add user management columns to users table
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'role_id')) {
                $table->foreignId('role_id')->nullable()->after('role')->constrained('roles')->nullOnDelete();
            }
            if (!Schema::hasColumn('users', 'status')) {
                $table->string('status')->default('active')->after('role'); // active, suspended, banned
            }
            if (!Schema::hasColumn('users', 'banned_reason')) {
                $table->string('banned_reason')->nullable()->after('status');
            }
            if (!Schema::hasColumn('users', 'banned_until')) {
                $table->timestamp('banned_until')->nullable()->after('banned_reason');
            }
            if (!Schema::hasColumn('users', 'avatar_path')) {
                $table->string('avatar_path')->nullable()->after('banned_until');
            }
            if (!Schema::hasColumn('users', 'last_login_at')) {
                $table->timestamp('last_login_at')->nullable()->after('avatar_path');
            }
            if (!Schema::hasColumn('users', 'last_login_ip')) {
                $table->string('last_login_ip', 45)->nullable()->after('last_login_at');
            }
        });

        // 2. Studios
        if (!Schema::hasTable('studios')) {
            Schema::create('studios', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->string('logo_path')->nullable();
                $table->string('country')->nullable();
                $table->unsignedSmallInteger('founded_year')->nullable();
                $table->string('website')->nullable();
                $table->timestamps();
            });
        }

        // Add studio_id & SEO & featured columns to animes table
        Schema::table('animes', function (Blueprint $table) {
            if (!Schema::hasColumn('animes', 'studio_id')) {
                $table->foreignId('studio_id')->nullable()->after('studio')->constrained('studios')->nullOnDelete();
            }
            if (!Schema::hasColumn('animes', 'is_featured')) {
                $table->boolean('is_featured')->default(false)->after('is_popular');
            }
            if (!Schema::hasColumn('animes', 'release_day')) {
                $table->string('release_day', 20)->nullable()->after('status'); // monday, tuesday, etc.
            }
            if (!Schema::hasColumn('animes', 'meta_title')) {
                $table->string('meta_title')->nullable()->after('is_featured');
            }
            if (!Schema::hasColumn('animes', 'meta_description')) {
                $table->text('meta_description')->nullable()->after('meta_title');
            }
            if (!Schema::hasColumn('animes', 'meta_keywords')) {
                $table->string('meta_keywords')->nullable()->after('meta_description');
            }
        });

        // Add servers & subtitle options to episodes table
        Schema::table('episodes', function (Blueprint $table) {
            if (!Schema::hasColumn('episodes', 'servers')) {
                $table->json('servers')->nullable()->after('video_url');
            }
            if (!Schema::hasColumn('episodes', 'subtitles')) {
                $table->json('subtitles')->nullable()->after('servers');
            }
            if (!Schema::hasColumn('episodes', 'qualities')) {
                $table->json('qualities')->nullable()->after('subtitles');
            }
        });

        // 3. Announcements
        if (!Schema::hasTable('announcements')) {
            Schema::create('announcements', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->string('slug')->unique();
                $table->longText('content');
                $table->string('category', 50)->default('Information'); // Information, Update, Maintenance, Event, Important, New Release
                $table->string('banner_path')->nullable();
                $table->string('status', 30)->default('published'); // draft, published, scheduled, expired
                $table->timestamp('published_at')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->boolean('is_pinned')->default(false);
                $table->boolean('is_popup')->default(false);
                $table->unsignedBigInteger('reads_count')->default(0);
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        // 4. Notifications
        if (!Schema::hasTable('notifications_broadcast')) {
            Schema::create('notifications_broadcast', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->text('message');
                $table->string('type', 50)->default('announcement'); // announcement, episode, maintenance, system, role
                $table->string('target_role', 50)->nullable(); // null for all, or specific role slug
                $table->string('action_url')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('user_notification_reads')) {
            Schema::create('user_notification_reads', function (Blueprint $table) {
                $table->id();
                $table->foreignId('notification_id')->constrained('notifications_broadcast')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->timestamp('read_at')->useCurrent();
                $table->unique(['notification_id', 'user_id']);
            });
        }

        // 5. Comments & Reviews
        if (!Schema::hasTable('comments')) {
            Schema::create('comments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('anime_id')->nullable()->constrained('animes')->cascadeOnDelete();
                $table->foreignId('episode_id')->nullable()->constrained('episodes')->cascadeOnDelete();
                $table->foreignId('parent_id')->nullable()->constrained('comments')->cascadeOnDelete();
                $table->text('content');
                $table->string('status', 30)->default('published'); // published, hidden, spam
                $table->unsignedInteger('reports_count')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('reviews')) {
            Schema::create('reviews', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('anime_id')->constrained('animes')->cascadeOnDelete();
                $table->unsignedTinyInteger('rating')->default(8); // 1-10
                $table->string('title')->nullable();
                $table->text('content')->nullable();
                $table->boolean('is_recommended')->default(true);
                $table->string('status', 30)->default('published'); // published, hidden
                $table->timestamps();
                $table->unique(['user_id', 'anime_id']);
            });
        }

        // 6. Reports
        if (!Schema::hasTable('reports')) {
            Schema::create('reports', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('reportable_type')->nullable(); // Anime, Episode, Comment
                $table->unsignedBigInteger('reportable_id')->nullable();
                $table->string('type', 50)->default('broken_link'); // broken_link, subtitle_issue, inappropriate_content, comment_report, other
                $table->text('description');
                $table->string('status', 30)->default('pending'); // pending, in_progress, resolved, rejected
                $table->text('admin_notes')->nullable();
                $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('resolved_at')->nullable();
                $table->timestamps();
            });
        }

        // 7. Watch History & Analytics
        if (!Schema::hasTable('watch_histories')) {
            Schema::create('watch_histories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
                $table->foreignId('anime_id')->constrained('animes')->cascadeOnDelete();
                $table->foreignId('episode_id')->nullable()->constrained('episodes')->cascadeOnDelete();
                $table->string('ip_address', 45)->nullable();
                $table->unsignedInteger('duration_seconds')->default(0);
                $table->boolean('is_completed')->default(false);
                $table->timestamps();
                $table->index(['created_at', 'anime_id']);
                $table->index('user_id');
            });
        }

        // 8. Website Settings & Custom Pages
        if (!Schema::hasTable('website_settings')) {
            Schema::create('website_settings', function (Blueprint $table) {
                $table->id();
                $table->string('group', 50)->default('general'); // general, seo, appearance, homepage, security
                $table->string('key')->unique();
                $table->longText('value')->nullable();
                $table->string('type', 30)->default('text'); // text, textarea, boolean, json, image
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('custom_pages')) {
            Schema::create('custom_pages', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->string('slug')->unique();
                $table->longText('content');
                $table->boolean('is_published')->default(true);
                $table->string('meta_title')->nullable();
                $table->text('meta_description')->nullable();
                $table->timestamps();
            });
        }

        // 9. Activity Logs
        if (!Schema::hasTable('activity_logs')) {
            Schema::create('activity_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('module', 50); // Anime, Episode, User, Role, Settings, System, etc.
                $table->string('action', 50); // created, updated, deleted, login, logout, etc.
                $table->string('description');
                $table->json('changes')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->string('user_agent')->nullable();
                $table->timestamps();
                $table->index(['module', 'created_at']);
                $table->index('user_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('custom_pages');
        Schema::dropIfExists('website_settings');
        Schema::dropIfExists('watch_histories');
        Schema::dropIfExists('reports');
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('comments');
        Schema::dropIfExists('user_notification_reads');
        Schema::dropIfExists('notifications_broadcast');
        Schema::dropIfExists('announcements');
        Schema::dropIfExists('studios');
        Schema::dropIfExists('permission_role');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
    }
};
