<?php namespace AkibTheme\Models;

use Model;
use System\Models\File;

/**
 * Slider Model
 * 
 * This model represents a slide with title, description,
 * a background image (photo), and a button link.
 */
class Slider extends Model
{
    /**
     * AttachOne relationship to System Files
     * The 'photo' field is an image uploaded via FileUpload.
     */
    public $attachOne = [
        'photo' => File::class
    ];

    /**
     * Fillable fields for the model
     */
    protected $fillable = [
        'title',
        'description',
        'button_link'
    ];
}
