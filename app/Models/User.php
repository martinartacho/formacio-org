<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\HasMany;


class User extends Authenticatable implements JWTSubject, MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable,  HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'locale',
        'fcm_token',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];


    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

     /**
     * Get the attributes that should be cast.
     *
     * @return $with roles
     */
    /*
        
    /**
     * Get the identifier that will be stored in the JWT.
     */
    public function getJWTIdentifier()
    {
        return $this->getKey(); // Retorna el ID del usuario
    }

    /**
     * Return custom claims to be added to the JWT.
     */
    public function getJWTCustomClaims()
    {
        return []; // Puedes añadir datos personalizados aquí si necesitas
    }


    // Relación personalizada con notifications_user
    public function notifications()
    {
        return $this->belongsToMany(Notification::class, 'notification_user')
            ->withPivot('read', 'read_at')
            ->withTimestamps()
            ->orderBy('notifications.created_at', 'desc');
    }

    public function unreadNotifications()
    {
        return $this->notifications()->wherePivot('read', false);
    }

    public function fcmTokens() {  
        return $this->hasMany(FcmToken::class); // Relación 1:N  
    }

       public function settings(): HasMany
    {
        return $this->hasMany(UserSetting::class);
    }
    
    public function getSetting($key, $default = null)
    {
        $setting = $this->settings()->where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }
    
    public function setSetting($key, $value)
    {
        $this->settings()->updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }

    public function teacher()
    {
        return $this->hasOne(CampusTeacher::class);
    }

    public function treasuryData(): HasMany
    {
        return $this->hasMany(TreasuryData::class, 'teacher_id');
    }


}

