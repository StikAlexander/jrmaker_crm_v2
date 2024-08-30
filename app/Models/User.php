<?php

namespace App\Models;

use BezhanSalleh\FilamentShield\Traits\HasPanelShield;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Facades\Crypt;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Image\Manipulations;

class User extends Authenticatable implements FilamentUser, MustVerifyEmail, HasAvatar, HasName, HasMedia
{
    use InteractsWithMedia;
    use HasRoles;
    use HasApiTokens, HasFactory, Notifiable;
    use HasPanelShield;

    protected $fillable = [
        'document_number',
        'name',
        'email',
        'phone',
        'email_verified_at',
        'password',
        'created_at',
        'updated_at',
        'deleted_at',
        'document_type_id',
        'created_by_id',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    protected static function booted()
    {
        static::creating(function ($user) {
            if (is_null($user->status)) {
                $user->status = 'active';
            }
        });
    }

    // Encriptar el número de documento
    public function setDocumentNumberAttribute($value)
    {
        $this->attributes['document_number'] = Crypt::encryptString((string) $value);
    }

    public function getDocumentNumberAttribute($value)
    {
        try {
            return Crypt::decryptString($value);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            return null;
        }
    }

    // Métodos de Filament
    public function getFilamentName(): string
    {
        return $this->name;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() === 'admin') {
            return $this->hasAnyRole(['super_admin', 'admin', 'collaborator']);
        }

        if ($panel->getId() === 'client') {
            return $this->hasRole('client');
        }

        return false;
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->getMedia('avatars')?->first()?->getUrl() ?? $this->getMedia('avatars')?->first()?->getUrl('thumb') ?? null;
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole(config('filament-shield.super_admin.name'));
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->fit(Fit::Contain, 300, 300)
            ->nonQueued();
    }

    // Relaciones
    public function documentType()
    {
        return $this->belongsTo(DocumentType::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }
}
