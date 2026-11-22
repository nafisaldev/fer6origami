import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../core/constants/app_colors.dart';
import '../../../shared/widgets/app_content_container.dart';
import '../../home/controllers/home_controller.dart';
import '../../settings/controllers/settings_controller.dart';

class AppShell extends ConsumerWidget {
  const AppShell({super.key, required this.navigationShell});

  final StatefulNavigationShell navigationShell;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final settings = ref.watch(settingsControllerProvider);

    return Scaffold(
      backgroundColor: AppColors.background,
      endDrawer: _AppDrawer(settings.profileName),
      body: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          _AppHeader(settings.profileName),
          Expanded(child: navigationShell),
        ],
      ),
      bottomNavigationBar: _BottomBar(
        selectedIndex: navigationShell.currentIndex,
        onDestinationSelected: (index) => navigationShell.goBranch(
          index,
          initialLocation: index == navigationShell.currentIndex,
        ),
      ),
    );
  }
}

class _AppHeader extends ConsumerWidget {
  const _AppHeader(this.profileName);

  final String profileName;

  static const _fallbackGenres = [
    'Action',
    'Adventure',
    'Comedy',
    'Drama',
    'Fantasy',
    'Horror',
    'Mystery',
    'Romance',
    'Sci-Fi',
    'Slice of Life',
    'Supernatural',
    'Thriller',
  ];

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final currentPath = GoRouterState.of(context).uri.path;
    final genresAsync = ref.watch(homeControllerProvider);
    final genres = genresAsync.valueOrNull?.genres ?? _fallbackGenres;

    return Container(
      height: 56,
      decoration: const BoxDecoration(
        color: Color(0xF5090A0F),
        border: Border(bottom: BorderSide(color: Color(0x0FFFFFFF))),
      ),
      child: Center(
        child: ConstrainedBox(
          constraints: const BoxConstraints(
            maxWidth: AppSpacing.maxContentWidth,
          ),
          child: Padding(
            padding: EdgeInsets.symmetric(
              horizontal: AppSpacing.horizontal(context),
            ),
            child: Row(
              children: [
                GestureDetector(
                  onTap: () => context.go('/'),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Image.asset(
                        'assets/images/emblem.png',
                        width: 28,
                        height: 28,
                        errorBuilder: (_, __, ___) => const Icon(
                          Icons.local_fire_department_rounded,
                          size: 24,
                          color: AppColors.brand,
                        ),
                      ),
                      const SizedBox(width: 8),
                      RichText(
                        text: const TextSpan(
                          style: TextStyle(
                            fontFamily: 'Syne',
                            fontSize: 17,
                            fontWeight: FontWeight.w800,
                            letterSpacing: -0.4,
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
                      ),
                    ],
                  ),
                ),
                const SizedBox(width: 24),
                LayoutBuilder(
                  builder: (context, constraints) {
                    if (constraints.maxWidth >= 980) {
                      return Expanded(
                        child: Row(
                          children: [
                            _NavLink(
                              label: 'Home',
                              path: '/',
                              active: currentPath == '/',
                              onTap: () => context.go('/'),
                            ),
                            _NavLink(
                              label: 'Catalog',
                              path: '/catalog',
                              active: currentPath.startsWith('/catalog'),
                              onTap: () => context.go('/catalog'),
                            ),
                            _NavLink(
                              label: 'Ongoing',
                              path: '/ongoing',
                              active: currentPath == '/ongoing',
                              onTap: () => context.go('/ongoing'),
                            ),
                            _NavLink(
                              label: 'Completed',
                              path: '/completed',
                              active: currentPath == '/completed',
                              onTap: () => context.go('/completed'),
                            ),
                            _NavLink(
                              label: 'Membership',
                              path: '/membership',
                              active: currentPath == '/membership',
                              onTap: () => context.go('/membership'),
                            ),
                            PopupMenuButton<String>(
                              tooltip: 'Genres',
                              offset: const Offset(0, 44),
                              color: AppColors.panel,
                              shape: RoundedRectangleBorder(
                                borderRadius: BorderRadius.circular(10),
                                side: const BorderSide(
                                  color: AppColors.borderStrong,
                                ),
                              ),
                              onSelected: (genre) => context.go(
                                Uri(
                                  path: '/catalog',
                                  queryParameters: {'genre': genre},
                                ).toString(),
                              ),
                              itemBuilder: (context) => genres
                                  .map(
                                    (genre) => PopupMenuItem<String>(
                                      value: genre,
                                      child: Text(genre),
                                    ),
                                  )
                                  .toList(),
                              child: Padding(
                                padding: const EdgeInsets.symmetric(
                                  horizontal: 10,
                                  vertical: 8,
                                ),
                                child: Row(
                                  mainAxisSize: MainAxisSize.min,
                                  children: [
                                    Text(
                                      'Genres',
                                      style: TextStyle(
                                        fontSize: 12,
                                        fontWeight: FontWeight.w700,
                                        color: AppColors.textSecondary,
                                      ),
                                    ),
                                    const SizedBox(width: 3),
                                    const Icon(
                                      Icons.keyboard_arrow_down_rounded,
                                      size: 16,
                                      color: AppColors.textSecondary,
                                    ),
                                  ],
                                ),
                              ),
                            ),
                          ],
                        ),
                      );
                    }
                    return const Spacer();
                  },
                ),
                if (constraintsOf(context) >= 1280) ...[
                  SizedBox(
                    width: 220,
                    height: 34,
                    child: InkWell(
                      onTap: () => context.go('/search'),
                      borderRadius: BorderRadius.circular(8),
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10),
                        decoration: BoxDecoration(
                          color: AppColors.card,
                          borderRadius: BorderRadius.circular(8),
                          border: Border.all(color: AppColors.border),
                        ),
                        child: Row(
                          children: [
                            const Icon(
                              Icons.search_rounded,
                              size: 16,
                              color: AppColors.textSecondary,
                            ),
                            const SizedBox(width: 8),
                            Text(
                              'Search anime, genre, studio...',
                              style: Theme.of(context).textTheme.bodySmall,
                            ),
                          ],
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(width: 14),
                ],
                IconButton(
                  tooltip: 'Search',
                  onPressed: () => context.go('/search'),
                  icon: const Icon(Icons.search_rounded, size: 20),
                ),
                IconButton(
                  tooltip: 'Notifications',
                  onPressed: () => context.go('/membership'),
                  icon: const Icon(Icons.notifications_none_rounded, size: 20),
                ),
                const SizedBox(width: 4),
                GestureDetector(
                  onTap: () => context.go('/profile'),
                  child: Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 8,
                      vertical: 5,
                    ),
                    decoration: BoxDecoration(
                      color: const Color(0x24E53535),
                      borderRadius: BorderRadius.circular(6),
                      border: Border.all(color: const Color(0x66E53535)),
                    ),
                    child: Text(
                      'FREE',
                      style: const TextStyle(
                        fontSize: 10,
                        fontWeight: FontWeight.w800,
                        letterSpacing: 0.6,
                        color: AppColors.brand300,
                      ),
                    ),
                  ),
                ),
                const SizedBox(width: 8),
                GestureDetector(
                  onTap: () => context.go('/profile'),
                  child: CircleAvatar(
                    radius: 15,
                    backgroundColor: AppColors.brand,
                    child: Text(
                      profileName.characters.first.toUpperCase(),
                      style: const TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.w800,
                        color: Colors.white,
                      ),
                    ),
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  double constraintsOf(BuildContext context) =>
      MediaQuery.sizeOf(context).width;
}

class _NavLink extends StatelessWidget {
  const _NavLink({
    required this.label,
    required this.path,
    required this.active,
    required this.onTap,
  });

  final String label;
  final String path;
  final bool active;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
        child: Text(
          label,
          style: TextStyle(
            fontSize: 12,
            fontWeight: active ? FontWeight.w800 : FontWeight.w600,
            color: active ? AppColors.brand400 : AppColors.textSecondary,
          ),
        ),
      ),
    );
  }
}

class _AppDrawer extends StatelessWidget {
  const _AppDrawer(this.profileName);

  final String profileName;

  @override
  Widget build(BuildContext context) {
    final links = <(String, IconData, String)>[
      ('Home', Icons.home_rounded, '/'),
      ('Catalog', Icons.view_list_rounded, '/catalog'),
      ('Ongoing', Icons.schedule_rounded, '/ongoing'),
      ('Completed', Icons.check_circle_outline_rounded, '/completed'),
      ('Membership', Icons.workspace_premium_rounded, '/membership'),
      ('Watchlist', Icons.bookmark_rounded, '/bookmarks'),
      ('Search', Icons.search_rounded, '/search'),
    ];

    return Drawer(
      backgroundColor: AppColors.panel,
      child: SafeArea(
        child: ListView(
          padding: EdgeInsets.zero,
          children: [
            Padding(
              padding: const EdgeInsets.fromLTRB(20, 20, 20, 16),
              child: Row(
                children: [
                  CircleAvatar(
                    radius: 20,
                    backgroundColor: AppColors.brand,
                    child: Text(
                      profileName.characters.first.toUpperCase(),
                      style: const TextStyle(
                        color: Colors.white,
                        fontWeight: FontWeight.w800,
                      ),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          profileName,
                          style: Theme.of(context).textTheme.titleMedium,
                        ),
                        const Text(
                          'Free member',
                          style: TextStyle(
                            fontSize: 11,
                            color: AppColors.textSecondary,
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
            const Divider(height: 1),
            ...links.map(
              (link) => ListTile(
                leading: Icon(
                  link.$2,
                  size: 20,
                  color: AppColors.textSecondary,
                ),
                title: Text(
                  link.$1,
                  style: const TextStyle(
                    fontSize: 13,
                    fontWeight: FontWeight.w600,
                  ),
                ),
                onTap: () {
                  Navigator.of(context).pop();
                  context.go(link.$3);
                },
              ),
            ),
            const Divider(height: 1),
            const Padding(
              padding: EdgeInsets.fromLTRB(20, 16, 20, 8),
              child: Text(
                'BROWSE GENRES',
                style: TextStyle(
                  fontSize: 10,
                  fontWeight: FontWeight.w800,
                  letterSpacing: 1.5,
                  color: AppColors.brand400,
                ),
              ),
            ),
            ..._AppHeader._fallbackGenres.map(
              (genre) => ListTile(
                dense: true,
                title: Text(genre, style: const TextStyle(fontSize: 12)),
                onTap: () {
                  Navigator.of(context).pop();
                  context.go(
                    Uri(
                      path: '/catalog',
                      queryParameters: {'genre': genre},
                    ).toString(),
                  );
                },
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _BottomBar extends StatelessWidget {
  const _BottomBar({
    required this.selectedIndex,
    required this.onDestinationSelected,
  });

  final int selectedIndex;
  final ValueChanged<int> onDestinationSelected;

  static const _items = <(String, IconData)>[
    ('Home', Icons.home_rounded),
    ('Catalog', Icons.view_list_rounded),
    ('Ongoing', Icons.schedule_rounded),
    ('Watchlist', Icons.bookmark_border_rounded),
    ('Profile', Icons.person_outline_rounded),
  ];

  @override
  Widget build(BuildContext context) {
    return SafeArea(
      top: false,
      child: Container(
        height: 62,
        decoration: const BoxDecoration(
          color: Color(0xF5090A0F),
          border: Border(top: BorderSide(color: Color(0x1AFFFFFF))),
        ),
        child: Row(
          children: [
            for (var index = 0; index < _items.length; index++)
              Expanded(
                child: InkWell(
                  onTap: () => onDestinationSelected(index),
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(
                        _items[index].$2,
                        size: 20,
                        color: index == selectedIndex
                            ? AppColors.brand400
                            : AppColors.textSecondary,
                      ),
                      const SizedBox(height: 3),
                      Text(
                        _items[index].$1,
                        style: TextStyle(
                          fontSize: 10,
                          fontWeight: index == selectedIndex
                              ? FontWeight.w800
                              : FontWeight.w600,
                          color: index == selectedIndex
                              ? AppColors.brand400
                              : AppColors.textSecondary,
                        ),
                      ),
                    ],
                  ),
                ),
              ),
          ],
        ),
      ),
    );
  }
}
