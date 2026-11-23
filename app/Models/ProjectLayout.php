<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ProjectLayout extends Model
{
    protected $fillable = ['project_id', 'title', 'block_name', 'unit_type', 'facing', 'area', 'description', 'plan_image'];
}