import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

import '../constants/app_colors.dart';

class AppTheme {
  static const _radius = 12.0;
  static const _radiusLarge = 16.0;

  static ThemeData get darkTheme {
    const colorScheme = ColorScheme.dark(
      primary: AppColors.brand,
      onPrimary: Colors.white,
      secondary: AppColors.tide400,
      onSecondary: Colors.white,
      surface: AppColors.panel,
      onSurface: AppColors.textPrimary,
      error: AppColors.danger,
      onError: Colors.white,
    );

    final textTheme = TextTheme(
      displayLarge: _display(36, FontWeight.w800),
      displayMedium: _display(30, FontWeight.w800),
      displaySmall: _display(26, FontWeight.w800),
      headlineMedium: _display(23, FontWeight.w700),
      headlineSmall: _display(20, FontWeight.w700),
      titleLarge: _body(18, FontWeight.w700, AppColors.textPrimary),
      titleMedium: _body(15, FontWeight.w700, AppColors.textPrimary),
      titleSmall: _body(13, FontWeight.w700, AppColors.textPrimary),
      bodyLarge: _body(
        14,
        FontWeight.w500,
        AppColors.textPrimary,
        height: 1.55,
      ),
      bodyMedium: _body(
        13,
        FontWeight.w400,
        AppColors.textSecondary,
        height: 1.55,
      ),
      bodySmall: _body(
        11,
        FontWeight.w500,
        AppColors.textSecondary,
        height: 1.45,
      ),
      labelLarge: _body(12, FontWeight.w700, AppColors.textPrimary),
      labelMedium: _body(11, FontWeight.w700, AppColors.textSecondary),
      labelSmall: _body(10, FontWeight.w700, AppColors.textSecondary),
    );

    return ThemeData(
      useMaterial3: true,
      brightness: Brightness.dark,
      colorScheme: colorScheme,
      scaffoldBackgroundColor: AppColors.background,
      canvasColor: AppColors.background,
      splashFactory: InkRipple.splashFactory,
      visualDensity: VisualDensity.standard,
      textTheme: textTheme,
      primaryTextTheme: textTheme,
      appBarTheme: const AppBarTheme(
        backgroundColor: AppColors.background,
        foregroundColor: AppColors.textPrimary,
        elevation: 0,
        scrolledUnderElevation: 0,
        centerTitle: false,
        surfaceTintColor: Colors.transparent,
        iconTheme: IconThemeData(color: AppColors.textPrimary, size: 20),
        titleTextStyle: TextStyle(
          fontFamily: 'Syne',
          fontSize: 18,
          fontWeight: FontWeight.w700,
          color: AppColors.textPrimary,
        ),
      ),
      cardTheme: CardThemeData(
        margin: EdgeInsets.zero,
        color: AppColors.card,
        elevation: 0,
        shadowColor: Colors.transparent,
        surfaceTintColor: Colors.transparent,
        clipBehavior: Clip.antiAlias,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(_radius),
          side: const BorderSide(color: AppColors.border),
        ),
      ),
      chipTheme: ChipThemeData(
        backgroundColor: const Color(0x0AFFFFFF),
        selectedColor: const Color(0x24E53535),
        disabledColor: Colors.transparent,
        side: const BorderSide(color: AppColors.border),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(999)),
        labelStyle: _body(11, FontWeight.w700, AppColors.textPrimary),
        secondaryLabelStyle: _body(11, FontWeight.w700, AppColors.textPrimary),
        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
        showCheckmark: false,
      ),
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: AppColors.card,
        contentPadding: const EdgeInsets.symmetric(
          horizontal: 14,
          vertical: 14,
        ),
        hintStyle: _body(13, FontWeight.w400, AppColors.textSecondary),
        prefixIconColor: AppColors.textSecondary,
        suffixIconColor: AppColors.textSecondary,
        border: _outlineBorder(AppColors.border),
        enabledBorder: _outlineBorder(AppColors.border),
        focusedBorder: _outlineBorder(AppColors.brand400),
        errorBorder: _outlineBorder(AppColors.danger),
        focusedErrorBorder: _outlineBorder(AppColors.danger),
      ),
      navigationBarTheme: NavigationBarThemeData(
        height: 64,
        backgroundColor: AppColors.background,
        surfaceTintColor: Colors.transparent,
        indicatorColor: Colors.transparent,
        overlayColor: WidgetStateProperty.all(const Color(0x14E53535)),
        labelBehavior: NavigationDestinationLabelBehavior.alwaysShow,
        iconTheme: WidgetStateProperty.resolveWith((states) {
          if (states.contains(WidgetState.selected)) {
            return const IconThemeData(color: AppColors.brand400, size: 20);
          }
          return const IconThemeData(color: AppColors.textSecondary, size: 20);
        }),
        labelTextStyle: WidgetStateProperty.resolveWith((states) {
          return _body(
            10,
            states.contains(WidgetState.selected)
                ? FontWeight.w800
                : FontWeight.w600,
            states.contains(WidgetState.selected)
                ? AppColors.brand400
                : AppColors.textSecondary,
          );
        }),
      ),
      bottomSheetTheme: const BottomSheetThemeData(
        backgroundColor: AppColors.panel,
        modalBackgroundColor: AppColors.panel,
        surfaceTintColor: Colors.transparent,
      ),
      dialogTheme: DialogThemeData(
        backgroundColor: AppColors.panel,
        surfaceTintColor: Colors.transparent,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(_radiusLarge),
          side: const BorderSide(color: AppColors.borderStrong),
        ),
        titleTextStyle: _display(19, FontWeight.w700),
        contentTextStyle: _body(13, FontWeight.w400, AppColors.textSecondary),
      ),
      dividerColor: AppColors.border,
      dividerTheme: const DividerThemeData(
        color: AppColors.border,
        thickness: 1,
        space: 1,
      ),
      switchTheme: SwitchThemeData(
        thumbColor: WidgetStateProperty.resolveWith((states) {
          return states.contains(WidgetState.selected)
              ? Colors.white
              : AppColors.textSecondary;
        }),
        trackColor: WidgetStateProperty.resolveWith((states) {
          return states.contains(WidgetState.selected)
              ? AppColors.brand
              : AppColors.overlay;
        }),
      ),
      textSelectionTheme: const TextSelectionThemeData(
        cursorColor: AppColors.brand,
        selectionColor: Color(0x66E53535),
        selectionHandleColor: AppColors.brand,
      ),
      progressIndicatorTheme: const ProgressIndicatorThemeData(
        color: AppColors.brand,
        linearTrackColor: Color(0x14FFFFFF),
        circularTrackColor: Color(0x14FFFFFF),
      ),
      snackBarTheme: SnackBarThemeData(
        backgroundColor: AppColors.panel,
        contentTextStyle: _body(12, FontWeight.w600, AppColors.textPrimary),
        behavior: SnackBarBehavior.floating,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(_radius),
        ),
      ),
      pageTransitionsTheme: const PageTransitionsTheme(
        builders: {
          TargetPlatform.android: FadeForwardsPageTransitionsBuilder(),
          TargetPlatform.iOS: CupertinoPageTransitionsBuilder(),
        },
      ),
    );
  }

  static ThemeData get lightTheme {
    const colorScheme = ColorScheme.light(
      primary: AppColors.brand,
      onPrimary: Colors.white,
      surface: Colors.white,
      onSurface: Color(0xFF11131B),
    );

    return ThemeData(
      useMaterial3: true,
      brightness: Brightness.light,
      colorScheme: colorScheme,
      scaffoldBackgroundColor: const Color(0xFFF8FAFC),
      textTheme: TextTheme(
        displaySmall: _display(26, FontWeight.w800, const Color(0xFF11131B)),
        headlineSmall: _display(20, FontWeight.w700, const Color(0xFF11131B)),
        titleLarge: _body(18, FontWeight.w700, const Color(0xFF11131B)),
        titleMedium: _body(15, FontWeight.w700, const Color(0xFF11131B)),
        bodyMedium: _body(13, FontWeight.w400, const Color(0xFF475569)),
      ),
      cardTheme: CardThemeData(
        color: Colors.white,
        elevation: 0,
        surfaceTintColor: Colors.transparent,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(_radius),
          side: const BorderSide(color: Color(0x14000000)),
        ),
      ),
    );
  }

  static TextStyle _display(
    double size,
    FontWeight weight, [
    Color color = AppColors.textPrimary,
  ]) {
    return GoogleFonts.syne(
      fontSize: size,
      height: 1.15,
      fontWeight: weight,
      color: color,
    );
  }

  static TextStyle _body(
    double size,
    FontWeight weight,
    Color color, {
    double height = 1.3,
  }) {
    return GoogleFonts.manrope(
      fontSize: size,
      height: height,
      fontWeight: weight,
      color: color,
    );
  }

  static OutlineInputBorder _outlineBorder(Color color) {
    return OutlineInputBorder(
      borderRadius: BorderRadius.circular(_radius),
      borderSide: BorderSide(color: color),
    );
  }
}
