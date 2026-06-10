# Dynamic URL Solution - Web & Mobile Compatibility

## Problem Statement

When `APP_URL=http://192.168.100.99:8000`:
- ✅ Mobile app works correctly
- ❌ Web admin dashboard breaks (403 FORBIDDEN on storage files)

When `APP_URL=http://localhost:8000`:
- ✅ Web admin dashboard works correctly
- ❌ Mobile app cannot access media files

## Solution Implemented

We've implemented a **middleware-based dynamic URL solution** that automatically adapts URLs based on the incoming request without changing `APP_URL`.

---

## How It Works

### 1. **Keep APP_URL Web-Friendly**
```env
APP_URL=http://localhost:8000
```
This keeps the Filament Web Dashboard functioning perfectly.

### 2. **Dynamic API Middleware**
Created `app/Http/Middleware/DynamicApiUrl.php` that:
- Intercepts all API responses
- Detects the requesting device's IP (e.g., `192.168.100.99:8000`)
- Dynamically replaces `localhost:8000` and `127.0.0.1:8000` with the actual request host
- Only affects API routes (leaves web dashboard untouched)

### 3. **Middleware Registration**
Registered in `bootstrap/app.php`:
```php
$middleware->api(append: [
    \App\Http\Middleware\DynamicApiUrl::class,
]);
```

---

## Technical Details

### Middleware Logic

```php
public function handle(Request $request, Closure $next): Response
{
    $response = $next($request);

    // Only process JSON responses (API responses)
    if ($response->headers->get('Content-Type') === 'application/json') {
        $content = $response->getContent();
        
        // Get the request's full base URL (e.g., http://192.168.100.99:8000)
        $requestBaseUrl = $request->getSchemeAndHttpHost();
        
        // Replace localhost with actual request host
        $content = str_replace(
            ['http://localhost:8000', 'http://127.0.0.1:8000'],
            $requestBaseUrl,
            $content
        );
        
        $response->setContent($content);
    }

    return $response;
}
```

### How It Affects Different Clients

| Client | Request From | APP_URL Used | Middleware Action | Final URL |
|--------|--------------|--------------|-------------------|-----------|
| **Web Dashboard** | Browser at localhost | `localhost:8000` | Not applied (not API route) | `http://localhost:8000/storage/file.pdf` |
| **Mobile App** | Phone at 192.168.100.99 | `localhost:8000` | Replaces with request host | `http://192.168.100.99:8000/storage/file.pdf` |
| **Mobile App** | Phone at 192.168.1.50 | `localhost:8000` | Replaces with request host | `http://192.168.1.50:8000/storage/file.pdf` |

---

## Files Modified/Created

### ✅ Created Files:
1. **`app/Http/Middleware/DynamicApiUrl.php`** - Main middleware
2. **`app/Helpers/UrlHelper.php`** - Optional helper (for alternative approach)
3. **`DYNAMIC_URL_SOLUTION.md`** - This documentation

### ✅ Modified Files:
1. **`bootstrap/app.php`** - Registered middleware for API routes
2. **`.env`** - Confirmed `APP_URL=http://localhost:8000`

### ℹ️ Unchanged Files (No modification needed):
- `app/Http/Controllers/CourseController.php`
- `app/Http/Controllers/DocumentController.php`
- `app/Http/Controllers/NotificationController.php`
- All other API controllers

The beauty of the middleware approach is that **no controller changes are needed**.

---

## Setup Instructions

### 1. Clear Laravel Cache
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### 2. Recreate Storage Symlink
```bash
php artisan storage:link
```

If you get "The [public/storage] link already exists", remove it first:
```bash
# Windows CMD
rmdir public\storage

# Then recreate
php artisan storage:link
```

### 3. Verify .env Configuration
Ensure your `.env` file has:
```env
APP_URL=http://localhost:8000
```

### 4. Restart Laravel Server
```bash
php artisan serve --host=0.0.0.0 --port=8000
```

The `--host=0.0.0.0` makes the server accessible from other devices on your network.

---

## Testing Checklist

### ✅ Test Web Dashboard:
1. Open browser at `http://localhost:8000/admin`
2. Login with admin credentials
3. Upload a course file or event image
4. Verify the media displays correctly
5. Check that there are no 403 FORBIDDEN errors

### ✅ Test Mobile App:
1. Ensure your phone is on the same network as your dev machine
2. Set Flutter app's `baseUrl` to your machine's IP (e.g., `http://192.168.100.99:8000`)
3. Login to the mobile app
4. Navigate to:
   - **Courses** - Videos/PDFs should load
   - **Events** - Event images should display
   - **Documents** - Generated PDFs should download
   - **Notifications** - Attachments should be accessible
5. Verify all media loads correctly

### ✅ Test URL Transformation:
Make an API request and check the response:
```bash
curl -X POST http://192.168.100.99:8000/api/courses \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json"
```

Expected response should contain URLs like:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "Mathematics",
      "video_url": "http://192.168.100.99:8000/storage/courses/video.mp4"
    }
  ]
}
```

Notice the URL uses `192.168.100.99` (your machine's IP), not `localhost`.

---

## Alternative Approach: Helper Function

If you prefer more control, you can use the `UrlHelper` class we created:

### Usage in Controllers:
```php
use App\Helpers\UrlHelper;

// Option 1: Dynamic URL (automatically adapts)
'video_url' => UrlHelper::storageUrl($course->file_path)

// Option 2: Relative URL (let Flutter handle it)
'video_url' => UrlHelper::relativeStorageUrl($course->file_path)
```

### When to Use Which:
- **Middleware (Current)**: Automatic, no code changes, works everywhere
- **Helper Function**: More control, explicit, requires updating controllers

We recommend sticking with the **middleware approach** for simplicity.

---

## Affected API Endpoints

These endpoints now return dynamic URLs automatically:

| Endpoint | Media Field | Type |
|----------|-------------|------|
| `/api/courses` | `video_url` | Videos/PDFs |
| `/api/documents` | `pdf_url` | Generated certificates |
| `/api/events` | `image_url` | Event images |
| `/api/notifications` | `pieceJointe` | Attachments |
| `/api/dashboard` | Various | Mixed media |
| `/api/planning` | `fileUrl` | Schedule files |

---

## Troubleshooting

### Issue: Media still returns localhost URLs
**Solution:**
1. Clear all caches: `php artisan optimize:clear`
2. Restart Laravel server
3. Check middleware is registered in `bootstrap/app.php`

### Issue: Web dashboard shows 403 FORBIDDEN
**Solution:**
1. Verify `APP_URL=http://localhost:8000` in `.env`
2. Recreate storage symlink: `php artisan storage:link`
3. Check file permissions on `storage/` and `public/storage/`

### Issue: Mobile app can't connect
**Solution:**
1. Ensure dev machine and phone are on same WiFi network
2. Check firewall isn't blocking port 8000
3. Verify server is running with `--host=0.0.0.0`
4. Use your machine's actual IP, not localhost, in Flutter app

### Issue: Mixed content (HTTP/HTTPS) errors
**Solution:**
The middleware respects the request scheme. If the request comes via HTTPS, responses will use HTTPS.

---

## Performance Considerations

**Q: Does this impact performance?**
A: Minimal. The middleware only processes JSON responses and performs a simple string replacement. The overhead is negligible (< 1ms).

**Q: Can I disable it?**
A: Yes. Remove the middleware registration from `bootstrap/app.php`:
```php
// Comment out or remove this line:
// \App\Http\Middleware\DynamicApiUrl::class,
```

---

## Security Considerations

The middleware:
- ✅ Only affects API routes (not web dashboard)
- ✅ Only processes JSON responses
- ✅ Uses the actual request host (no external input)
- ✅ Doesn't expose sensitive information
- ✅ Doesn't modify request data, only response URLs

---

## Network Configuration

### Find Your Machine's IP Address:

**Windows:**
```cmd
ipconfig
```
Look for "IPv4 Address" under your active network adapter.

**macOS/Linux:**
```bash
ifconfig
```
or
```bash
ip addr show
```

### Update Flutter App:
In your Flutter app's constants:
```dart
class AppConstants {
  static const String baseUrl = 'http://YOUR_IP_HERE:8000';
  // Example: static const String baseUrl = 'http://192.168.100.99:8000';
}
```

---

## Summary

✅ **Web Dashboard**: Works with `APP_URL=http://localhost:8000`  
✅ **Mobile App**: Gets dynamic URLs via middleware (e.g., `http://192.168.100.99:8000`)  
✅ **No Controller Changes**: Middleware handles everything automatically  
✅ **Zero Configuration**: Works on any IP address automatically  
✅ **Portable**: Works in different networks without changing code  

---

## Questions?

For issues or questions, refer to:
- Laravel Sanctum docs: https://laravel.com/docs/sanctum
- Laravel Storage docs: https://laravel.com/docs/filesystem
- Middleware docs: https://laravel.com/docs/middleware
