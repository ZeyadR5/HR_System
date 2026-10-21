<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class EmployeeDocument extends Model
{
    protected $fillable = ['user_id', 'type', 'path', 'original_name'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function extension(): string
    {
        return strtolower(pathinfo($this->path, PATHINFO_EXTENSION));
    }

    public function isImage(): bool
    {
        return in_array($this->extension(), ['jpg', 'jpeg', 'png', 'webp', 'gif'], true);
    }

    public function isPdf(): bool
    {
        return $this->extension() === 'pdf';
    }

    public function exists(): bool
    {
        return Storage::disk('public')->exists($this->path);
    }

    public function url(): string
    {
        return route('employee_profiles.document', [$this->user_id, $this->type]) . '?file=' . $this->id;
    }

    public function downloadUrl(): string
    {
        return $this->url() . '&download=1';
    }
}
