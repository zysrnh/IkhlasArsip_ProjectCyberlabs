<?php

namespace App\Http\Controllers;

use App\Mail\DatabaseBackupMail;
use App\Services\DatabaseBackupService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BackupController extends Controller
{
    protected DatabaseBackupService $backupService;

    public function __construct(DatabaseBackupService $backupService)
    {
        $this->backupService = $backupService;
    }

    /**
     * Pastikan hanya Super Admin yang bisa mengakses modul backup
     */
    protected function authorizeSuperAdmin(): void
    {
        if (!auth()->user() || !auth()->user()->isSuperAdmin()) {
            abort(403, 'Akses ditolak. Modul Backup Database hanya dapat diakses oleh Super Admin.');
        }
    }

    /**
     * Tampilkan Halaman Daftar Riwayat Backup Database
     */
    public function index(Request $request): View
    {
        $this->authorizeSuperAdmin();

        $backups = $this->backupService->getBackupsList();
        $totalBytes = array_sum(array_column($backups, 'size_bytes'));
        $totalFormatted = $this->backupService->formatBytes($totalBytes);

        return view('backups.index', [
            'backups' => $backups,
            'totalCount' => count($backups),
            'totalStorageFormatted' => $totalFormatted,
            'driver' => config('database.connections.' . config('database.default') . '.driver'),
            'databaseName' => config('database.connections.' . config('database.default') . '.database'),
        ]);
    }

    /**
     * Buat Backup Database Baru Secara Langsung
     */
    public function createBackup(Request $request): RedirectResponse
    {
        $this->authorizeSuperAdmin();

        try {
            $backup = $this->backupService->createBackup();

            // Jika user memilih kirim ke email saat pembuatan backup
            if ($request->filled('email') && filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
                Mail::to($request->email)->send(new DatabaseBackupMail(
                    filePath: $backup['file_path'],
                    fileName: $backup['file_name'],
                    fileSizeFormatted: $backup['size_formatted'],
                    recipientName: auth()->user()->name,
                    notes: $request->input('notes', 'Backup database manual oleh Super Admin.')
                ));

                return redirect()->route('backups.index')->with(
                    'success',
                    "Backup {$backup['file_name']} ({$backup['size_formatted']}) berhasil dibuat dan dikirim ke {$request->email}."
                );
            }

            return redirect()->route('backups.index')->with(
                'success',
                "Backup database {$backup['file_name']} ({$backup['size_formatted']}) berhasil dibuat."
            );
        } catch (Exception $e) {
            return redirect()->route('backups.index')->with(
                'error',
                'Gagal membuat backup database: ' . $e->getMessage()
            );
        }
    }

    /**
     * Unduh Berkas Backup SQL Langsung
     */
    public function download(string $fileName): BinaryFileResponse
    {
        $this->authorizeSuperAdmin();

        $filePath = $this->backupService->getBackupPath($fileName);

        if (!$filePath) {
            abort(404, 'Berkas backup tidak ditemukan atau format tidak valid.');
        }

        return response()->download($filePath, $fileName, [
            'Content-Type' => 'application/sql',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
        ]);
    }

    /**
     * Kirim Berkas Backup yang Sudah Ada ke Alamat Email Tertentu
     */
    public function sendEmail(Request $request, string $fileName): RedirectResponse
    {
        $this->authorizeSuperAdmin();

        $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'notes' => ['nullable', 'string', 'max:500'],
        ], [
            'email.required' => 'Alamat email penerima wajib diisi.',
            'email.email' => 'Format alamat email tidak valid.',
        ]);

        $filePath = $this->backupService->getBackupPath($fileName);

        if (!$filePath) {
            return redirect()->route('backups.index')->with('error', 'Berkas backup yang dipilih tidak ditemukan.');
        }

        $fileSizeFormatted = $this->backupService->formatBytes(filesize($filePath));

        try {
            Mail::to($request->email)->send(new DatabaseBackupMail(
                filePath: $filePath,
                fileName: $fileName,
                fileSizeFormatted: $fileSizeFormatted,
                recipientName: $request->input('recipient_name', 'Super Admin'),
                notes: $request->input('notes', 'Pengiriman salinan berkas backup database.')
            ));

            return redirect()->route('backups.index')->with(
                'success',
                "Berkas backup {$fileName} berhasil dikirim ke {$request->email}."
            );
        } catch (Exception $e) {
            return redirect()->route('backups.index')->with(
                'error',
                'Gagal mengirim email backup: ' . $e->getMessage()
            );
        }
    }

    /**
     * Hapus Berkas Backup Tertentu
     */
    public function destroy(string $fileName): RedirectResponse
    {
        $this->authorizeSuperAdmin();

        $deleted = $this->backupService->deleteBackup($fileName);

        if ($deleted) {
            return redirect()->route('backups.index')->with(
                'success',
                "Berkas backup {$fileName} berhasil dihapus dari penyimpanan server."
            );
        }

        return redirect()->route('backups.index')->with(
            'error',
            "Gagal menghapus berkas backup {$fileName}."
        );
    }
}
