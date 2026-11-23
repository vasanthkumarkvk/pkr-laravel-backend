<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ProjectLayoutHeader extends Model
{
    protected $fillable = ['project_id', 'eyebrow', 'heading', 'description', 'unit_type_groups', 'blocks'];
    protected $casts = ['unit_type_groups' => 'array', 'blocks' => 'array'];

    public function layouts()
    {
        return $this->hasMany(ProjectLayout::class, 'project_id', 'project_id');
    }
}