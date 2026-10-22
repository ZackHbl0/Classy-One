<?php

$loginPath = 'C:\\laragon\\www\\Classy-One-Mobile\\lib\\screens\\login_page.dart';
$lines = file($loginPath);

$newLines = [];
$skipping = false;

for ($i = 0; $i < count($lines); $i++) {
    $line = $lines[$i];

    if (str_contains($line, 'void _showForgotPasswordDialog() {')) {
        $newLines[] = "  void _showForgotPasswordDialog() {\n";
        $newLines[] = "    Navigator.of(context).push(\n";
        $newLines[] = "      MaterialPageRoute(\n";
        $newLines[] = "        builder: (_) => ForgotPasswordPage(\n";
        $newLines[] = "          initialIdentifier: _matriculeController.text.trim(),\n";
        $newLines[] = "        ),\n";
        $newLines[] = "      ),\n";
        $newLines[] = "    );\n";
        $newLines[] = "  }\n";
        $skipping = true;
        continue;
    }

    if ($skipping) {
        if (str_contains($line, 'Future<void> _login() async {')) {
            $skipping = false;
            $newLines[] = "\n";
            $newLines[] = $line;
        }
        continue;
    }

    $newLines[] = $line;
}

file_put_contents($loginPath, implode('', $newLines));
echo "Successfully fixed login_page.dart lines!\n";
