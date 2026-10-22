<?php

$widgetTestPath = 'C:\\laragon\\www\\Classy-One-Mobile\\test\\widget_test.dart';

$content = <<<'DART'
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:provider/provider.dart';

import 'package:flutter_application_1/providers/theme_provider.dart';
import 'package:flutter_application_1/providers/settings_provider.dart';
import 'package:flutter_application_1/providers/payment_provider.dart';
import 'package:flutter_application_1/providers/notification_provider.dart';
import 'package:flutter_application_1/providers/network_provider.dart';
import 'package:flutter_application_1/main.dart';

void main() {
  testWidgets('App renders MaterialApp smoke test', (WidgetTester tester) async {
    await tester.pumpWidget(
      MultiProvider(
        providers: [
          ChangeNotifierProvider(create: (_) => ThemeProvider()),
          ChangeNotifierProvider(create: (_) => SettingsProvider()),
          ChangeNotifierProvider(create: (_) => PaymentProvider()),
          ChangeNotifierProvider(create: (_) => NotificationProvider()),
          ChangeNotifierProvider(create: (_) => NetworkProvider()),
        ],
        child: const MyApp(),
      ),
    );

    expect(find.byType(MaterialApp), findsOneWidget);
  });
}
DART;

file_put_contents($widgetTestPath, $content);
echo "Updated widget_test.dart with all providers!\n";
