import 'dart:async';

import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../core/constants/app_colors.dart';
import '../../../shared/models/anime.dart';
import '../../../shared/widgets/anime_card.dart';
import '../../../shared/widgets/app_content_container.dart';
import '../../../shared/widgets/app_error_state.dart';
import '../../../shared/widgets/loading_skeleton.dart';
import '../../../shared/widgets/section_title.dart';
import '../controllers/home_controller.dart';

class HomePage extends ConsumerWidget {
  const HomePage({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final state = ref.watch(homeControllerProvider);

    return Scaffold(
      body: AppContentContainer(
        includeVerticalPadding: false,
        child: state.when(
          data: (data) => RefreshIndicator(
            color: AppColors.brand,
            onRefresh: () async => ref.invalidate(homeControllerProvider),
            child: CustomScrollView(
              physics: const BouncingScrollPhysics(
                parent: AlwaysScrollableScrollPhysics(),
              ),
              slivers: [
                SliverToBoxAdapter(
                  child: Padding(
                    padding: const EdgeInsets.only(top: 16),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        _HeroCarousel(items: data.featured),
                        const SizedBox(height: 20),
                        _GenreBar(genres: data.genres),
                        if (data.continueWatching.isNotEmpty) ...[
                          const SizedBox(height: 28),
                          const SectionTitle(
                            kicker: 'Continue watching',
                            title: 'Continue Watching',
                            subtitle:
                                'Lanjut dari episode terakhir tanpa mencari ulang.',
                          ),
                          const SizedBox(height: 14),
                          SizedBox(
                            height: 252,
                            child: ListView.separated(
                              scrollDirection: Axis.horizontal,
                              itemCount: data.continueWatching.length,
                              separatorBuilder: (_, __) =>
                                  const SizedBox(width: 12),
                              itemBuilder: (context, index) => AnimeCard(
                                anime: data.continueWatching[index],
                                width: 152,
                                showProgress: true,
                              ),
                            ),
                          ),
                        ],
                        const SizedBox(height: 28),
                        SectionTitle(
                          kicker: 'Trending now',
                          title: 'Trending Now',
                          subtitle: 'Most watched anime right now',
                          actionLabel: 'View all',
                          onActionTap: () => context.go('/catalog'),
                        ),
                      ],
                    ),
                  ),
                ),
                SliverGrid(
                  delegate: SliverChildBuilderDelegate(
                    (context, index) => AnimeCard(anime: data.trending[index]),
                    childCount: data.trending.take(6).length,
                  ),
                  gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
                    crossAxisCount: AppSpacing.columns(context, max: 4),
                    mainAxisSpacing: 12,
                    crossAxisSpacing: 12,
                    childAspectRatio: 0.62,
                  ),
                ),
                SliverToBoxAdapter(
                  child: Padding(
                    padding: const EdgeInsets.fromLTRB(0, 28, 0, 24),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        SectionTitle(
                          kicker: 'Popular this week',
                          title: 'Popular This Week',
                          subtitle:
                              'Judul dengan engagement tertinggi minggu ini.',
                          actionLabel: 'View all',
                          onActionTap: () => context.go('/catalog'),
                        ),
                        const SizedBox(height: 14),
                        SizedBox(
                          height: 252,
                          child: ListView.separated(
                            scrollDirection: Axis.horizontal,
                            itemCount: data.popular.length,
                            separatorBuilder: (_, __) =>
                                const SizedBox(width: 12),
                            itemBuilder: (context, index) => AnimeCard(
                              anime: data.popular[index],
                              width: 152,
                            ),
                          ),
                        ),
                        const SizedBox(height: 28),
                        SectionTitle(
                          kicker: 'Latest drop',
                          title: 'Latest Episode Updates',
                          subtitle:
                              'Episode dan update terbaru dari serial favoritmu.',
                          actionLabel: 'Ongoing',
                          onActionTap: () => context.go('/ongoing'),
                        ),
                        const SizedBox(height: 14),
                        ...data.latest
                            .take(4)
                            .map(
                              (anime) => Padding(
                                padding: const EdgeInsets.only(bottom: 10),
                                child: _LatestEpisodeTile(anime: anime),
                              ),
                            ),
                        const SizedBox(height: 20),
                        _StatusHighlights(
                          ongoing: data.popular
                              .where((item) => item.status == 'Ongoing')
                              .toList(),
                          completed: data.recommended
                              .where((item) => item.status == 'Completed')
                              .toList(),
                        ),
                        const SizedBox(height: 28),
                        const SectionTitle(
                          kicker: 'From the community',
                          title: 'From the Community',
                          subtitle:
                              'Aktivitas dan pengumuman terbaru dari komunitas Fer6origami.',
                        ),
                        const SizedBox(height: 14),
                        const _CommunityStrip(),
                        const SizedBox(height: 28),
                        const SectionTitle(
                          kicker: 'Browse by year and score',
                          title: 'Browse by Year and Score',
                          subtitle:
                              'Persempit pencarian berdasarkan tahun rilis dan rating.',
                        ),
                        const SizedBox(height: 14),
                        const _BrowseFilters(),
                      ],
                    ),
                  ),
                ),
              ],
            ),
          ),
          loading: () => const _HomeLoading(),
          error: (_, __) => AppErrorState(
            title: 'Home belum bisa dimuat',
            message:
                'Terjadi masalah saat menyiapkan katalog anime. Coba refresh lagi.',
            onRetry: () => ref.invalidate(homeControllerProvider),
          ),
        ),
      ),
    );
  }
}

class _HeroCarousel extends StatefulWidget {
  const _HeroCarousel({required this.items});

  final List<Anime> items;

  @override
  State<_HeroCarousel> createState() => _HeroCarouselState();
}

class _HeroCarouselState extends State<_HeroCarousel> {
  final PageController _controller = PageController();
  Timer? _timer;
  int _current = 0;

  @override
  void initState() {
    super.initState();
    _startTimer();
  }

  @override
  void dispose() {
    _timer?.cancel();
    _controller.dispose();
    super.dispose();
  }

  void _startTimer() {
    _timer?.cancel();
    if (widget.items.length < 2) {
      return;
    }
    _timer = Timer.periodic(const Duration(milliseconds: 5500), (_) {
      if (!mounted || widget.items.isEmpty) {
        return;
      }
      final next = (_current + 1) % widget.items.length;
      _controller.animateToPage(
        next,
        duration: const Duration(milliseconds: 600),
        curve: Curves.easeInOut,
      );
    });
  }

  void _goTo(int index) {
    if (widget.items.isEmpty) {
      return;
    }
    final next = (index + widget.items.length) % widget.items.length;
    _controller.animateToPage(
      next,
      duration: const Duration(milliseconds: 450),
      curve: Curves.easeInOut,
    );
  }

  @override
  Widget build(BuildContext context) {
    final items = widget.items.take(5).toList();
    if (items.isEmpty) {
      return const SizedBox(height: 360);
    }

    return Container(
      height: 400,
      decoration: BoxDecoration(
        color: AppColors.panel,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.border),
      ),
      clipBehavior: Clip.antiAlias,
      child: Stack(
        children: [
          PageView.builder(
            controller: _controller,
            itemCount: items.length,
            onPageChanged: (index) {
              setState(() => _current = index);
              _startTimer();
            },
            itemBuilder: (context, index) =>
                _HeroSlide(anime: items[index], index: index),
          ),
          Positioned(
            left: 16,
            right: 16,
            bottom: 12,
            child: Row(
              children: [
                ...List.generate(items.length, (index) {
                  final active = index == _current;
                  return GestureDetector(
                    onTap: () => _goTo(index),
                    child: AnimatedContainer(
                      duration: const Duration(milliseconds: 250),
                      margin: const EdgeInsets.only(right: 6),
                      width: active ? 28 : 8,
                      height: 8,
                      decoration: BoxDecoration(
                        color: active ? AppColors.brand : Colors.white24,
                        borderRadius: BorderRadius.circular(999),
                      ),
                    ),
                  );
                }),
                const Spacer(),
                _HeroArrow(
                  icon: Icons.chevron_left_rounded,
                  onTap: () => _goTo(_current - 1),
                ),
                const SizedBox(width: 8),
                _HeroArrow(
                  icon: Icons.chevron_right_rounded,
                  onTap: () => _goTo(_current + 1),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _HeroSlide extends StatelessWidget {
  const _HeroSlide({required this.anime, required this.index});

  final Anime anime;
  final int index;

  @override
  Widget build(BuildContext context) {
    final wide = MediaQuery.sizeOf(context).width >= 900;

    return Stack(
      fit: StackFit.expand,
      children: [
        CachedNetworkImage(
          imageUrl: anime.bannerUrl,
          fit: BoxFit.cover,
          color: AppColors.background.withValues(alpha: 0.55),
          colorBlendMode: BlendMode.darken,
          errorWidget: (_, __, ___) => Container(color: AppColors.panel),
        ),
        const DecoratedBox(
          decoration: BoxDecoration(
            gradient: LinearGradient(
              begin: Alignment.bottomCenter,
              end: Alignment.topCenter,
              colors: [Color(0xFF11131B), Color(0x9911131B), Color(0x3311131B)],
            ),
          ),
        ),
        Padding(
          padding: const EdgeInsets.fromLTRB(20, 20, 20, 52),
          child: Row(
            children: [
              Expanded(
                flex: wide ? 7 : 1,
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Wrap(
                      spacing: 8,
                      runSpacing: 8,
                      children: [
                        _HeroBadge(
                          label: 'Featured Spotlight #${index + 1}',
                          color: AppColors.brand,
                          textColor: Colors.white,
                        ),
                        _HeroBadge(
                          label: anime.status,
                          color: const Color(0x14FFFFFF),
                          textColor: Colors.white,
                        ),
                        _HeroBadge(
                          label: anime.score.toStringAsFixed(1),
                          color: const Color(0x33FBBF24),
                          textColor: AppColors.warning,
                        ),
                      ],
                    ),
                    const SizedBox(height: 16),
                    Text(
                      anime.title,
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                      style: Theme.of(context).textTheme.displaySmall?.copyWith(
                        fontSize: wide ? 32 : 26,
                      ),
                    ),
                    const SizedBox(height: 10),
                    Text(
                      'ABOUT THIS ANIME  •  ${anime.studio}  •  ${anime.episodeInfo}',
                      style: const TextStyle(
                        fontSize: 10,
                        fontWeight: FontWeight.w700,
                        letterSpacing: 0.6,
                        color: AppColors.textSecondary,
                      ),
                    ),
                    const SizedBox(height: 8),
                    Text(
                      anime.synopsis,
                      maxLines: 3,
                      overflow: TextOverflow.ellipsis,
                      style: const TextStyle(
                        fontSize: 12,
                        height: 1.5,
                        color: Color(0xFFCBD5E1),
                      ),
                    ),
                    const SizedBox(height: 12),
                    Wrap(
                      spacing: 6,
                      runSpacing: 6,
                      children: anime.genres
                          .take(4)
                          .map(
                            (genre) => _HeroBadge(
                              label: genre,
                              color: const Color(0x14FFFFFF),
                              textColor: AppColors.textSecondary,
                            ),
                          )
                          .toList(),
                    ),
                    const Spacer(),
                    Row(
                      children: [
                        ElevatedButton(
                          onPressed: () => context.push('/anime/${anime.id}'),
                          style: ElevatedButton.styleFrom(
                            backgroundColor: AppColors.brand,
                            foregroundColor: Colors.white,
                            elevation: 0,
                            padding: const EdgeInsets.symmetric(
                              horizontal: 18,
                              vertical: 12,
                            ),
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(10),
                            ),
                            textStyle: const TextStyle(
                              fontSize: 12,
                              fontWeight: FontWeight.w800,
                            ),
                          ),
                          child: const Text('Watch Now'),
                        ),
                        const SizedBox(width: 10),
                        OutlinedButton(
                          onPressed: () => context.go('/ongoing'),
                          style: OutlinedButton.styleFrom(
                            foregroundColor: Colors.white,
                            side: const BorderSide(
                              color: AppColors.borderStrong,
                            ),
                            padding: const EdgeInsets.symmetric(
                              horizontal: 14,
                              vertical: 12,
                            ),
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(10),
                            ),
                            textStyle: const TextStyle(
                              fontSize: 12,
                              fontWeight: FontWeight.w700,
                            ),
                          ),
                          child: const Text('View Ongoing Schedule'),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
              if (wide) ...[
                const SizedBox(width: 24),
                SizedBox(width: 260, child: _HeroPreview(anime: anime)),
              ],
            ],
          ),
        ),
      ],
    );
  }
}

class _HeroPreview extends StatelessWidget {
  const _HeroPreview({required this.anime});

  final Anime anime;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: const Color(0xE611131B),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: AppColors.borderStrong),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Expanded(
            child: ClipRRect(
              borderRadius: BorderRadius.circular(8),
              child: CachedNetworkImage(
                imageUrl: anime.posterUrl,
                fit: BoxFit.cover,
                width: double.infinity,
                errorWidget: (_, __, ___) => Container(color: AppColors.card),
              ),
            ),
          ),
          const SizedBox(height: 12),
          Row(
            children: [
              Expanded(
                child: Text(
                  anime.title,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: const TextStyle(
                    fontSize: 14,
                    fontWeight: FontWeight.w700,
                    color: Colors.white,
                  ),
                ),
              ),
              Text(
                anime.episodeInfo,
                style: const TextStyle(
                  fontSize: 11,
                  color: AppColors.brand400,
                  fontWeight: FontWeight.w700,
                ),
              ),
            ],
          ),
          const SizedBox(height: 8),
          const Divider(height: 1),
          const SizedBox(height: 8),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                'Studio: ${anime.studio}',
                style: const TextStyle(
                  fontSize: 10,
                  color: AppColors.textSecondary,
                ),
              ),
              Text(
                'Year: ${anime.year}',
                style: const TextStyle(
                  fontSize: 10,
                  color: AppColors.textSecondary,
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }
}

class _HeroBadge extends StatelessWidget {
  const _HeroBadge({
    required this.label,
    required this.color,
    required this.textColor,
  });

  final String label;
  final Color color;
  final Color textColor;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      decoration: BoxDecoration(
        color: color,
        borderRadius: BorderRadius.circular(6),
      ),
      child: Text(
        label,
        style: TextStyle(
          fontSize: 10,
          fontWeight: FontWeight.w700,
          color: textColor,
        ),
      ),
    );
  }
}

class _HeroArrow extends StatelessWidget {
  const _HeroArrow({required this.icon, required this.onTap});

  final IconData icon;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(8),
      child: Container(
        width: 30,
        height: 30,
        decoration: BoxDecoration(
          color: const Color(0x99000000),
          borderRadius: BorderRadius.circular(8),
          border: Border.all(color: AppColors.borderStrong),
        ),
        child: Icon(icon, size: 18, color: Colors.white),
      ),
    );
  }
}

class _GenreBar extends StatelessWidget {
  const _GenreBar({required this.genres});

  final List<String> genres;

  @override
  Widget build(BuildContext context) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: AppColors.panel,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: AppColors.border),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              const Expanded(
                child: Text(
                  'BROWSE POPULAR GENRES',
                  style: TextStyle(
                    fontSize: 11,
                    fontWeight: FontWeight.w800,
                    letterSpacing: 1.4,
                    color: AppColors.textSecondary,
                  ),
                ),
              ),
              GestureDetector(
                onTap: () => context.go('/catalog'),
                child: const Text(
                  'All Genres',
                  style: TextStyle(
                    fontSize: 11,
                    fontWeight: FontWeight.w700,
                    color: AppColors.brand400,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),
          Wrap(
            spacing: 8,
            runSpacing: 8,
            children: genres.take(12).map((genre) {
              return InkWell(
                onTap: () => context.go(
                  Uri(
                    path: '/catalog',
                    queryParameters: {'genre': genre},
                  ).toString(),
                ),
                borderRadius: BorderRadius.circular(8),
                child: Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 10,
                    vertical: 6,
                  ),
                  decoration: BoxDecoration(
                    color: AppColors.card,
                    borderRadius: BorderRadius.circular(8),
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
              );
            }).toList(),
          ),
        ],
      ),
    );
  }
}

class _LatestEpisodeTile extends StatelessWidget {
  const _LatestEpisodeTile({required this.anime});

  final Anime anime;

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: () => context.push('/anime/${anime.id}'),
      borderRadius: BorderRadius.circular(10),
      child: Container(
        padding: const EdgeInsets.all(10),
        decoration: BoxDecoration(
          color: AppColors.panel,
          borderRadius: BorderRadius.circular(10),
          border: Border.all(color: AppColors.border),
        ),
        child: Row(
          children: [
            ClipRRect(
              borderRadius: BorderRadius.circular(6),
              child: CachedNetworkImage(
                imageUrl: anime.posterUrl,
                width: 46,
                height: 62,
                fit: BoxFit.cover,
                errorWidget: (_, __, ___) =>
                    Container(color: AppColors.card, width: 46, height: 62),
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    anime.title,
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(
                      fontSize: 13,
                      fontWeight: FontWeight.w700,
                      color: Colors.white,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    anime.episodeInfo,
                    style: const TextStyle(
                      fontSize: 11,
                      color: AppColors.brand400,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    anime.genres.take(2).join(' • '),
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
              size: 22,
              color: AppColors.textSecondary,
            ),
          ],
        ),
      ),
    );
  }
}

class _StatusHighlights extends StatelessWidget {
  const _StatusHighlights({required this.ongoing, required this.completed});

  final List<Anime> ongoing;
  final List<Anime> completed;

  @override
  Widget build(BuildContext context) {
    return LayoutBuilder(
      builder: (context, constraints) {
        final wide = constraints.maxWidth >= 720;
        final children = [
          _StatusPanel(
            title: 'Ongoing Anime',
            actionLabel: 'View All',
            onActionTap: () => context.go('/ongoing'),
            items: ongoing.take(5).toList(),
          ),
          _StatusPanel(
            title: 'Completed Anime',
            actionLabel: 'View All',
            onActionTap: () => context.go('/completed'),
            items: completed.take(5).toList(),
          ),
        ];

        if (wide) {
          return Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Expanded(child: children[0]),
              const SizedBox(width: 14),
              Expanded(child: children[1]),
            ],
          );
        }

        return Column(
          children: [children[0], const SizedBox(height: 14), children[1]],
        );
      },
    );
  }
}

class _StatusPanel extends StatelessWidget {
  const _StatusPanel({
    required this.title,
    required this.actionLabel,
    required this.onActionTap,
    required this.items,
  });

  final String title;
  final String actionLabel;
  final VoidCallback onActionTap;
  final List<Anime> items;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: AppColors.panel,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: AppColors.border),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Expanded(
                child: Text(
                  title,
                  style: Theme.of(context).textTheme.titleMedium,
                ),
              ),
              GestureDetector(
                onTap: onActionTap,
                child: Text(
                  actionLabel,
                  style: const TextStyle(
                    fontSize: 11,
                    fontWeight: FontWeight.w700,
                    color: AppColors.brand400,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),
          ...items.map(
            (anime) => Padding(
              padding: const EdgeInsets.symmetric(vertical: 7),
              child: InkWell(
                onTap: () => context.push('/anime/${anime.id}'),
                child: Row(
                  children: [
                    Expanded(
                      child: Text(
                        anime.title,
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: const TextStyle(
                          fontSize: 12,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                    ),
                    const SizedBox(width: 8),
                    Text(
                      anime.episodeInfo,
                      style: const TextStyle(
                        fontSize: 10,
                        color: AppColors.textSecondary,
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _CommunityStrip extends StatelessWidget {
  const _CommunityStrip();

  @override
  Widget build(BuildContext context) {
    return LayoutBuilder(
      builder: (context, constraints) {
        final cards = [
          _InfoCard(
            icon: Icons.forum_outlined,
            title: 'From the Community',
            body: 'Lihat review dan diskusi terbaru dari penonton Fer6origami.',
            action: 'Open discussion',
            onTap: () => context.go('/search'),
          ),
          _InfoCard(
            icon: Icons.campaign_outlined,
            title: 'Announcements',
            body:
                'Pembaruan katalog, fitur baru, dan pengumuman penting lainnya.',
            action: 'Read updates',
            onTap: () => context.go('/membership'),
          ),
        ];
        if (constraints.maxWidth >= 720) {
          return Row(
            children: [
              Expanded(child: cards[0]),
              const SizedBox(width: 14),
              Expanded(child: cards[1]),
            ],
          );
        }
        return Column(
          children: [cards[0], const SizedBox(height: 14), cards[1]],
        );
      },
    );
  }
}

class _InfoCard extends StatelessWidget {
  const _InfoCard({
    required this.icon,
    required this.title,
    required this.body,
    required this.action,
    required this.onTap,
  });

  final IconData icon;
  final String title;
  final String body;
  final String action;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: AppColors.panel,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: AppColors.border),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(icon, size: 20, color: AppColors.brand400),
          const SizedBox(height: 10),
          Text(title, style: Theme.of(context).textTheme.titleMedium),
          const SizedBox(height: 6),
          Text(body, style: Theme.of(context).textTheme.bodySmall),
          const SizedBox(height: 10),
          GestureDetector(
            onTap: onTap,
            child: Text(
              action,
              style: const TextStyle(
                fontSize: 11,
                fontWeight: FontWeight.w700,
                color: AppColors.brand400,
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _BrowseFilters extends StatelessWidget {
  const _BrowseFilters();

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: AppColors.panel,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: AppColors.border),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'YEAR',
            style: TextStyle(
              fontSize: 10,
              fontWeight: FontWeight.w800,
              letterSpacing: 1.4,
              color: AppColors.textSecondary,
            ),
          ),
          const SizedBox(height: 10),
          Wrap(
            spacing: 8,
            children: ['2026', '2025', '2024', '2023']
                .map(
                  (year) =>
                      _Pill(label: year, onTap: () => context.go('/catalog')),
                )
                .toList(),
          ),
          const SizedBox(height: 16),
          const Text(
            'SCORE',
            style: TextStyle(
              fontSize: 10,
              fontWeight: FontWeight.w800,
              letterSpacing: 1.4,
              color: AppColors.textSecondary,
            ),
          ),
          const SizedBox(height: 10),
          Wrap(
            spacing: 8,
            children: ['9+', '8+', '7+']
                .map(
                  (score) =>
                      _Pill(label: score, onTap: () => context.go('/catalog')),
                )
                .toList(),
          ),
        ],
      ),
    );
  }
}

class _Pill extends StatelessWidget {
  const _Pill({required this.label, required this.onTap});

  final String label;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(6),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
        decoration: BoxDecoration(
          color: AppColors.card,
          borderRadius: BorderRadius.circular(6),
          border: Border.all(color: AppColors.border),
        ),
        child: Text(
          label,
          style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w600),
        ),
      ),
    );
  }
}

class _HomeLoading extends StatelessWidget {
  const _HomeLoading();

  @override
  Widget build(BuildContext context) {
    return ListView(
      padding: const EdgeInsets.fromLTRB(0, 16, 0, 24),
      children: const [
        LoadingSkeleton(height: 400, radius: 16),
        SizedBox(height: 20),
        LoadingSkeleton(height: 96, radius: 12),
        SizedBox(height: 24),
        LoadingSkeleton(height: 24, width: 180),
        SizedBox(height: 12),
        LoadingSkeleton(height: 250, radius: 12),
      ],
    );
  }
}
