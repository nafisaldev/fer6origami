import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:google_fonts/google_fonts.dart';

import 'package:animestream_mobile/core/constants/app_colors.dart';
import 'package:animestream_mobile/core/theme/app_theme.dart';

void main() {
  TestWidgetsFlutterBinding.ensureInitialized();
  GoogleFonts.config.allowRuntimeFetching = false;

  test('Fer6origami design tokens are applied', () {
    expect(AppColors.brand, const Color(0xFFE53535));
    expect(AppColors.background, const Color(0xFF090A0F));
    expect(AppColors.panel, const Color(0xFF11131B));
    expect(AppTheme.darkTheme.scaffoldBackgroundColor, AppColors.background);
    expect(AppTheme.darkTheme.colorScheme.primary, AppColors.brand);
  });
}
