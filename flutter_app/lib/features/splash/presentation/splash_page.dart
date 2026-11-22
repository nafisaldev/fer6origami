import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../core/constants/app_colors.dart';
import '../../settings/controllers/settings_controller.dart';

class SplashPage extends ConsumerStatefulWidget {
  const SplashPage({super.key});

  @override
  ConsumerState<SplashPage> createState() => _SplashPageState();
}

class _SplashPageState extends ConsumerState<SplashPage> {
  @override
  void initState() {
    super.initState();
    Future<void>.delayed(const Duration(milliseconds: 2200), _proceed);
  }

  void _proceed() {
    if (!mounted) {
      return;
    }

    final settings = ref.read(settingsControllerProvider);
    context.go(settings.didFinishOnboarding ? '/' : '/onboarding');
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      body: Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            ClipRRect(
                  borderRadius: BorderRadius.circular(20),
                  child: Image.asset(
                    'assets/images/emblem.png',
                    width: 104,
                    height: 104,
                    fit: BoxFit.cover,
                    errorBuilder: (_, __, ___) => Container(
                      width: 104,
                      height: 104,
                      color: AppColors.card,
                      alignment: Alignment.center,
                      child: const Text(
                        'F6',
                        style: TextStyle(
                          fontSize: 34,
                          fontWeight: FontWeight.w800,
                          color: AppColors.brand400,
                        ),
                      ),
                    ),
                  ),
                )
                .animate()
                .fadeIn(duration: 600.ms)
                .scale(
                  begin: const Offset(0.9, 0.9),
                  end: const Offset(1, 1),
                  duration: 800.ms,
                ),
            const SizedBox(height: 24),
            RichText(
              text: const TextSpan(
                style: TextStyle(
                  fontFamily: 'Syne',
                  fontSize: 30,
                  fontWeight: FontWeight.w800,
                  letterSpacing: -0.6,
                ),
                children: [
                  TextSpan(
                    text: 'Fer6',
                    style: TextStyle(color: Colors.white),
                  ),
                  TextSpan(
                    text: 'origami',
                    style: TextStyle(color: AppColors.brand400),
                  ),
                ],
              ),
            ).animate().fadeIn(delay: 250.ms, duration: 600.ms),
            const SizedBox(height: 10),
            const Text(
              'Anime Streaming & Catalog Platform',
              style: TextStyle(fontSize: 12, color: AppColors.textSecondary),
            ).animate().fadeIn(delay: 450.ms, duration: 600.ms),
            const SizedBox(height: 40),
            const SizedBox(
              width: 22,
              height: 22,
              child: CircularProgressIndicator(strokeWidth: 2),
            ),
          ],
        ),
      ),
    );
  }
}
