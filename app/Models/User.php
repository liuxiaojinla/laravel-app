<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Xin\LaravelFortify\Model\Modelable;
use App\Models\User\Identity as UserIdentity;

/**
 * @property-read int $id
 * * @property int $app_id
 * * @property string $third_appid
 * * @property int $origin
 * * @property string $openid
 * * @property string $nickname
 * * @property string $gender
 * * @property string $avatarUrl
 * * @property string $language
 * * @property string $country
 * * @property string $province
 * * @property string $city
 * * @property int $energy
 * * @property int $status
 * * @property-read string $status_text
 * * @property int $sync_time
 * * @property int $login_count
 * * @property int $last_login_ip
 * * @property int $last_login_time
 * * @property UserIdentity $identity
 * * @property float $cash_amount
 * * @property int $parent_id
 * * @property int $is_distributor
 * * @property int $distributor_id
 * * @property int $belong_distributor_id
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, Modelable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nickname', 'avatar', 'gender',
        'language', 'province', 'city',
        'email', 'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'gender'            => 'int',
    ];

    public function chirps(): HasMany
    {
        return $this->hasMany(Chirp::class);
    }

    /**
     * Get the entity's notifications.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function notifications()
    {
        return $this->morphMany(Notification::class, 'notifiable')->latest();
    }

    /**
     * 登录IP地址访问器修改器
     *
     * @return Attribute
     */
    protected function lastLoginIp(): Attribute
    {
        return Attribute::make(
            function ($ip) {
                return long2ip($ip);
            },
            function ($ip) {
                return ip2long($ip);
            }
        );
    }

    /**
     * 创建IP地址访问器修改器
     *
     * @return Attribute
     */
    protected function createIp(): Attribute
    {
        return Attribute::make(
            function ($ip) {
                return long2ip($ip);
            },
            function ($ip) {
                return ip2long($ip);
            }
        );
    }
}
