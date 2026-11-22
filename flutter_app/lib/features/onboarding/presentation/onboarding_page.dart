import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../core/constants/app_colors.dart';
import '../../../shared/widgets/custom_button.dart';
import '../../settings/controllers/settings_controller.dart';

class OnboardingPage extends ConsumerStatefulWidget {
  const OnboardingPage({super.key});

  @override
  ConsumerState<OnboardingPage> createState() => _OnboardingPageState();
}

class _OnboardingPageState extends ConsumerState<OnboardingPage> {
  late final PageController _pageController;
  int _page = 0;

  static const _items = [
    (
      title: 'Cinematic anime experience',
      message:
          'Hero spotlight, kurasi rapi, dan navigasi cepat seperti pengalaman di website Fer6origami.',
      icon: Icons.play_circle_outline_rounded,
      color: AppColors.brand,
    ),
    (
      title: 'Catalog, ongoing, and watchlist',
      message:
          'Jelajahi katalog lengkap, ikuti episode ongoing, dan simpan anime favoritmu dalam satu tempat.',
      icon: Icons.view_list_rounded,
      color: AppColors.tide400,
    ),
    (
      title: 'Membership with real benefits',
      message:
          'Free, Premium, dan VIP menghadirkan kualitas 720p, 1080p, dan 4K UHD serta badge komentar berbeda.',
      icon: Icons.workspace_premium_rounded,
      color: AppColors.warning,
    ),
  ];

  @override
  void initState() {
    super.initState();
    _pageController = PageController();
  }

  @override
  void dispose() {
    _pageController.dispose();
    super.dispose();
  }

  Future<void> _finish() async {
    await ref.read(settingsControllerProvider.notifier).completeOnboarding();
    if (mounted) {
      context.go('/');
    }
  }

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    return Scaffold(
      backgroundColor: AppColors.background,
      body: SafeArea(
        child: Padding(
          padding: const EdgeInsets.fromLTRB(20, 16, 20, 22),
          child: Column(
            children: [
              Row(
                children: [
                  const _BrandMark(),
                  const Spacer(),
                  TextButton(onPressed: _finish, child: const Text('Lewati')),
                ],
              ),
              const SizedBox(height: 12),
              Expanded(
                child: PageView.builder(
                  controller: _pageController,
                  itemCount: _items.length,
                  onPageChanged: (value) => setState(() => _page = value),
                  itemBuilder: (context, index) {
                    final item = _items[index];
                    return Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Expanded(
                          child: Container(
                            width: double.infinity,
                            padding: const EdgeInsets.all(24),
                            decoration: BoxDecoration(
                              color: AppColors.panel,
                              borderRadius: BorderRadius.circular(16),
                              border: Border.all(color: AppColors.borderStrong),
                            ),
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Container(
                                  width: 64,
                                  height: 64,
                                  decoration: BoxDecoration(
                                    borderRadius: BorderRadius.circular(12),
                                    color: item.color.withValues(alpha: 0.16),
                                    border: Border.all(
                                      color: item.color.withValues(alpha: 0.4),
                                    ),
                                  ),
                                  alignment: Alignment.center,
                                  child: Icon(
                                    item.icon,
                                    size: 30,
                                    color: item.color,
                                  ),
                                ),
                                const Spacer(),
                                Text(
                                  '0${index + 1}',
                                  style: textTheme.displayLarge?.copyWith(
                                    color: const Color(0x22FFFFFF),
                                  ),
                                ),
                                const SizedBox(height: 10),
                                Text(item.title, style: textTheme.displaySmall),
                                const SizedBox(height: 12),
                                Text(item.message, style: textTheme.bodyMedium),
                              ],
                            ),
                          ),
                        ),
                        const SizedBox(height: 24),
                        Row(
                          children: List.generate(
                            _items.length,
                            (index) => AnimatedContainer(
                              duration: const Duration(milliseconds: 260),
                              width: _page == index ? 28 : 10,
                              height: 10,
                              margin: const EdgeInsets.only(right: 8),
                              decoration: BoxDecoration(
                                borderRadius: BorderRadius.circular(999),
                                color: _page == index
                                    ? AppColors.brand
                                    : const Color(0x22FFFFFF),
                              ),
                            ),
                          ),
                        ),
                      ],
                    );
                  },
                ),
              ),
              const SizedBox(height: 18),
              CustomButton.primary(
                label: _page == _items.length - 1 ? 'Mulai sekarang' : 'Lanjut',
                onPressed: () async {
                  if (_page == _items.length - 1) {
                    await _finish();
                    return;
                  }

                  await _pageController.nextPage(
                    duration: const Duration(milliseconds: 320),
                    curve: Curves.easeOutCubic,
                  );
                },
                icon: Icons.arrow_forward_rounded,
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _BrandMark extends StatelessWidget {
  const _BrandMark();

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 9),
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(10),
        color: AppColors.panel,
        border: Border.all(color: AppColors.borderStrong),
      ),
      child: const Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(
            Icons.local_fire_department_rounded,
            color: AppColors.brand,
            size: 18,
          ),
          SizedBox(width: 8),
          Text(
            'Fer6origami',
            style: TextStyle(
              fontFamily: 'Syne',
              fontWeight: FontWeight.w800,
              fontSize: 14,
            ),
          ),
        ],
      ),
    );
  }
}
