<?php

namespace App\Utilities;

/**
 * Pure PHP ZIP archive creator (no ZipArchive extension required)
 */
class ZipHelper
{
    /**
     * Create a zip archive from an array of file paths
     */
    public static function create($zipPath, $files)
    {
        $fp = fopen($zipPath, 'wb');
        if (!$fp) return false;

        $centralDir = '';
        $offset = 0;

        foreach ($files as $file) {
            if (!file_exists($file)) continue;

            $filename = basename($file);
            $content = file_get_contents($file);
            $filesize = filesize($file);
            $fileCrc32 = crc32($content);

            // Local file header
            $localHeader = "\x50\x4b\x03\x04" // Local file header signature
                . "\x14\x00" // Version needed to extract (2.0)
                . "\x00\x00" // General purpose bit flag
                . "\x00\x00" // Compression method (0 = stored)
                . "\x00\x00" // Last mod file time
                . "\x00\x00" // Last mod file date
                . pack('V', $fileCrc32) // CRC-32
                . pack('V', $filesize) // Compressed size
                . pack('V', $filesize) // Uncompressed size
                . pack('v', strlen($filename)) // Filename length
                . "\x00\x00" // Extra field length
                . $filename; // Filename

            fwrite($fp, $localHeader);
            fwrite($fp, $content);

            // Central directory file header
            $centralHeader = "\x50\x4b\x01\x02" // Central directory file header signature
                . "\x14\x00" // Version made by
                . "\x14\x00" // Version needed to extract
                . "\x00\x00" // General purpose bit flag
                . "\x00\x00" // Compression method
                . "\x00\x00" // Last mod file time
                . "\x00\x00" // Last mod file date
                . pack('V', $fileCrc32) // CRC-32
                . pack('V', $filesize) // Compressed size
                . pack('V', $filesize) // Uncompressed size
                . pack('v', strlen($filename)) // Filename length
                . "\x00\x00" // Extra field length
                . "\x00\x00" // File comment length
                . "\x00\x00" // Disk number start
                . "\x00\x00" // Internal file attributes
                . pack('V', 0) // External file attributes
                . pack('V', $offset) // Relative offset of local header
                . $filename; // Filename

            $centralDir .= $centralHeader;
            $offset += strlen($localHeader) + $filesize;
        }

        // End of central directory record
        $centralDirSize = strlen($centralDir);
        $endOfCentralDir = "\x50\x4b\x05\x06" // End of central directory signature
            . "\x00\x00" // Number of this disk
            . "\x00\x00" // Number of disk with central directory
            . pack('v', count($files)) // Number of central directory records on this disk
            . pack('v', count($files)) // Total number of central directory records
            . pack('V', $centralDirSize) // Size of central directory
            . pack('V', $offset) // Offset of start of central directory
            . "\x00\x00"; // ZIP file comment length

        fwrite($fp, $centralDir);
        fwrite($fp, $endOfCentralDir);
        fclose($fp);

        return true;
    }
}
