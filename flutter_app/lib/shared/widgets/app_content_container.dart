import 'package:flutter/material.dart';

import '../models/anime.dart';
import 'anime_card.dart';

/// Padding dan lebar konten publik mengikuti `max-w-7xl px-4 sm:px-6 lg:px-8`.
class AppSpacing {
  const AppSpacing._();

  static const double maxContentWidth = 1280;

  static double horizontal(BuildContext context) {
    final width = MediaQuery.sizeOf(context).width;
    if (width < 640) return 16;
    if (width < 1024) return 24;
    return 32;
  }

  static double vertical(BuildContext context) {
    final width = MediaQuery.sizeOf(context).width;
    return width < 640 ? 16 : 24;
  }

  static int columns(BuildContext context, {int max = 6}) {
    final width = MediaQuery.sizeOf(context).width;
    var columns = 2;
    if (width >= 1280) {
      columns = 6;
    } else if (width >= 1024) {
      columns = 4;
    } else if (width >= 640) {
      columns = 3;
    }
    return columns > max ? max : columns;
  }
}

class AppContentContainer extends StatelessWidget {
  const AppContentContainer({
    super.key,
    required this.child,
    this.includeVerticalPadding = true,
  });

  final Widget child;
  final bool includeVerticalPadding;

  @override
  Widget build(BuildContext context) {
    final horizontal = AppSpacing.horizontal(context);
    final vertical = AppSpacing.vertical(context);

    return Center(
      child: ConstrainedBox(
        constraints: const BoxConstraints(maxWidth: AppSpacing.maxContentWidth),
        child: Padding(
          padding: EdgeInsets.fromLTRB(
            horizontal,
            includeVerticalPadding ? vertical : 0,
            horizontal,
            includeVerticalPadding ? vertical : 0,
          ),
          child: child,
        ),
      ),
    );
  }
}

/// Grid kartu dengan jumlah kolom responsif seperti website:
/// 2 kolom di mobile, 3 di sm, 4 di lg, dan 6 di xl untuk katalog.
class ResponsiveAnimeGrid extends StatelessWidget {
  const ResponsiveAnimeGrid({
    super.key,
    required this.items,
    this.maxColumns = 6,
    this.shrinkWrap = false,
    this.physics,
  });

  final List<Anime> items;
  final int maxColumns;
  final bool shrinkWrap;
  final ScrollPhysics? physics;

  @override
  Widget build(BuildContext context) {
    final columns = AppSpacing.columns(context, max: maxColumns);

    return GridView.builder(
      shrinkWrap: shrinkWrap,
      physics:
          physics ??
          const BouncingScrollPhysics(parent: AlwaysScrollableScrollPhysics()),
      itemCount: items.length,
      gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
        crossAxisCount: columns,
        crossAxisSpacing: 12,
        mainAxisSpacing: 12,
        childAspectRatio: 0.62,
      ),
      itemBuilder: (context, index) => AnimeCard(anime: items[index]),
    );
  }
}
