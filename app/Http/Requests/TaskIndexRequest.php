<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Task;
use Illuminate\Validation\Rule;

class TaskIndexRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => [
                'nullable',
                'string',
                Rule::in([Task::STATUS_PLANNED, Task::STATUS_IN_PROGRESS, Task::STATUS_DONE])
            ],
            'user_id' => 'nullable|integer|exists:users,id',
            'end_date_from' => 'nullable|date',
            'end_date_to' => 'nullable|date|after_or_equal:end_date_from',
        ];
    }

    public function messages(): array
    {
        return [
            'status.in' => 'Status must be one of: planned, in_progress, done',
            'user_id.exists' => 'The selected user does not exist',
            'end_date_to.after_or_equal' => 'End date to must be after or equal to end date from',
        ];
    }
}
