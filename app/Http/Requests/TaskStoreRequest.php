<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Task;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class TaskStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('data')) {
            $data = json_decode($this->input('data'), true);
            
            if (json_last_error() === JSON_ERROR_NONE) {
                $this->merge($data);
            }
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'end_date' => 'nullable|date',
            'status' => [
                'required',
                Rule::in([Task::STATUS_PLANNED, Task::STATUS_IN_PROGRESS, Task::STATUS_DONE])
            ],
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'A name is required',
            'description.required' => 'A description is required',
            'user_id.required' => 'A user ID is required',
            'user_id.exists' => 'The selected user ID is invalid',
            'status.in' => 'The status must be planned, in_progress, or done',
            'image.image' => 'The file must be an image',
            'image.mimes' => 'The image must be a file of type: jpeg, png, jpg, gif',
            'image.max' => 'The image may not be greater than 2MB',
        ];
    }           
}