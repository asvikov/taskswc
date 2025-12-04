<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Illuminate\Database\Eloquent\Builder;

class Task extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;
    
    protected $fillable = [
        'name',
        'description',
        'user_id',
        'end_date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'end_date' => 'date',
        ];
    }

    const STATUS_PLANNED = 'planned';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_DONE = 'done';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')
             ->useFallbackUrl('/images/placeholder.jpg')
             ->acceptsMimeTypes(['image/jpeg', 'image/jpg', 'image/png', 'image/gif'])
             ->singleFile();
    }

    protected static function booted()
    {
        static::deleted(function ($task) {
            $task->clearMediaCollection('images');
        });
    }

    public function scopeStatus(Builder $query, ?string $status): Builder
    {
        if ($status) {
            return $query->where('status', $status);
        }
        return $query;
    }

    public function scopeUserFilter(Builder $query, ?int $userId): Builder
    {
        if ($userId) {
            return $query->where('user_id', $userId);
        }
        return $query;
    }

    public function scopeEndDateRange(Builder $query, ?string $dateFrom, ?string $dateTo): Builder
    {
        if ($dateFrom && $dateTo) {
            return $query->whereBetween('end_date', [$dateFrom, $dateTo]);
        }
        
        if ($dateFrom) {
            return $query->where('end_date', '>=', $dateFrom);
        }
        
        if ($dateTo) {
            return $query->where('end_date', '<=', $dateTo);
        }
        
        return $query;
    }
}