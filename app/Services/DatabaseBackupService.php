<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class DatabaseBackupService
{
    /**
     * Direktori penyimpanan berkas backup
     */
    protected string $backupDir;

    public function __construct()
    {
        $this->backupDir = storage_path('app/backups');

        if (!File::exists($this->backupDir)) {
            File::makeDirectory($this->backupDir, 0755, true);
        }
    }

    /**
     * Dapatkan path direktori backup
     */
    public function getBackupDir(): string
    {
        return $this->backupDir;
    }

    /**
     * Eksekusi proses backup database menghasilkan file .sql
     *
     * @return array
     * @throws Exception
     */
    public function createBackup(): array
    {
        $timestamp = date('Y-m-d_His');
        $fileName = "backup_ikhlas_{$timestamp}.sql";
        $filePath = $this->backupDir . DIRECTORY_SEPARATOR . $fileName;

        $handle = fopen($filePath, 'w+');
        if (!$handle) {
            throw new Exception("Gagal membuat berkas backup di {$filePath}");
        }

        $connection = config('database.default');
        $driver = config("database.connections.{$connection}.driver");
        $databaseName = config("database.connections.{$connection}.database");

        // Header SQL
        fwrite($handle, "-- ========================================================\n");
        fwrite($handle, "-- IKHLAS SOLUSI - DATABASE BACKUP FILE\n");
        fwrite($handle, "-- Tanggal & Waktu: " . date('Y-m-d H:i:s') . " WIB\n");
        fwrite($handle, "-- Database: {$databaseName}\n");
        fwrite($handle, "-- Driver: {$driver}\n");
        fwrite($handle, "-- ========================================================\n\n");

        if ($driver === 'mysql') {
            fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n");
            fwrite($handle, "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n");
            fwrite($handle, "SET time_zone = \"+07:00\";\n\n");

            $this->dumpMySql($handle);

            fwrite($handle, "\nSET FOREIGN_KEY_CHECKS=1;\n");
        } elseif ($driver === 'sqlite') {
            $this->dumpSqlite($handle);
        } else {
            // Generic PDO fallback
            $this->dumpGenericPdo($handle);
        }

        fwrite($handle, "\n-- ========================================================\n");
        fwrite($handle, "-- SELESAI: Backup Berhasil Dibuat\n");
        fwrite($handle, "-- ========================================================\n");

        fclose($handle);

        $fileSize = filesize($filePath);

        return [
            'file_name' => $fileName,
            'file_path' => $filePath,
            'size_bytes' => $fileSize,
            'size_formatted' => $this->formatBytes($fileSize),
            'created_at' => now(),
        ];
    }

    /**
     * Dump Database MySQL secara terstruktur, cepat & efisien
     */
    protected function dumpMySql($handle): void
    {
        $pdo = DB::connection()->getPdo();
        $tablesStatement = $pdo->query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'");
        $tables = $tablesStatement->fetchAll(\PDO::FETCH_COLUMN);

        foreach ($tables as $table) {
            fwrite($handle, "\n-- --------------------------------------------------------\n");
            fwrite($handle, "-- Struktur Tabel: `{$table}`\n");
            fwrite($handle, "-- --------------------------------------------------------\n");
            fwrite($handle, "DROP TABLE IF EXISTS `{$table}`;\n");

            $createTableStmt = $pdo->query("SHOW CREATE TABLE `{$table}`");
            $createTableRow = $createTableStmt->fetch(\PDO::FETCH_NUM);
            if (!empty($createTableRow[1])) {
                fwrite($handle, $createTableRow[1] . ";\n\n");
            }

            // Stream seluruh baris tabel dalam satu query cepat (tanpa LIMIT OFFSET berulang)
            $rowsStmt = $pdo->query("SELECT * FROM `{$table}`");
            $rowCount = 0;
            $batchSize = 500;
            $valuesList = [];
            $columnNames = null;

            while ($row = $rowsStmt->fetch(\PDO::FETCH_ASSOC)) {
                $rowCount++;
                if ($columnNames === null) {
                    $columnNames = array_map(function ($col) {
                        return "`" . str_replace("`", "``", $col) . "`";
                    }, array_keys($row));
                }

                $rowValues = [];
                foreach ($row as $val) {
                    if (is_null($val)) {
                        $rowValues[] = 'NULL';
                    } elseif (is_numeric($val) && !is_string($val)) {
                        $rowValues[] = $val;
                    } else {
                        $rowValues[] = $pdo->quote($val);
                    }
                }
                $valuesList[] = "  (" . implode(', ', $rowValues) . ")";

                // Tulis per batch 500 baris ke file
                if (count($valuesList) >= $batchSize) {
                    $insertHeader = "INSERT INTO `{$table}` (" . implode(', ', $columnNames) . ") VALUES\n";
                    fwrite($handle, $insertHeader . implode(",\n", $valuesList) . ";\n");
                    $valuesList = [];
                }
            }

            // Tulis sisa baris batch terakhir jika ada
            if (!empty($valuesList) && $columnNames !== null) {
                $insertHeader = "INSERT INTO `{$table}` (" . implode(', ', $columnNames) . ") VALUES\n";
                fwrite($handle, $insertHeader . implode(",\n", $valuesList) . ";\n\n");
            } elseif ($rowCount > 0) {
                fwrite($handle, "\n");
            }
        }
    }

    /**
     * Dump Database SQLite
     */
    protected function dumpSqlite($handle): void
    {
        $pdo = DB::connection()->getPdo();
        $tablesStmt = $pdo->query("SELECT name, sql FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
        $tables = $tablesStmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($tables as $t) {
            $table = $t['name'];
            $sql = $t['sql'];

            fwrite($handle, "\nDROP TABLE IF EXISTS \"{$table}\";\n");
            fwrite($handle, $sql . ";\n\n");

            $rowsStmt = $pdo->query("SELECT * FROM \"{$table}\"");
            $rows = $rowsStmt->fetchAll(\PDO::FETCH_ASSOC);

            if (!empty($rows)) {
                foreach ($rows as $row) {
                    $cols = array_map(fn($c) => "\"{$c}\"", array_keys($row));
                    $vals = array_map(function ($v) use ($pdo) {
                        return is_null($v) ? 'NULL' : $pdo->quote($v);
                    }, array_values($row));

                    fwrite($handle, "INSERT INTO \"{$table}\" (" . implode(', ', $cols) . ") VALUES (" . implode(', ', $vals) . ");\n");
                }
            }
        }
    }

    /**
     * Generic PDO Dump
     */
    protected function dumpGenericPdo($handle): void
    {
        $this->dumpMySql($handle);
    }

    /**
     * Dapatkan daftar seluruh berkas backup yang tersedia
     *
     * @return array
     */
    public function getBackupsList(): array
    {
        if (!File::exists($this->backupDir)) {
            return [];
        }

        $files = File::files($this->backupDir);
        $backups = [];

        foreach ($files as $file) {
            if ($file->getExtension() !== 'sql') {
                continue;
            }

            $size = $file->getSize();
            $mTime = $file->getMTime();

            $backups[] = [
                'file_name' => $file->getFilename(),
                'file_path' => $file->getRealPath(),
                'size_bytes' => $size,
                'size_formatted' => $this->formatBytes($size),
                'created_at' => $mTime,
                'created_at_formatted' => date('d M Y, H:i', $mTime) . ' WIB',
                'is_recent' => (time() - $mTime) < (24 * 3600),
            ];
        }

        // Urutkan dari yang terbaru
        usort($backups, function ($a, $b) {
            return $b['created_at'] <=> $a['created_at'];
        });

        return $backups;
    }

    /**
     * Dapatkan path berkas backup berdasarkan nama berkas
     */
    public function getBackupPath(string $fileName): ?string
    {
        // Sanitasi nama berkas untuk mencegah directory traversal
        $safeFileName = basename($fileName);
        $filePath = $this->backupDir . DIRECTORY_SEPARATOR . $safeFileName;

        if (File::exists($filePath) && str_ends_with($safeFileName, '.sql')) {
            return $filePath;
        }

        return null;
    }

    /**
     * Hapus berkas backup
     */
    public function deleteBackup(string $fileName): bool
    {
        $path = $this->getBackupPath($fileName);
        if ($path && File::exists($path)) {
            return File::delete($path);
        }

        return false;
    }

    /**
     * Format byte ke satuan yang mudah dibaca (B, KB, MB, GB)
     */
    public function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
