import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../features/anime_detail/presentation/anime_detail_page.dart';
import '../../features/bookmarks/presentation/bookmarks_page.dart';
import '../../features/catalog/presentation/anime_listing_page.dart';
import '../../features/home/presentation/home_page.dart';
import '../../features/membership/presentation/membership_page.dart';
import '../../features/onboarding/presentation/onboarding_page.dart';
import '../../features/profile/presentation/profile_page.dart';
import '../../features/search/presentation/search_page.dart';
import '../../features/settings/controllers/settings_controller.dart';
import '../../features/shell/presentation/app_shell.dart';
import '../../features/splash/presentation/splash_page.dart';

final appRouterProvider = Provider<GoRouter>((ref) {
  return GoRouter(
    initialLocation: '/splash',
    routes: [
      GoRoute(
        path: '/splash',
        name: 'splash',
        builder: (context, state) => const SplashPage(),
      ),
      GoRoute(
        path: '/onboarding',
        name: 'onboarding',
        builder: (context, state) => const OnboardingPage(),
      ),
      StatefulShellRoute.indexedStack(
        builder: (context, state, navigationShell) =>
            AppShell(navigationShell: navigationShell),
        branches: [
          StatefulShellBranch(
            routes: [
              GoRoute(
                path: '/',
                name: 'home',
                builder: (context, state) => const HomePage(),
              ),
              GoRoute(
                path: '/search',
                name: 'search',
                builder: (context, state) => const SearchPage(),
              ),
            ],
          ),
          StatefulShellBranch(
            routes: [
              GoRoute(
                path: '/catalog',
                name: 'catalog',
                builder: (context, state) => AnimeListingPage(
                  kicker: 'Collection Library',
                  title: 'Anime Catalog',
                  subtitle:
                      'Jelajahi seluruh judul anime yang tersedia di Fer6origami.',
                  status: 'all',
                  routePath: '/catalog',
                  genre: state.uri.queryParameters['genre'],
                ),
              ),
              GoRoute(
                path: '/completed',
                name: 'completed',
                builder: (context, state) => const AnimeListingPage(
                  kicker: 'Completed Series',
                  title: 'Completed Anime',
                  subtitle:
                      'Serial yang sudah tamat dan siap untuk ditonton satu maraton.',
                  status: 'completed',
                  routePath: '/completed',
                ),
              ),
            ],
          ),
          StatefulShellBranch(
            routes: [
              GoRoute(
                path: '/ongoing',
                name: 'ongoing',
                builder: (context, state) => const AnimeListingPage(
                  kicker: 'Weekly Schedule',
                  title: 'Ongoing Anime',
                  subtitle:
                      'Ikuti episode terbaru yang masih berjalan setiap minggu.',
                  status: 'ongoing',
                  routePath: '/ongoing',
                ),
              ),
            ],
          ),
          StatefulShellBranch(
            routes: [
              GoRoute(
                path: '/bookmarks',
                name: 'bookmarks',
                builder: (context, state) => const BookmarksPage(),
              ),
            ],
          ),
          StatefulShellBranch(
            routes: [
              GoRoute(
                path: '/profile',
                name: 'profile',
                builder: (context, state) => const ProfilePage(),
              ),
              GoRoute(
                path: '/membership',
                name: 'membership',
                builder: (context, state) => const MembershipPage(),
              ),
            ],
          ),
        ],
      ),
      GoRoute(
        path: '/anime/:id',
        name: 'anime-detail',
        builder: (context, state) =>
            AnimeDetailPage(animeId: state.pathParameters['id'] ?? ''),
      ),
    ],
    redirect: (context, state) {
      final settings = ref.read(settingsControllerProvider);
      final doneOnboarding = settings.didFinishOnboarding;
      final isSplash = state.matchedLocation == '/splash';
      final isOnboarding = state.matchedLocation == '/onboarding';

      if (!doneOnboarding && !isSplash && !isOnboarding) {
        return '/onboarding';
      }

      if (doneOnboarding && isOnboarding) {
        return '/';
      }

      return null;
    },
  );
});
