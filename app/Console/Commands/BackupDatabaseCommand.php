<?php

namespace App\Console\Commands;

use App\Mail\DatabaseBackupMail;
use App\Services\DatabaseBackupService;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class BackupDatabaseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:database {--email= : Kirim file backup ke email tujuan}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Melakukan backup seluruh struktur dan data database ke dalam berkas .sql';

    /**
     * Execute the console command.
     */
    public function handle(DatabaseBackupService $backupService): int
    {
        $this->info('Memulai proses backup database Ikhlas Solusi...');

        try {
            $backup = $backupService->createBackup();

            $this->info("Backup database berhasil dibuat: {$backup['file_name']} ({$backup['size_formatted']})");

            $email = $this->option('email');
            if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->info("Mengirim file backup ke email: {$email}...");

                Mail::to($email)->send(new DatabaseBackupMail(
                    filePath: $backup['file_path'],
                    fileName: $backup['file_name'],
                    fileSizeFormatted: $backup['size_formatted'],
                    recipientName: 'Super Admin',
                    notes: 'Backup database otomatis terjadwal oleh sistem.'
                ));

                $this->info("File backup berhasil dikirim ke {$email}.");
            }

            return Command::SUCCESS;
        } catch (Exception $e) {
            $this->error("Gagal melakukan backup database: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
