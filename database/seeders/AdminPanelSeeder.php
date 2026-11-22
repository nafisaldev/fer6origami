<?php

namespace Database\Seeders;

use App\Models\CustomPage;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Studio;
use App\Models\User;
use App\Models\WebsiteSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AdminPanelSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Roles
        $roles = [
            'super_admin' => [
                'name' => 'Super Admin',
                'description' => 'Akses penuh ke seluruh sistem tanpa batasan.',
                'is_system' => true,
            ],
            'admin' => [
                'name' => 'Admin',
                'description' => 'Mengelola konten, user, moderasi, dan konfigurasi umum.',
                'is_system' => true,
            ],
            'anime_manager' => [
                'name' => 'Anime Manager',
                'description' => 'Fokus mengelola anime, episode, genre, studio, dan jadwal rilis.',
                'is_system' => false,
            ],
            'content_editor' => [
                'name' => 'Content Editor',
                'description' => 'Mengelola konten anime, episode, pengumuman, dan halaman website.',
                'is_system' => false,
            ],
            'moderator' => [
                'name' => 'Moderator',
                'description' => 'Memoderasi komentar, review, laporan pengguna, dan tindakan suspend.',
                'is_system' => false,
            ],
            'user' => [
                'name' => 'User',
                'description' => 'Pengguna terdaftar umum penikmat streaming anime.',
                'is_system' => true,
            ],
        ];

        $roleModels = [];
        foreach ($roles as $slug => $data) {
            $roleModels[$slug] = Role::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'is_system' => $data['is_system'],
                ]
            );
        }

        // 2. Permissions by Module
        $modules = [
            'overview' => [
                'overview.dashboard' => 'Melihat Dashboard Utama',
                'overview.analytics' => 'Melihat Analytics & Statistik',
                'overview.export' => 'Export Laporan Statistik',
            ],
            'anime' => [
                'anime.view' => 'Melihat Daftar Anime',
                'anime.create' => 'Menambah Anime Baru',
                'anime.edit' => 'Mengedit Data Anime',
                'anime.delete' => 'Menghapus Anime',
                'anime.bulk' => 'Bulk Action Anime',
            ],
            'episodes' => [
                'episodes.view' => 'Melihat Daftar Episode',
                'episodes.create' => 'Menambah Episode Baru',
                'episodes.edit' => 'Mengedit Episode',
                'episodes.delete' => 'Menghapus Episode',
            ],
            'genres' => [
                'genres.view' => 'Melihat Daftar Genre',
                'genres.manage' => 'CRUD Genre Anime',
            ],
            'studios' => [
                'studios.view' => 'Melihat Daftar Studio',
                'studios.manage' => 'CRUD Studio Animasi',
            ],
            'schedules' => [
                'schedules.manage' => 'Mengatur Jadwal Rilis Mingguan',
            ],
            'users' => [
                'users.view' => 'Melihat Seluruh Pengguna',
                'users.edit' => 'Mengedit Profil Pengguna',
                'users.suspend' => 'Suspend & Ban Pengguna',
                'users.delete' => 'Menghapus Pengguna',
                'users.roles' => 'Mengubah Role Pengguna',
            ],
            'roles' => [
                'roles.view' => 'Melihat Daftar Role & Permission',
                'roles.manage' => 'CRUD Role & Assign Permissions',
            ],
            'comments' => [
                'comments.view' => 'Melihat Seluruh Komentar',
                'comments.moderate' => 'Edit, Sembunyikan, & Hapus Komentar',
            ],
            'reviews' => [
                'reviews.view' => 'Melihat Review Anime',
                'reviews.moderate' => 'Moderasi Review & Rating',
            ],
            'reports' => [
                'reports.view' => 'Melihat Laporan Pengguna',
                'reports.manage' => 'Menangani & Menyelesaikan Laporan',
            ],
            'announcements' => [
                'announcements.view' => 'Melihat Daftar Pengumuman',
                'announcements.manage' => 'CRUD Pengumuman & Jadwal',
            ],
            'notifications' => [
                'notifications.view' => 'Melihat Riwayat Notifikasi',
                'notifications.send' => 'Kirim Notifikasi Broadcast',
            ],
            'appearance' => [
                'appearance.homepage' => 'Mengatur Hero Banner & Sections',
                'appearance.theme' => 'Mengatur Tema, Logo, & Navigasi',
            ],
            'pages' => [
                'pages.manage' => 'CRUD Halaman Statis (About, Contact, dll)',
            ],
            'seo' => [
                'seo.manage' => 'Konfigurasi SEO, Meta, Sitemap, & Robots',
            ],
            'membership' => [
                'membership.view' => 'Melihat Data Membership',
                'membership.manage' => 'Mengubah Paket & Status Membership',
                'membership.plans' => 'Mengelola Paket dan Harga',
                'membership.vouchers' => 'Mengelola Voucher',
                'membership.payments' => 'Memverifikasi Pembayaran Manual',
                'membership.history' => 'Melihat Riwayat Membership',
            ],
            'system' => [
                'system.logs' => 'Melihat Activity Logs',
                'system.monitoring' => 'Melihat Server & Database Status',
                'system.cache' => 'Mengelola Cache Sistem',
                'system.backup' => 'Backup & Restore Database',
                'system.security' => 'Mengatur Security Settings',
                'system.general' => 'Mengatur General Settings',
            ],
        ];

        $permissionIds = [];
        foreach ($modules as $module => $perms) {
            foreach ($perms as $slug => $name) {
                $permission = Permission::updateOrCreate(
                    ['slug' => $slug],
                    [
                        'name' => $name,
                        'module' => $module,
                        'description' => "Permission untuk modul {$module}: {$name}",
                    ]
                );
                $permissionIds[$slug] = $permission->id;
            }
        }

        // Attach permissions to roles
        // Super Admin gets all permissions
        $roleModels['super_admin']->permissions()->sync(array_values($permissionIds));

        // Admin gets most permissions except managing super_admin roles directly
        $adminPerms = array_values($permissionIds);
        $roleModels['admin']->permissions()->sync($adminPerms);

        // Anime Manager permissions
        $animeManagerPermSlugs = [
            'overview.dashboard',
            'anime.view', 'anime.create', 'anime.edit', 'anime.delete', 'anime.bulk',
            'episodes.view', 'episodes.create', 'episodes.edit', 'episodes.delete',
            'genres.view', 'genres.manage',
            'studios.view', 'studios.manage',
            'schedules.manage',
        ];
        $animeManagerIds = array_intersect_key($permissionIds, array_flip($animeManagerPermSlugs));
        $roleModels['anime_manager']->permissions()->sync(array_values($animeManagerIds));

        // Content Editor permissions
        $contentEditorPermSlugs = [
            'overview.dashboard',
            'anime.view', 'anime.create', 'anime.edit',
            'episodes.view', 'episodes.create', 'episodes.edit',
            'announcements.view', 'announcements.manage',
            'pages.manage',
        ];
        $contentEditorIds = array_intersect_key($permissionIds, array_flip($contentEditorPermSlugs));
        $roleModels['content_editor']->permissions()->sync(array_values($contentEditorIds));

        // Moderator permissions
        $moderatorPermSlugs = [
            'overview.dashboard',
            'comments.view', 'comments.moderate',
            'reviews.view', 'reviews.moderate',
            'reports.view', 'reports.manage',
            'users.view', 'users.suspend',
        ];
        $moderatorIds = array_intersect_key($permissionIds, array_flip($moderatorPermSlugs));
        $roleModels['moderator']->permissions()->sync(array_values($moderatorIds));

        // 3. Assign User 1 to Super Admin
        $user1 = User::find(1);
        if ($user1) {
            $user1->update([
                'role' => 'super_admin',
                'role_id' => $roleModels['super_admin']->id,
                'status' => 'active',
            ]);
        }

        $user2 = User::find(2);
        if ($user2) {
            $user2->update([
                'role' => 'admin',
                'role_id' => $roleModels['admin']->id,
                'status' => 'active',
            ]);
        }

        // 4. Seed Default Studios
        $studios = [
            ['name' => 'MAPPA', 'country' => 'Japan', 'founded_year' => 2011, 'website' => 'http://www.mappa.co.jp'],
            ['name' => 'Ufotable', 'country' => 'Japan', 'founded_year' => 2000, 'website' => 'http://www.ufotable.com'],
            ['name' => 'Wit Studio', 'country' => 'Japan', 'founded_year' => 2012, 'website' => 'https://witstudio.co.jp'],
            ['name' => 'CloverWorks', 'country' => 'Japan', 'founded_year' => 2018, 'website' => 'https://cloverworks.co.jp'],
            ['name' => 'A-1 Pictures', 'country' => 'Japan', 'founded_year' => 2005, 'website' => 'https://a1p.jp'],
            ['name' => 'Bones', 'country' => 'Japan', 'founded_year' => 1998, 'website' => 'https://bones.co.jp'],
            ['name' => 'Kyoto Animation', 'country' => 'Japan', 'founded_year' => 1981, 'website' => 'https://kyotoanimation.co.jp'],
            ['name' => 'Madhouse', 'country' => 'Japan', 'founded_year' => 1972, 'website' => 'https://madhouse.co.jp'],
        ];

        foreach ($studios as $s) {
            Studio::updateOrCreate(
                ['slug' => Str::slug($s['name'])],
                [
                    'name' => $s['name'],
                    'country' => $s['country'],
                    'founded_year' => $s['founded_year'],
                    'website' => $s['website'],
                    'description' => "Studio animasi ternama {$s['name']} yang memproduksi serial anime populer kelas dunia.",
                ]
            );
        }

        // 5. Seed Default Settings
        $defaultSettings = [
            // General
            ['group' => 'general', 'key' => 'site_name', 'value' => 'Fer6origami', 'type' => 'text'],
            ['group' => 'general', 'key' => 'site_tagline', 'value' => 'Ultimate Anime Streaming Platform', 'type' => 'text'],
            ['group' => 'general', 'key' => 'site_email', 'value' => 'support@fer6origami.com', 'type' => 'text'],
            ['group' => 'general', 'key' => 'allow_registration', 'value' => '1', 'type' => 'boolean'],
            ['group' => 'general', 'key' => 'default_role', 'value' => 'user', 'type' => 'text'],

            // SEO
            ['group' => 'seo', 'key' => 'meta_title', 'value' => 'Fer6origami — Nonton Anime Streaming Subtitle Indonesia HD', 'type' => 'text'],
            ['group' => 'seo', 'key' => 'meta_description', 'value' => 'Platform streaming anime terlengkap dengan kualitas HD, server tercepat, dan update harian tanpa gangguan.', 'type' => 'textarea'],
            ['group' => 'seo', 'key' => 'meta_keywords', 'value' => 'nonton anime, streaming anime, download anime, anime sub indo, anime hd, fer6origami', 'type' => 'text'],
            ['group' => 'seo', 'key' => 'canonical_url', 'value' => 'https://fer6origami.com', 'type' => 'text'],
            ['group' => 'seo', 'key' => 'robots_txt', 'value' => "User-agent: *\nAllow: /\nDisallow: /admin/\nDisallow: /api/\nSitemap: https://fer6origami.com/sitemap.xml", 'type' => 'textarea'],

            // Appearance & Homepage
            ['group' => 'appearance', 'key' => 'primary_color', 'value' => '#e53535', 'type' => 'text'],
            ['group' => 'appearance', 'key' => 'accent_color', 'value' => '#f97316', 'type' => 'text'],
            ['group' => 'appearance', 'key' => 'footer_text', 'value' => '© 2026 Fer6origami. All rights reserved. Designed for true anime enthusiasts.', 'type' => 'textarea'],
            ['group' => 'homepage', 'key' => 'hero_style', 'value' => 'carousel', 'type' => 'text'],
            ['group' => 'homepage', 'key' => 'show_trending', 'value' => '1', 'type' => 'boolean'],
            ['group' => 'homepage', 'key' => 'show_ongoing', 'value' => '1', 'type' => 'boolean'],
            ['group' => 'homepage', 'key' => 'show_completed', 'value' => '1', 'type' => 'boolean'],
            ['group' => 'homepage', 'key' => 'show_schedule', 'value' => '1', 'type' => 'boolean'],

            // Security
            ['group' => 'security', 'key' => 'maintenance_mode', 'value' => '0', 'type' => 'boolean'],
            ['group' => 'security', 'key' => 'maintenance_message', 'value' => 'Website sedang dalam pemeliharaan berkala untuk peningkatan kualitas server. Kami akan segera kembali.', 'type' => 'textarea'],
            ['group' => 'security', 'key' => 'max_login_attempts', 'value' => '5', 'type' => 'text'],
            ['group' => 'security', 'key' => 'lockout_minutes', 'value' => '15', 'type' => 'text'],
        ];

        foreach ($defaultSettings as $setting) {
            WebsiteSetting::updateOrCreate(
                ['key' => $setting['key']],
                [
                    'group' => $setting['group'],
                    'value' => $setting['value'],
                    'type' => $setting['type'],
                ]
            );
        }

        // 6. Seed Default Custom Pages
        $pages = [
            [
                'title' => 'About Fer6origami',
                'slug' => 'about',
                'content' => "<h1>Tentang Fer6origami</h1><p>Fer6origami adalah destinasi utama untuk menikmati tayangan anime terbaik dengan kualitas visual jernih, navigasi responsif, dan kurasi katalog terdepan. Kami berkomitmen menyajikan pengalaman streaming yang mulus bagi seluruh komunitas anime.</p>",
                'is_published' => true,
                'meta_title' => 'About Us — Fer6origami',
                'meta_description' => 'Pelajari lebih lanjut tentang visi, misi, dan tim di balik Fer6origami Anime Streaming.',
            ],
            [
                'title' => 'Contact Support',
                'slug' => 'contact',
                'content' => "<h1>Hubungi Kami</h1><p>Ada pertanyaan, masukan, atau laporan masalah? Hubungi kami melalui email di support@fer6origami.com atau gunakan fitur laporan langsung di halaman anime.</p>",
                'is_published' => true,
                'meta_title' => 'Contact Support — Fer6origami',
                'meta_description' => 'Hubungi tim dukungan Fer6origami untuk bantuan teknis dan pertanyaan.',
            ],
            [
                'title' => 'Terms of Service & DMCA',
                'slug' => 'terms',
                'content' => "<h1>Syarat & Ketentuan Layanan</h1><p>Fer6origami menghormati hak cipta intelektual para pembuat karya. Seluruh konten media streaming disajikan melalui embed dan link publik yang tersedia secara bebas di internet.</p>",
                'is_published' => true,
                'meta_title' => 'Terms of Service — Fer6origami',
                'meta_description' => 'Ketentuan penggunaan dan kebijakan hak cipta DMCA di Fer6origami.',
            ],
        ];

        foreach ($pages as $p) {
            CustomPage::updateOrCreate(
                ['slug' => $p['slug']],
                $p
            );
        }
    }
}
