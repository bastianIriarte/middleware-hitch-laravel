<?php

namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Users extends Authenticatable implements JWTSubject
{
    use Notifiable, HasApiTokens;
    protected $table = 'users';
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'username',
        'rut',
        'mobile',
        'password',
        'remember_token',
        'last_entry',
        'activation_token',
        'profile_id',
        'status',
        'validate_password',
        'user_created',
        'created_at',
        'user_updated',
        'updated_at',
        'deleted',
        'user_deleted',
        'deleted_at',
        'user_confirmed',
        'account_confirmed',
        'account_confirmed_at',
        'menu_type',
        'api_access',
    ];
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */

    protected $hidden = [
        'password', 'remember_token',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey(); // normalmente el ID del usuario
    }

    public function getJWTCustomClaims()
    {
        return []; // claims personalizados (si quieres agregar)
    }
    
    public function profile()
    {
        return $this->belongsTo('App\Models\Profiles', 'profile_id');
    }

    public function permissions()
    {
        // Esto obtiene los permisos a través del perfil del usuario
        return $this->profile ? $this->profile->permissions() : collect();
    }

    public function hasPermission($code)
    {
        return $this->permissions()
            ->where('permissions.code', $code)
            ->where('permissions_profile.deleted', false) // Especifica la tabla 'permissions_profile'
            ->exists();
    }

    // Relaciones con el modelo Users
    public function createdBy()
    {
        return $this->belongsTo('App\Models\Users', 'user_created');
    }

    public function updatedBy()
    {
        return $this->belongsTo('App\Models\Users', 'user_updated');
    }

    public function deletedBy()
    {
        return $this->belongsTo('App\Models\Users', 'user_deleted');
    }
}
