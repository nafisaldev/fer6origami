import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../core/constants/app_colors.dart';
import '../../../shared/widgets/app_content_container.dart';
import '../../../shared/widgets/app_error_state.dart';
import '../../../shared/widgets/empty_state.dart';
import '../../../shared/widgets/loading_skeleton.dart';
import '../controllers/bookmarks_controller.dart';

class BookmarksPage extends ConsumerWidget {
  const BookmarksPage({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final state = ref.watch(bookmarkedAnimeProvider);
    final total = state.valueOrNull?.length ?? 0;

    return Scaffold(
      body: AppContentContainer(
        includeVerticalPadding: false,
        child: Padding(
          padding: const EdgeInsets.fromLTRB(0, 16, 0, 24),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              _WatchlistHeader(total: total),
              const SizedBox(height: 16),
              Expanded(
                child: state.when(
                  data: (items) {
                    if (items.isEmpty) {
                      return const EmptyState(
                        title: 'Belum ada anime tersimpan',
                        message:
                            'Simpan anime favoritmu dari halaman detail agar muncul di watchlist.',
                        icon: Icons.bookmark_border_rounded,
                      );
                    }

                    return ResponsiveAnimeGrid(
                      items: items,
                      maxColumns: 6,
                      physics: const BouncingScrollPhysics(),
                    );
                  },
                  loading: () => GridView.builder(
                    itemCount: 4,
                    gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
                      crossAxisCount: AppSpacing.columns(context, max: 4),
                      crossAxisSpacing: 12,
                      mainAxisSpacing: 12,
                      childAspectRatio: 0.62,
                    ),
                    itemBuilder: (_, __) =>
                        const LoadingSkeleton(height: 250, radius: 12),
                  ),
                  error: (_, __) => AppErrorState(
                    title: 'Watchlist belum bisa dimuat',
                    message: 'Terjadi masalah saat membaca daftar tersimpan.',
                    onRetry: () => ref.invalidate(bookmarkedAnimeProvider),
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _WatchlistHeader extends StatelessWidget {
  const _WatchlistHeader({required this.total});

  final int total;

  @override
  Widget build(BuildContext context) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: AppColors.panel,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: AppColors.border),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'PERSONAL COLLECTION',
            style: TextStyle(
              fontSize: 11,
              fontWeight: FontWeight.w800,
              letterSpacing: 1.5,
              color: AppColors.brand400,
            ),
          ),
          const SizedBox(height: 6),
          Text(
            'My Watchlist',
            style: Theme.of(context).textTheme.headlineSmall,
          ),
          const SizedBox(height: 6),
          const Text(
            'Anime titles saved to your personal queue.',
            style: TextStyle(fontSize: 12, color: AppColors.textSecondary),
          ),
          const SizedBox(height: 8),
          Text(
            'Total $total anime saved.',
            style: const TextStyle(
              fontSize: 11,
              color: AppColors.textSecondary,
            ),
          ),
          const SizedBox(height: 14),
          SizedBox(
            height: 40,
            child: ElevatedButton(
              onPressed: () => context.go('/catalog'),
              style: ElevatedButton.styleFrom(
                backgroundColor: AppColors.brand,
                foregroundColor: Colors.white,
                elevation: 0,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(10),
                ),
                textStyle: const TextStyle(
                  fontSize: 12,
                  fontWeight: FontWeight.w700,
                ),
              ),
              child: const Text('Browse More Anime'),
            ),
          ),
        ],
      ),
    );
  }
}
