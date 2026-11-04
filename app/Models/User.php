<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'password_view',
        'role_id',
        'periode_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'password_view',
        'remember_token',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function periode()
    {
        return $this->belongsTo(Periode::class);
    }

    public function isAdmin(): bool
    {
        return (bool) $this->role?->is_admin;
    }

    public function bisaMenu(string $menu): bool
    {
        return $this->isAdmin() || in_array($menu, $this->role?->menus ?? [], true);
    }

    public function butuhPeriode(): bool
    {
        return ! $this->isAdmin()
            && array_intersect(array_keys(Role::MENU_KEPENGURUSAN), $this->role?->menus ?? []) !== [];
    }

    public function homeUrl(): string
    {
        if ($this->isAdmin()) {
            return route('dashboard');
        }

        if ($this->periode_id) {
            foreach (array_keys(Role::MENU_KEPENGURUSAN) as $menu) {
                if ($this->bisaMenu($menu)) {
                    return url("/kepengurusan/{$this->periode_id}/".str_replace('_', '-', $menu));
                }
            }
        }

        $rute = [
            'dashboard' => 'dashboard',
            'inventaris' => 'inventaris.index',
            'peminjaman' => 'peminjaman-admin',
            'data_anggota' => 'data_anggota.index',
            'generator_qr' => 'generate_qr.index',
        ];
        foreach ($rute as $menu => $nama) {
            if ($this->bisaMenu($menu)) {
                return route($nama);
            }
        }

        return route('akses.kosong');
    }

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
}
