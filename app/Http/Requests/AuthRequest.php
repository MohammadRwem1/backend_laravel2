<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AuthRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'first_name'=>'required|string|max:255',
            'last_name'=>'required|string|max:255',
            'phone'=>'required|string|unique:users,phone',
            'password'=>'required|string|min:8',
            'role'=>'required|in:renter,owner',
            'birth_date'=>'required|date',
            'id_image'=>'nullable|mimes:jpg,jpeg,png',
            'profile_image'=>'nullable|mimes:jpg,jpeg,png',
        ];
    }
}
