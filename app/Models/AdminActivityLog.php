<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminActivityLog extends Model
{
    protected $fillable = [
        'admin_id',
        'action',
        'description',
        'ip_address',
    ];

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public static function record(?int $adminId, string $action, string $description, ?string $ip = null): void
    {
        try {
            self::create([
                'admin_id' => $adminId,
                'action' => $action,
                'description' => mb_substr($description, 0, 255),
                'ip_address' => $ip,
            ]);
        } catch (\Throwable) {
            // Non-blocking safeguard
        }
    }
}
