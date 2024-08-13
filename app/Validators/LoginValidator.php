<?php

namespace App\Validators;
use App\Validators\Validator;
use Illuminate\Validation\Rule;

class LoginValidator extends Validator 
{
    /**
     * Rules for User login
     *
     * @var array
     */
    protected $rules = [
        'email'     => 'required|string|email|max:255',
        'password' => 'required|string|min:5'
    ];
    /**
     * Messages for User login
     *
     * @var array
     */
    protected $messages = [];
    public function __construct()
    {
        $this->messages = [
         
        ];
    }
}