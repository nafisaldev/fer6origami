import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/utils/debouncer.dart';
import '../../../shared/widgets/app_content_container.dart';
import '../../../shared/widgets/app_error_state.dart';
import '../../../shared/widgets/empty_state.dart';
import '../../../shared/widgets/loading_skeleton.dart';
import '../../../shared/widgets/search_field.dart';
import '../controllers/search_controller.dart';

class SearchPage extends ConsumerStatefulWidget {
  const SearchPage({super.key});

  @override
  ConsumerState<SearchPage> createState() => _SearchPageState();
}

class _SearchPageState extends ConsumerState<SearchPage> {
  late final TextEditingController _controller;
  late final Debouncer _debouncer;

  @override
  void initState() {
    super.initState();
    _controller = TextEditingController();
    _debouncer = Debouncer(delay: const Duration(milliseconds: 360));
  }

  @override
  void dispose() {
    _controller.dispose();
    _debouncer.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final results = ref.watch(searchResultsProvider);
    final query = ref.watch(searchQueryProvider);

    return Scaffold(
      body: AppContentContainer(
        includeVerticalPadding: false,
        child: Padding(
          padding: const EdgeInsets.fromLTRB(0, 16, 0, 24),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              _SearchHeader(
                count: query.trim().isEmpty
                    ? null
                    : results.valueOrNull?.length,
              ),
              const SizedBox(height: 16),
              SearchField(
                controller: _controller,
                onChanged: (value) {
                  setState(() {});
                  _debouncer(() {
                    ref.read(searchQueryProvider.notifier).state = value;
                  });
                },
              ),
              const SizedBox(height: 20),
              if (query.trim().isNotEmpty)
                Padding(
                  padding: const EdgeInsets.only(bottom: 12),
                  child: Text(
                    'Results for "$query"',
                    style: Theme.of(context).textTheme.titleMedium,
                  ),
                ),
              Expanded(
                child: query.trim().isEmpty
                    ? const EmptyState(
                        title: 'Mulai pencarian',
                        message:
                            'Masukkan judul, genre, atau studio untuk menemukan anime.',
                        icon: Icons.search_rounded,
                      )
                    : results.when(
                        data: (items) {
                          if (items.isEmpty) {
                            return const EmptyState(
                              title: 'Tidak ada hasil',
                              message:
                                  'Coba keyword lain atau gunakan pencarian yang lebih singkat.',
                              icon: Icons.travel_explore_rounded,
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
                          gridDelegate:
                              SliverGridDelegateWithFixedCrossAxisCount(
                                crossAxisCount: AppSpacing.columns(
                                  context,
                                  max: 4,
                                ),
                                crossAxisSpacing: 12,
                                mainAxisSpacing: 12,
                                childAspectRatio: 0.62,
                              ),
                          itemBuilder: (_, __) =>
                              const LoadingSkeleton(height: 250, radius: 12),
                        ),
                        error: (_, __) => AppErrorState(
                          title: 'Pencarian gagal',
                          message: 'Ada masalah saat memuat hasil pencarian.',
                          onRetry: () => ref.invalidate(searchResultsProvider),
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

class _SearchHeader extends StatelessWidget {
  const _SearchHeader({this.count});

  final int? count;

  @override
  Widget build(BuildContext context) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: const Color(0xFF11131B),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: const Color(0x14FFFFFF)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'SEARCH LIBRARY',
            style: TextStyle(
              fontSize: 11,
              fontWeight: FontWeight.w800,
              letterSpacing: 1.5,
              color: Color(0xFFFF6666),
            ),
          ),
          const SizedBox(height: 6),
          Text(
            'Find Your Next Anime',
            style: Theme.of(context).textTheme.headlineSmall,
          ),
          const SizedBox(height: 6),
          const Text(
            'Cari judul, genre, atau studio dari katalog Fer6origami.',
            style: TextStyle(fontSize: 12, color: Color(0xFF94A3B8)),
          ),
          if (count != null) ...[
            const SizedBox(height: 8),
            Text(
              '$count titles found',
              style: const TextStyle(fontSize: 11, color: Color(0xFF94A3B8)),
            ),
          ],
        ],
      ),
    );
  }
}
