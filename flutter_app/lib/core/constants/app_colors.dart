import 'package:flutter/material.dart';

/// Token warna websites Fer6origami.
///
/// Nama lama tetap dipertahankan sebagai alias agar seluruh kode existing
/// otomatis mengikuti palet website tanpa perlu dihapus atau duplikat.
class AppColors {
  static const background = Color(0xFF090A0F);
  static const panel = Color(0xFF11131B);
  static const card = Color(0xFF161924);
  static const overlay = Color(0xFF1D2230);

  static const brand = Color(0xFFE53535);
  static const brand300 = Color(0xFFFFA0A0);
  static const brand400 = Color(0xFFFF6666);
  static const brand600 = Color(0xFFCC2222);

  static const tide300 = Color(0xFF67E8F9);
  static const tide400 = Color(0xFF22D3EE);
  static const tide500 = Color(0xFF06B6D4);

  static const border = Color(0x14FFFFFF);
  static const borderStrong = Color(0x1AFFFFFF);
  static const muted = Color(0xFF8490A5);

  static const textPrimary = Color(0xFFF1F5F9);
  static const textSecondary = Color(0xFF94A3B8);

  static const success = Color(0xFF34D399);
  static const warning = Color(0xFFFBBF24);
  static const danger = Color(0xFFFB7185);

  static const free = Color(0xFFCBD5E1);
  static const premium = brand400;
  static const vip = warning;

  // Alias kompatibilitas dengan kode Flutter yang sudah ada.
  static const abyss = background;
  static const night = panel;
  static const slate = card;
  static const ember = brand;
  static const emberSoft = brand400;
  static const cyan = tide400;
  static const stroke = border;
}
