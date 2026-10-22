<?php
$file = 'C:/laragon/www/Classy-One-Mobile/lib/services/auth_service.dart';
$content = file_get_contents($file);

// Replace login catch block
$oldLoginCatch = "    } catch (e) {
      return {'success': false, 'message': 'Connection error: \$e'};
    }
  }";

$newLoginCatch = "    } catch (e) {
      // Tentative automatique sur l'URL de secours
      try {
        final fallbackUrl = Uri.parse('\${AppConstants.fallbackUrl}/login');
        final response = await http.post(
          fallbackUrl,
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
          },
          body: jsonEncode({
            'matricule': matricule,
            'password': password,
            'fcmToken': fcmToken,
          }),
        ).timeout(const Duration(seconds: 5));

        AppConstants.switchUrl();

        if (response.statusCode == 200) {
          final data = jsonDecode(response.body);
          if (data['status'] == 'success' || data['success'] == true) {
            final prefs = await SharedPreferences.getInstance();
            if (data['token'] != null) {
              await prefs.setString('auth_token', data['token']);
            }
            return {
              'success': true,
              'message': data['message'],
              'student': Student.fromJson(data['student'] ?? data['data']),
            };
          }
          return {'success': false, 'message': data['message'] ?? 'Login failed'};
        }
      } catch (_) {}

      return {
        'success': false,
        'message': 'Impossible de joindre le serveur. Assurez-vous que le serveur Laravel tourne et que le câble USB est branché.',
      };
    }
  }";

$content = str_replace($oldLoginCatch, $newLoginCatch, $content);

// Replace parentLogin catch block
$oldParentCatch = "    } catch (e) {
      return {'success': false, 'message': 'Erreur de connexion : \$e'};
    }
  }

  Future<Map<String, dynamic>> updateParentFcmToken";

$newParentCatch = "    } catch (e) {
      // Tentative automatique sur l'URL de secours
      try {
        final fallbackUrl = Uri.parse('\${AppConstants.fallbackUrl}/parent/login');
        final response = await http.post(
          fallbackUrl,
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
          },
          body: jsonEncode({
            'email': email,
            'password': password,
            if (fcmToken.isNotEmpty) 'fcmToken': fcmToken,
          }),
        ).timeout(const Duration(seconds: 5));

        AppConstants.switchUrl();

        final data = jsonDecode(response.body);
        if (response.statusCode == 200 && (data['status'] == 'success' || data['success'] == true)) {
          final prefs = await SharedPreferences.getInstance();
          if (data['token'] != null) {
            await prefs.setString('auth_token', data['token']);
            await prefs.setString('user_role', 'parent');
          }
          if (data['parent'] != null) {
            await prefs.setString('parent_name', data['parent']['name'] ?? '');
            await prefs.setString('parent_email', data['parent']['email'] ?? '');
            await prefs.setString('parent_phone', data['parent']['phone'] ?? '');
          }
          return {'success': true, 'data': data};
        }
      } catch (_) {}

      return {
        'success': false,
        'message': 'Impossible de joindre le serveur. Assurez-vous que le serveur Laravel tourne et que le câble USB est branché.',
      };
    }
  }

  Future<Map<String, dynamic>> updateParentFcmToken";

$content = str_replace($oldParentCatch, $newParentCatch, $content);

file_put_contents($file, $content);
echo "AUTH_SERVICE_UPDATED";
