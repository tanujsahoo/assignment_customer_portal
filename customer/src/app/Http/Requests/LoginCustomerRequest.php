<?php

namespace App\Http\Requests;

class LoginCustomerRequest extends CommonFormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'origin' => [
                'required',
                'string'
            ],
            'destination' => [
                'required',
                'string'
            ],
        ];
    }

    /**
     * Custom message for validation
     *
     * @return array
     */
    public function messages()
    {
        return [
            'origin.required' => 'REQ_ORIGIN',
            'destination.required' => 'REQ_DESTINATION',
        ];
    }
}
