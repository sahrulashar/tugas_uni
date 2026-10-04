<?php

declare(strict_types=1);

namespace App\Libraries\Session;

use CodeIgniter\Session\Exceptions\SessionException;
use CodeIgniter\Session\Handlers\FileHandler as BaseFileHandler;

/**
 * Windows & OneDrive resilient Session FileHandler.
 * Automatically clears Read-Only directory attribute on Windows OneDrive folders,
 * and performs actual file-write verification to avoid false-positive is_writable() failures.
 */
class FileHandler extends BaseFileHandler
{
    /**
     * Re-initialize existing session, or creates a new one.
     *
     * @param string $path The path where to store/retrieve the session.
     * @param string $name The session name.
     *
     * @throws SessionException
     */
    public function open($path, $name): bool
    {
        if (! is_dir($path) && ! @mkdir($path, 0700, true)) {
            throw SessionException::forInvalidSavePath($this->savePath);
        }

        $writable = is_writable($path);
        if (! $writable) {
            // Attempt to remove read-only attribute caused by Windows / OneDrive syncing
            @chmod($path, 0777);
            if (DIRECTORY_SEPARATOR === '\\' && realpath($path)) {
                @exec('attrib -r "' . realpath($path) . '"');
            }
            clearstatcache(true, $path);
            $writable = is_writable($path);
        }

        // On Windows NTFS, directories often retain the FILE_ATTRIBUTE_READONLY flag
        // (used by Explorer / OneDrive for special folder state), which makes PHP's
        // is_writable() return false even when creating and modifying files is fully permitted.
        if (! $writable) {
            $testFile = rtrim($path, '/\\') . '/.ci_perm_test_' . bin2hex(random_bytes(4));
            if (@file_put_contents($testFile, '1') !== false) {
                @unlink($testFile);
                $writable = true;
            }
        }

        if (! $writable) {
            throw SessionException::forWriteProtectedSavePath($this->savePath);
        }

        $this->savePath = $path;
        $this->filePath = $this->savePath . '/' . $name . ($this->matchIP ? md5($this->ipAddress) : '');

        return true;
    }
}
