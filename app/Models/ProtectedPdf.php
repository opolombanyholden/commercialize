<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ProtectedPdf extends Model
{
    use HasFactory;

    protected $fillable = [
        'filename',
        'stored_path',
        'password_hash',
        'metadata',
        'expires_at',
        'max_downloads'
    ];

    protected $casts = [
        'metadata' => 'array',
        'expires_at' => 'datetime',
        'is_active' => 'boolean'
    ];

    protected $hidden = [
        'password_hash',
        'stored_path'
    ];

    /**
     * Boot du modèle
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            $model->token = Str::random(32);
        });

        static::deleting(function ($model) {
            // Supprimer le fichier physique
            if (Storage::disk('secure')->exists($model->stored_path)) {
                Storage::disk('secure')->delete($model->stored_path);
            }
        });
    }

    /**
     * Créer un PDF protégé
     */
    public static function createProtected(
        string $pdfContent,
        string $filename,
        string $password,
        array $metadata = [],
        int $hoursValid = 24,
        int $maxDownloads = 5
    ): self {
        // Générer un nom de fichier unique
        $storedFilename = Str::random(40) . '.pdf';
        $storedPath = 'protected-pdfs/' . $storedFilename;
        
        // Stocker le fichier de façon sécurisée
        Storage::disk('secure')->put($storedPath, $pdfContent);
        
        return self::create([
            'filename' => $filename,
            'stored_path' => $storedPath,
            'password_hash' => Hash::make($password),
            'metadata' => $metadata,
            'expires_at' => Carbon::now()->addHours($hoursValid),
            'max_downloads' => $maxDownloads,
        ]);
    }

    /**
     * Vérifier le mot de passe
     */
    public function verifyPassword(string $password): bool
    {
        return Hash::check($password, $this->password_hash);
    }

    /**
     * Vérifier si le PDF peut être téléchargé
     */
    public function canDownload(): bool
    {
        return $this->is_active && 
               $this->expires_at->isFuture() && 
               $this->download_count < $this->max_downloads;
    }

    /**
     * Obtenir le contenu du PDF
     */
    public function getContent(): ?string
    {
        if (!$this->canDownload()) {
            return null;
        }

        if (!Storage::disk('secure')->exists($this->stored_path)) {
            return null;
        }

        // Incrémenter le compteur de téléchargements
        $this->increment('download_count');

        return Storage::disk('secure')->get($this->stored_path);
    }

    /**
     * Scope pour les PDFs actifs
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                    ->where('expires_at', '>', Carbon::now());
    }

    /**
     * Scope pour nettoyer les PDFs expirés
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<', Carbon::now())
                    ->orWhere('download_count', '>=', 'max_downloads');
    }

    /**
     * Nettoyage automatique des PDFs expirés
     */
    public static function cleanupExpired(): int
    {
        $expired = self::expired()->get();
        $count = $expired->count();
        
        foreach ($expired as $pdf) {
            $pdf->delete();
        }
        
        return $count;
    }

    /**
     * Obtenir les informations publiques
     */
    public function getPublicInfo(): array
    {
        return [
            'token' => $this->token,
            'filename' => $this->filename,
            'metadata' => $this->metadata,
            'expires_at' => $this->expires_at,
            'downloads_remaining' => max(0, $this->max_downloads - $this->download_count),
            'can_download' => $this->canDownload(),
        ];
    }
}