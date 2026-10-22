<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SecureFileUploadService
{
    /**
     * Whitelist of allowed extensions and their strictly verified MIME types.
     */
    protected const ALLOWED_MIME_MAP = [
        'pdf'  => ['application/pdf'],
        'doc'  => ['application/msword', 'application/vnd.ms-office'],
        'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip'],
        'jpg'  => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'png'  => ['image/png'],
        'webp' => ['image/webp'],
    ];

    /**
     * Dangerous extensions that are strictly rejected under any circumstances.
     */
    protected const DANGEROUS_EXTENSIONS = [
        'php', 'php3', 'php4', 'php5', 'phtml', 'phar', 'sh', 'bash', 'exe',
        'bat', 'cmd', 'js', 'vbs', 'scr', 'jar', 'svg', 'html', 'htm', 'cgi', 'pl'
    ];

    /**
     * Maximum allowed file size in bytes (Default: 5 MB).
     */
    protected int $maxSizeBytes;

    public function __construct(int $maxSizeMb = 5)
    {
        $this->maxSizeBytes = $maxSizeMb * 1024 * 1024;
    }

    /**
     * Validate and inspect uploaded file deeply.
     *
     * @throws \InvalidArgumentException
     */
    public function validateFile(UploadedFile $file, array $allowedExtensions = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png']): array
    {
        // 1. Check upload error
        if (!$file->isValid()) {
            throw new \InvalidArgumentException("Échec de téléchargement du fichier : " . $file->getErrorMessage());
        }

        // 2. Strict size check
        if ($file->getSize() > $this->maxSizeBytes) {
            $maxMb = round($this->maxSizeBytes / (1024 * 1024), 1);
            throw new \InvalidArgumentException("Le fichier dépasse la taille maximale autorisée de {$maxMb} Mo.");
        }

        // 3. Inspect filename for dangerous patterns (null bytes, path traversal, multi-extensions)
        $clientOriginalName = $file->getClientOriginalName();
        if (str_contains($clientOriginalName, "\0") || str_contains($clientOriginalName, '../') || str_contains($clientOriginalName, '..\\')) {
            throw new \InvalidArgumentException("Nom de fichier suspect ou malveillant détecté.");
        }

        $rawExtension = strtolower(pathinfo($clientOriginalName, PATHINFO_EXTENSION));
        if (empty($rawExtension) || in_array($rawExtension, self::DANGEROUS_EXTENSIONS, true)) {
            throw new \InvalidArgumentException("Type d'extension non autorisée ou dangereuse : {$rawExtension}.");
        }

        // Check for double extension attacks (e.g., invoice.php.pdf)
        $parts = explode('.', strtolower($clientOriginalName));
        if (count($parts) > 2) {
            foreach (array_slice($parts, 1, -1) as $intermediateExt) {
                if (in_array($intermediateExt, self::DANGEROUS_EXTENSIONS, true)) {
                    throw new \InvalidArgumentException("Attaque par double extension détectée ({$intermediateExt}).");
                }
            }
        }

        // Whitelist check
        if (!in_array($rawExtension, $allowedExtensions, true)) {
            $allowedList = implode(', ', $allowedExtensions);
            throw new \InvalidArgumentException("Extension .{$rawExtension} non autorisée. Formats acceptés : {$allowedList}.");
        }

        // 4. Deep magic-bytes inspection using finfo (bypassing client-provided MIME type)
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $realMime = finfo_file($finfo, $file->getRealPath());
        finfo_close($finfo);

        $validMimesForExt = self::ALLOWED_MIME_MAP[$rawExtension] ?? [];
        if (!in_array($realMime, $validMimesForExt, true)) {
            throw new \InvalidArgumentException("Le contenu réel du fichier (MIME: {$realMime}) ne correspond pas à l'extension déclarée (.{$rawExtension}).");
        }

        return [
            'original_name' => $clientOriginalName,
            'extension' => $rawExtension,
            'mime_type' => $realMime,
            'size' => $file->getSize(),
        ];
    }

    /**
     * Store file securely outside the public web root (in storage/app/private_documents).
     */
    public function storeSecurely(UploadedFile $file, string $subfolder = 'documents'): string
    {
        $inspection = $this->validateFile($file);

        // Generate cryptographically secure random filename
        $secureFilename = Str::random(40) . '.' . $inspection['extension'];
        $directory = 'private_documents/' . trim($subfolder, '/');

        // Store directly in local private disk (storage/app)
        $path = $file->storeAs($directory, $secureFilename, 'local');

        return $path;
    }

    /**
     * Generate an HMAC signed temporary download URL.
     */
    public function generateSignedDownloadUrl(string $storagePath, int $validMinutes = 30): string
    {
        return URL::temporarySignedRoute(
            'secure.file.stream',
            now()->addMinutes($validMinutes),
            ['file_token' => base64_encode($storagePath)]
        );
    }

    /**
     * Stream a private file securely with security headers.
     */
    public function streamSecureFile(string $storagePath, ?string $downloadName = null): StreamedResponse
    {
        if (!Storage::disk('local')->exists($storagePath)) {
            abort(404, "Document introuvable ou archivé.");
        }

        $mime = Storage::disk('local')->mimeType($storagePath) ?: 'application/octet-stream';
        $filename = $downloadName ?: basename($storagePath);

        return Storage::disk('local')->response($storagePath, $filename, [
            'Content-Type' => $mime,
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'DENY',
            'Cache-Control' => 'private, no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }
}
