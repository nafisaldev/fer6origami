import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../core/constants/app_colors.dart';
import '../../../shared/widgets/app_content_container.dart';
import '../../../shared/widgets/section_title.dart';
import '../../settings/controllers/settings_controller.dart';

class ProfilePage extends ConsumerWidget {
  const ProfilePage({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final settings = ref.watch(settingsControllerProvider);
    final initial = settings.profileName.characters.first.toUpperCase();

    return Scaffold(
      body: AppContentContainer(
        includeVerticalPadding: false,
        child: ListView(
          padding: const EdgeInsets.fromLTRB(0, 16, 0, 28),
          children: [
            Container(
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(
                color: AppColors.panel,
                borderRadius: BorderRadius.circular(16),
                border: Border.all(color: AppColors.borderStrong),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.all(3),
                        decoration: BoxDecoration(
                          borderRadius: BorderRadius.circular(14),
                          border: Border.all(
                            color: AppColors.brand400,
                            width: 2,
                          ),
                        ),
                        child: CircleAvatar(
                          radius: 34,
                          backgroundColor: AppColors.card,
                          child: Text(
                            initial,
                            style: const TextStyle(
                              fontSize: 26,
                              fontWeight: FontWeight.w800,
                              color: Colors.white,
                            ),
                          ),
                        ),
                      ),
                      const SizedBox(width: 14),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              settings.profileName,
                              style: Theme.of(context).textTheme.titleLarge,
                            ),
                            const SizedBox(height: 4),
                            const Text(
                              'Novice Otaku • Level 1',
                              style: TextStyle(
                                fontSize: 11,
                                color: AppColors.textSecondary,
                              ),
                            ),
                            const SizedBox(height: 8),
                            Container(
                              padding: const EdgeInsets.symmetric(
                                horizontal: 8,
                                vertical: 4,
                              ),
                              decoration: BoxDecoration(
                                color: const Color(0x14FFFFFF),
                                borderRadius: BorderRadius.circular(6),
                                border: Border.all(
                                  color: AppColors.borderStrong,
                                ),
                              ),
                              child: const Text(
                                'FREE MEMBER',
                                style: TextStyle(
                                  fontSize: 10,
                                  fontWeight: FontWeight.w800,
                                  letterSpacing: 0.6,
                                  color: AppColors.free,
                                ),
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 20),
                  Row(
                    children: [
                      const Expanded(
                        child: Text(
                          'LEVEL PROGRESS',
                          style: TextStyle(
                            fontSize: 10,
                            fontWeight: FontWeight.w800,
                            letterSpacing: 1.4,
                            color: AppColors.textSecondary,
                          ),
                        ),
                      ),
                      Text(
                        '120 / 500 XP',
                        style: Theme.of(context).textTheme.bodySmall,
                      ),
                    ],
                  ),
                  const SizedBox(height: 8),
                  ClipRRect(
                    borderRadius: BorderRadius.circular(999),
                    child: LinearProgressIndicator(
                      value: 0.24,
                      minHeight: 7,
                      backgroundColor: const Color(0x14FFFFFF),
                      valueColor: const AlwaysStoppedAnimation<Color>(
                        AppColors.brand,
                      ),
                    ),
                  ),
                  const SizedBox(height: 18),
                  Row(
                    children: const [
                      _Stat(label: 'Watchlist', value: '12'),
                      SizedBox(width: 8),
                      _Stat(label: 'Reviews', value: '3'),
                      SizedBox(width: 8),
                      _Stat(label: 'Quests', value: '2/5'),
                    ],
                  ),
                ],
              ),
            ),
            const SizedBox(height: 24),
            const SectionTitle(
              kicker: 'Account',
              title: 'Pengaturan Akun',
              subtitle: 'Kelola identitas dan preferensi tampilan aplikasi.',
            ),
            const SizedBox(height: 14),
            Card(
              child: Column(
                children: [
                  SwitchListTile(
                    value: settings.themeMode != ThemeMode.light,
                    onChanged: (value) => ref
                        .read(settingsControllerProvider.notifier)
                        .toggleTheme(value),
                    title: const Text('Dark mode'),
                    subtitle: const Text(
                      'Gunakan tampilan gelap sebagai mode utama.',
                    ),
                  ),
                  const Divider(height: 1),
                  ListTile(
                    title: const Text('Edit profile name'),
                    subtitle: const Text('Ganti nama tampilan'),
                    trailing: const Icon(Icons.chevron_right_rounded, size: 18),
                    onTap: () =>
                        _showNameDialog(context, ref, settings.profileName),
                  ),
                  const Divider(height: 1),
                  ListTile(
                    title: const Text('Membership'),
                    subtitle: const Text('Lihat paket Free, Premium, dan VIP'),
                    trailing: const Icon(Icons.chevron_right_rounded, size: 18),
                    onTap: () => context.go('/membership'),
                  ),
                  const Divider(height: 1),
                  ListTile(
                    title: const Text('My Watchlist'),
                    subtitle: const Text('Lihat anime yang kamu simpan'),
                    trailing: const Icon(Icons.chevron_right_rounded, size: 18),
                    onTap: () => context.go('/bookmarks'),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 24),
            const SectionTitle(
              kicker: 'Activity',
              title: 'Aktivitas Terbaru',
              subtitle: 'Ringkasan aktivitas menonton dan komunitas.',
            ),
            const SizedBox(height: 14),
            Card(
              child: Column(
                children: const [
                  ListTile(
                    leading: Icon(
                      Icons.play_circle_outline_rounded,
                      size: 20,
                      color: AppColors.brand400,
                    ),
                    title: Text('Melanjutkan menonton'),
                    subtitle: Text('Celestial Breaker • Episode 9'),
                  ),
                  Divider(height: 1),
                  ListTile(
                    leading: Icon(
                      Icons.bookmark_add_outlined,
                      size: 20,
                      color: AppColors.brand400,
                    ),
                    title: Text('Anime tersimpan'),
                    subtitle: Text('Velvet Eclipse masuk watchlist'),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 20),
            const Text(
              'Fer6origami version 1.0.0',
              textAlign: TextAlign.center,
              style: TextStyle(fontSize: 11, color: AppColors.textSecondary),
            ),
          ],
        ),
      ),
    );
  }

  Future<void> _showNameDialog(
    BuildContext context,
    WidgetRef ref,
    String currentName,
  ) async {
    final controller = TextEditingController(text: currentName);

    await showDialog<void>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Edit profile name'),
        content: TextField(
          controller: controller,
          decoration: const InputDecoration(hintText: 'Masukkan nama'),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(),
            child: const Text('Batal'),
          ),
          FilledButton(
            onPressed: () {
              final nextName = controller.text.trim().isEmpty
                  ? 'Anime Explorer'
                  : controller.text.trim();
              ref
                  .read(settingsControllerProvider.notifier)
                  .updateProfileName(nextName);
              Navigator.of(context).pop();
            },
            child: const Text('Simpan'),
          ),
        ],
      ),
    );
  }
}

class _Stat extends StatelessWidget {
  const _Stat({required this.label, required this.value});

  final String label;
  final String value;

  @override
  Widget build(BuildContext context) {
    return Expanded(
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 12),
        decoration: BoxDecoration(
          color: AppColors.card,
          borderRadius: BorderRadius.circular(10),
          border: Border.all(color: AppColors.border),
        ),
        child: Column(
          children: [
            Text(
              value,
              style: const TextStyle(
                fontFamily: 'Syne',
                fontSize: 17,
                fontWeight: FontWeight.w800,
                color: Colors.white,
              ),
            ),
            const SizedBox(height: 4),
            Text(label, style: Theme.of(context).textTheme.bodySmall),
          ],
        ),
      ),
    );
  }
}
