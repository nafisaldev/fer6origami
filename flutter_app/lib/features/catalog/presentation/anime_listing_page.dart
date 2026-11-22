import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../core/constants/app_colors.dart';
import '../../../shared/models/anime.dart';
import '../../../shared/widgets/app_content_container.dart';
import '../../../shared/widgets/app_error_state.dart';
import '../../../shared/widgets/empty_state.dart';
import '../../../shared/widgets/loading_skeleton.dart';
import '../../home/controllers/home_controller.dart';

/// Halaman katalog/ongoing/completed yang memakai satu sumber data dengan Home.
class AnimeListingPage extends ConsumerWidget {
  const AnimeListingPage({
    super.key,
    required this.kicker,
    required this.title,
    required this.subtitle,
    required this.status,
    required this.routePath,
    this.genre,
  });

  final String kicker;
  final String title;
  final String subtitle;
  final String status;
  final String routePath;
  final String? genre;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final state = ref.watch(homeControllerProvider);

    return Scaffold(
      body: AppContentContainer(
        includeVerticalPadding: false,
        child: state.when(
          data: (data) {
            final all = _combine(data);
            final filtered = all.where((anime) {
              final matchesStatus =
                  status == 'all' ||
                  anime.status.toLowerCase() == status.toLowerCase();
              final matchesGenre =
                  genre == null ||
                  anime.genres.any(
                    (item) => item.toLowerCase() == genre!.toLowerCase(),
                  );
              return matchesStatus && matchesGenre;
            }).toList();

            return RefreshIndicator(
              color: AppColors.brand,
              onRefresh: () async => ref.invalidate(homeControllerProvider),
              child: ListView(
                physics: const AlwaysScrollableScrollPhysics(),
                padding: const EdgeInsets.fromLTRB(0, 16, 0, 24),
                children: [
                  _CatalogHeader(
                    kicker: kicker,
                    title: title,
                    subtitle: subtitle,
                    count: filtered.length,
                  ),
                  const SizedBox(height: 16),
                  _FilterRow(
                    routePath: routePath,
                    status: status,
                    genre: genre,
                  ),
                  const SizedBox(height: 16),
                  if (filtered.isEmpty)
                    const SizedBox(
                      height: 360,
                      child: EmptyState(
                        title: 'No anime found',
                        message:
                            'Coba ubah filter status atau genre untuk melihat judul lain.',
                        icon: Icons.search_off_rounded,
                      ),
                    )
                  else
                    ResponsiveAnimeGrid(
                      items: filtered,
                      maxColumns: 6,
                      shrinkWrap: true,
                      physics: const NeverScrollableScrollPhysics(),
                    ),
                ],
              ),
            );
          },
          loading: () => ListView(
            padding: const EdgeInsets.symmetric(vertical: 16),
            children: const [
              LoadingSkeleton(height: 108, radius: 12),
              SizedBox(height: 16),
              LoadingSkeleton(height: 36, radius: 999),
              SizedBox(height: 16),
              LoadingSkeleton(height: 260, radius: 12),
            ],
          ),
          error: (_, __) => AppErrorState(
            title: 'Catalog belum bisa dimuat',
            message: 'Terjadi masalah saat menyiapkan daftar anime. Coba lagi.',
            onRetry: () => ref.invalidate(homeControllerProvider),
          ),
        ),
      ),
    );
  }

  List<Anime> _combine(HomeViewData data) {
    final seen = <String>{};
    return [
      ...data.trending,
      ...data.popular,
      ...data.latest,
      ...data.recommended,
      ...data.featured,
    ].where((anime) => seen.add(anime.id)).toList();
  }
}

class _CatalogHeader extends StatelessWidget {
  const _CatalogHeader({
    required this.kicker,
    required this.title,
    required this.subtitle,
    required this.count,
  });

  final String kicker;
  final String title;
  final String subtitle;
  final int count;

  @override
  Widget build(BuildContext context) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: AppColors.panel,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: AppColors.border),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            kicker.toUpperCase(),
            style: const TextStyle(
              fontSize: 11,
              fontWeight: FontWeight.w800,
              letterSpacing: 1.5,
              color: AppColors.brand400,
            ),
          ),
          const SizedBox(height: 6),
          Text(title, style: Theme.of(context).textTheme.headlineSmall),
          const SizedBox(height: 6),
          Text(subtitle, style: Theme.of(context).textTheme.bodySmall),
          const SizedBox(height: 10),
          Text(
            'Showing $count titles from the Fer6origami catalog.',
            style: const TextStyle(
              fontSize: 11,
              color: AppColors.textSecondary,
            ),
          ),
        ],
      ),
    );
  }
}

class _FilterRow extends StatelessWidget {
  const _FilterRow({
    required this.routePath,
    required this.status,
    required this.genre,
  });

  final String routePath;
  final String status;
  final String? genre;

  @override
  Widget build(BuildContext context) {
    return SingleChildScrollView(
      scrollDirection: Axis.horizontal,
      child: Row(
        children: [
          _FilterChip(
            label: 'All',
            active: status == 'all',
            onTap: () => context.go('/catalog'),
          ),
          const SizedBox(width: 8),
          _FilterChip(
            label: 'Ongoing',
            active: status == 'ongoing',
            onTap: () => context.go('/ongoing'),
          ),
          const SizedBox(width: 8),
          _FilterChip(
            label: 'Completed',
            active: status == 'completed',
            onTap: () => context.go('/completed'),
          ),
          if (genre != null) ...[
            const SizedBox(width: 8),
            _FilterChip(
              label: genre!,
              active: true,
              onTap: () => context.go(routePath),
            ),
          ],
        ],
      ),
    );
  }
}

class _FilterChip extends StatelessWidget {
  const _FilterChip({
    required this.label,
    required this.active,
    required this.onTap,
  });

  final String label;
  final bool active;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(8),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
        decoration: BoxDecoration(
          color: active ? AppColors.brand : AppColors.card,
          borderRadius: BorderRadius.circular(8),
          border: Border.all(
            color: active ? AppColors.brand : AppColors.border,
          ),
        ),
        child: Text(
          label,
          style: TextStyle(
            fontSize: 11,
            fontWeight: FontWeight.w700,
            color: active ? Colors.white : AppColors.textSecondary,
          ),
        ),
      ),
    );
  }
}
