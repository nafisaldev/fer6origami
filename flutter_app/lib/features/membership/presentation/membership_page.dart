import 'package:flutter/material.dart';

import '../../../core/constants/app_colors.dart';
import '../../../shared/widgets/app_content_container.dart';
import '../../../shared/widgets/custom_button.dart';

/// Halaman pricing yang mengikuti resources/views/membership/pricing.blade.php.
class MembershipPage extends StatelessWidget {
  const MembershipPage({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: AppContentContainer(
        includeVerticalPadding: false,
        child: ListView(
          padding: const EdgeInsets.fromLTRB(0, 16, 0, 28),
          children: [
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(22),
              decoration: BoxDecoration(
                color: AppColors.panel,
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: AppColors.border),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text(
                    'MEMBERSHIP',
                    style: TextStyle(
                      fontSize: 11,
                      fontWeight: FontWeight.w800,
                      letterSpacing: 1.5,
                      color: AppColors.brand400,
                    ),
                  ),
                  const SizedBox(height: 8),
                  Text(
                    'Upgrade your viewing experience',
                    style: Theme.of(context).textTheme.headlineSmall,
                  ),
                  const SizedBox(height: 8),
                  Text(
                    'Pilih paket yang sesuai dengan cara kamu menonton. Setiap tier membuka kualitas, badge komentar, dan bingkai profil yang berbeda.',
                    style: Theme.of(context).textTheme.bodyMedium,
                  ),
                ],
              ),
            ),
            const SizedBox(height: 18),
            const _PlanCard(
              name: 'Free',
              price: 'Rp0',
              period: '/selamanya',
              quality: '720p HD',
              accent: AppColors.free,
              features: [
                'Akses katalog anime',
                'Kualitas maksimal 720p',
                'Komentar dengan badge Free',
                'Bingkai profil standar',
              ],
              isCurrent: true,
            ),
            const SizedBox(height: 14),
            const _PlanCard(
              name: 'Premium',
              price: 'Rp25.000',
              period: '/30 hari',
              quality: '1080p Full HD',
              accent: AppColors.premium,
              features: [
                'Semua fitur Free',
                'Kualitas maksimal 1080p',
                'Komentar dengan badge Premium',
                'Bingkai profil merah',
                'Akses episode pilihan lebih awal',
              ],
            ),
            const SizedBox(height: 14),
            const _PlanCard(
              name: 'VIP',
              price: 'Rp65.000',
              period: '/30 hari',
              quality: '4K UHD',
              accent: AppColors.vip,
              features: [
                'Semua fitur Premium',
                'Kualitas maksimal 4K UHD / 2160p',
                'Komentar dengan badge VIP',
                'Bingkai profil emas',
                'Prioritas server dan dukungan',
              ],
            ),
            const SizedBox(height: 22),
            Container(
              padding: const EdgeInsets.all(18),
              decoration: BoxDecoration(
                color: AppColors.card,
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: AppColors.border),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'Perbandingan akses',
                    style: Theme.of(context).textTheme.titleMedium,
                  ),
                  const SizedBox(height: 12),
                  _comparisonRow('Video quality', '720p', '1080p', '4K UHD'),
                  _comparisonRow('Comment badge', 'Free', 'Premium', 'VIP'),
                  _comparisonRow('Profile frame', 'Standar', 'Merah', 'Emas'),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _comparisonRow(String label, String free, String premium, String vip) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 8),
      child: Row(
        children: [
          Expanded(
            flex: 2,
            child: Text(
              label,
              style: const TextStyle(
                fontSize: 12,
                color: AppColors.textSecondary,
              ),
            ),
          ),
          Expanded(
            child: Text(
              free,
              textAlign: TextAlign.center,
              style: const TextStyle(fontSize: 11),
            ),
          ),
          Expanded(
            child: Text(
              premium,
              textAlign: TextAlign.center,
              style: const TextStyle(
                fontSize: 11,
                color: AppColors.premium,
                fontWeight: FontWeight.w700,
              ),
            ),
          ),
          Expanded(
            child: Text(
              vip,
              textAlign: TextAlign.center,
              style: const TextStyle(
                fontSize: 11,
                color: AppColors.vip,
                fontWeight: FontWeight.w700,
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _PlanCard extends StatelessWidget {
  const _PlanCard({
    required this.name,
    required this.price,
    required this.period,
    required this.quality,
    required this.accent,
    required this.features,
    this.isCurrent = false,
  });

  final String name;
  final String price;
  final String period;
  final String quality;
  final Color accent;
  final List<String> features;
  final bool isCurrent;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: AppColors.panel,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(
          color: isCurrent ? accent.withValues(alpha: 0.4) : AppColors.border,
        ),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Text(
                name.toUpperCase(),
                style: const TextStyle(
                  fontSize: 11,
                  fontWeight: FontWeight.w800,
                  letterSpacing: 1.5,
                  color: AppColors.textSecondary,
                ),
              ),
              const Spacer(),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                decoration: BoxDecoration(
                  color: accent.withValues(alpha: 0.14),
                  borderRadius: BorderRadius.circular(6),
                  border: Border.all(color: accent.withValues(alpha: 0.35)),
                ),
                child: Text(
                  quality,
                  style: TextStyle(
                    fontSize: 10,
                    fontWeight: FontWeight.w800,
                    color: accent,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 10),
          Row(
            crossAxisAlignment: CrossAxisAlignment.baseline,
            textBaseline: TextBaseline.alphabetic,
            children: [
              Text(
                price,
                style: TextStyle(
                  fontFamily: 'Syne',
                  fontSize: 24,
                  fontWeight: FontWeight.w800,
                  color: accent,
                ),
              ),
              const SizedBox(width: 4),
              Text(period, style: Theme.of(context).textTheme.bodySmall),
            ],
          ),
          const SizedBox(height: 14),
          ...features.map(
            (feature) => Padding(
              padding: const EdgeInsets.symmetric(vertical: 4),
              child: Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Icon(Icons.check_rounded, size: 15, color: accent),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Text(
                      feature,
                      style: Theme.of(context).textTheme.bodyMedium,
                    ),
                  ),
                ],
              ),
            ),
          ),
          const SizedBox(height: 16),
          SizedBox(
            width: double.infinity,
            child: isCurrent
                ? CustomButton.secondary(
                    label: 'Current plan',
                    onPressed: () {},
                  )
                : CustomButton.primary(label: 'Choose $name', onPressed: () {}),
          ),
        ],
      ),
    );
  }
}
