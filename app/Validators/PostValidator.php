<?php

namespace App\Validators;
use App\Validators\Validator;
use Illuminate\Validation\Rule;

class PostValidator extends Validator 
{
    /**
     * Rules for User login
     *
     * @var array
     */
    protected $rules = [
        'title'     => 'required|string',
        'description' => 'required|string'
    ];
    /**
     * Messages for User login
     *
     * @var array
     */
    protected $messages = [];
    public function __construct()
    {
        
    }
}