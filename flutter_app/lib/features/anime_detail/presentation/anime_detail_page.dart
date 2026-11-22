import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../core/constants/app_colors.dart';
import '../../../shared/models/anime.dart';
import '../../../shared/widgets/app_content_container.dart';
import '../../../shared/widgets/app_error_state.dart';
import '../../../shared/widgets/custom_button.dart';
import '../../../shared/widgets/empty_state.dart';
import '../../../shared/widgets/loading_skeleton.dart';
import '../../bookmarks/controllers/bookmarks_controller.dart';
import '../controllers/anime_detail_controller.dart';

class AnimeDetailPage extends ConsumerWidget {
  const AnimeDetailPage({super.key, required this.animeId});

  final String animeId;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final detail = ref.watch(animeDetailProvider(animeId));
    final bookmarks = ref.watch(bookmarksControllerProvider);

    return Scaffold(
      appBar: AppBar(
        leading: const BackButton(),
        title: const Text('Anime Details'),
        actions: [
          IconButton(
            onPressed: () => context.go('/search'),
            icon: const Icon(Icons.search_rounded, size: 20),
          ),
        ],
      ),
      body: AppContentContainer(
        includeVerticalPadding: false,
        child: detail.when(
          data: (anime) {
            if (anime == null) {
              return const EmptyState(
                title: 'Anime tidak ditemukan',
                message: 'Detail anime belum tersedia untuk item ini.',
              );
            }

            final isBookmarked = bookmarks.contains(anime.id);

            return ListView(
              padding: const EdgeInsets.fromLTRB(0, 12, 0, 28),
              children: [
                _Breadcrumb(title: anime.title),
                const SizedBox(height: 14),
                _DetailHero(
                  anime: anime,
                  isBookmarked: isBookmarked,
                  onBookmarkTap: () => ref
                      .read(bookmarksControllerProvider.notifier)
                      .toggle(anime.id),
                ),
                const SizedBox(height: 24),
                _DetailSection(
                  kicker: 'Synopsis',
                  title: 'About This Anime',
                  child: Text(
                    anime.synopsis,
                    style: Theme.of(context).textTheme.bodyMedium,
                  ),
                ),
                const SizedBox(height: 24),
                _DetailSection(
                  kicker: 'Genres',
                  title: 'Categories',
                  child: Wrap(
                    spacing: 8,
                    runSpacing: 8,
                    children: anime.genres
                        .map(
                          (genre) => Container(
                            padding: const EdgeInsets.symmetric(
                              horizontal: 10,
                              vertical: 6,
                            ),
                            decoration: BoxDecoration(
                              color: AppColors.card,
                              borderRadius: BorderRadius.circular(6),
                              border: Border.all(color: AppColors.border),
                            ),
                            child: Text(
                              genre,
                              style: const TextStyle(
                                fontSize: 11,
                                fontWeight: FontWeight.w600,
                              ),
                            ),
                          ),
                        )
                        .toList(),
                  ),
                ),
                const SizedBox(height: 24),
                _DetailSection(
                  kicker: 'Episodes',
                  title: 'Episode Guide',
                  child: Column(
                    children: anime.episodes
                        .map((episode) => _EpisodeTile(episode: episode))
                        .toList(),
                  ),
                ),
              ],
            );
          },
          loading: () => const _DetailLoading(),
          error: (_, __) => AppErrorState(
            title: 'Detail belum tersedia',
            message: 'Terjadi masalah saat memuat detail anime.',
            onRetry: () => ref.invalidate(animeDetailProvider(animeId)),
          ),
        ),
      ),
    );
  }
}

class _Breadcrumb extends StatelessWidget {
  const _Breadcrumb({required this.title});

  final String title;

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        GestureDetector(
          onTap: () => context.go('/'),
          child: const Text(
            'Home',
            style: TextStyle(fontSize: 11, color: AppColors.textSecondary),
          ),
        ),
        const Text(
          ' / ',
          style: TextStyle(fontSize: 11, color: AppColors.textSecondary),
        ),
        GestureDetector(
          onTap: () => context.go('/catalog'),
          child: const Text(
            'Catalog',
            style: TextStyle(fontSize: 11, color: AppColors.textSecondary),
          ),
        ),
        const Text(
          ' / ',
          style: TextStyle(fontSize: 11, color: AppColors.textSecondary),
        ),
        Expanded(
          child: Text(
            title,
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
            style: const TextStyle(fontSize: 11, color: Color(0xFFE2E8F0)),
          ),
        ),
      ],
    );
  }
}

class _DetailHero extends StatelessWidget {
  const _DetailHero({
    required this.anime,
    required this.isBookmarked,
    required this.onBookmarkTap,
  });

  final Anime anime;
  final bool isBookmarked;
  final VoidCallback onBookmarkTap;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: AppColors.panel,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.border),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              ClipRRect(
                borderRadius: BorderRadius.circular(10),
                child: CachedNetworkImage(
                  imageUrl: anime.posterUrl,
                  width: 112,
                  height: 150,
                  fit: BoxFit.cover,
                  errorWidget: (_, __, ___) => Container(
                    width: 112,
                    height: 150,
                    color: AppColors.card,
                    alignment: Alignment.center,
                    child: const Text(
                      'Fer6origami',
                      style: TextStyle(
                        color: AppColors.brand400,
                        fontSize: 11,
                        fontWeight: FontWeight.w800,
                      ),
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
                      anime.title,
                      style: Theme.of(context).textTheme.titleLarge,
                    ),
                    const SizedBox(height: 6),
                    Text(
                      anime.headline,
                      style: Theme.of(context).textTheme.bodySmall,
                    ),
                    const SizedBox(height: 10),
                    Wrap(
                      spacing: 6,
                      runSpacing: 6,
                      children: [
                        _InfoChip(
                          label: '${(anime.score).toStringAsFixed(1)} rating',
                        ),
                        _InfoChip(label: anime.status),
                        _InfoChip(label: anime.episodeInfo),
                        _InfoChip(label: '${anime.year}'),
                      ],
                    ),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 18),
          Row(
            children: [
              Expanded(
                child: CustomButton.primary(
                  label: (anime.watchProgress) > 0
                      ? 'Lanjut nonton'
                      : 'Watch Now',
                  onPressed: () {},
                  icon: Icons.play_arrow_rounded,
                ),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: CustomButton.secondary(
                  label: isBookmarked ? 'Saved' : 'Add to Watchlist',
                  onPressed: onBookmarkTap,
                  icon: isBookmarked
                      ? Icons.bookmark_rounded
                      : Icons.bookmark_add_outlined,
                ),
              ),
            ],
          ),
          if ((anime.watchProgress) > 0) ...[
            const SizedBox(height: 16),
            Row(
              children: [
                const Text(
                  'Progress',
                  style: TextStyle(
                    fontSize: 11,
                    color: AppColors.textSecondary,
                  ),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: ClipRRect(
                    borderRadius: BorderRadius.circular(999),
                    child: LinearProgressIndicator(
                      value: anime.watchProgress,
                      minHeight: 6,
                      backgroundColor: const Color(0x14FFFFFF),
                      valueColor: const AlwaysStoppedAnimation<Color>(
                        AppColors.brand,
                      ),
                    ),
                  ),
                ),
              ],
            ),
          ],
        ],
      ),
    );
  }
}

class _InfoChip extends StatelessWidget {
  const _InfoChip({required this.label});

  final String label;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 5),
      decoration: BoxDecoration(
        color: AppColors.card,
        borderRadius: BorderRadius.circular(6),
        border: Border.all(color: AppColors.border),
      ),
      child: Text(
        label,
        style: const TextStyle(fontSize: 10, fontWeight: FontWeight.w700),
      ),
    );
  }
}

class _DetailSection extends StatelessWidget {
  const _DetailSection({
    required this.kicker,
    required this.title,
    required this.child,
  });

  final String kicker;
  final String title;
  final Widget child;

  @override
  Widget build(BuildContext context) {
    return Column(
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
        Text(title, style: Theme.of(context).textTheme.titleLarge),
        const SizedBox(height: 12),
        child,
      ],
    );
  }
}

class _EpisodeTile extends StatelessWidget {
  const _EpisodeTile({required this.episode});

  final Episode episode;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 10),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
        decoration: BoxDecoration(
          color: AppColors.panel,
          borderRadius: BorderRadius.circular(10),
          border: Border.all(color: AppColors.border),
        ),
        child: Row(
          children: [
            Container(
              width: 34,
              height: 34,
              decoration: BoxDecoration(
                color: const Color(0x14E53535),
                borderRadius: BorderRadius.circular(8),
              ),
              alignment: Alignment.center,
              child: Text(
                '${episode.number}',
                style: const TextStyle(
                  fontSize: 12,
                  fontWeight: FontWeight.w800,
                  color: AppColors.brand400,
                ),
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    episode.title,
                    style: const TextStyle(
                      fontSize: 12,
                      fontWeight: FontWeight.w700,
                    ),
                  ),
                  const SizedBox(height: 3),
                  Text(
                    '${episode.duration} • 720p ready',
                    style: const TextStyle(
                      fontSize: 10,
                      color: AppColors.textSecondary,
                    ),
                  ),
                ],
              ),
            ),
            const Icon(
              Icons.play_circle_outline_rounded,
              size: 20,
              color: AppColors.textSecondary,
            ),
          ],
        ),
      ),
    );
  }
}

class _DetailLoading extends StatelessWidget {
  const _DetailLoading();

  @override
  Widget build(BuildContext context) {
    return ListView(
      padding: const EdgeInsets.fromLTRB(0, 12, 0, 24),
      children: const [
        LoadingSkeleton(height: 180, radius: 16),
        SizedBox(height: 20),
        LoadingSkeleton(height: 24, width: 200),
        SizedBox(height: 12),
        LoadingSkeleton(height: 80),
        SizedBox(height: 12),
        LoadingSkeleton(height: 56, radius: 10),
      ],
    );
  }
}
