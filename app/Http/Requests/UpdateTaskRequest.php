<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'category' => [
                'sometimes',
                'required',
                Rule::in(['work', 'personal', 'urgent']),
            ],
            'deadline' => [
                'sometimes',
                'required',
                'date',
                'after:now',
            ],
            'completed' => ['sometimes', 'required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'A task title is required',
            'title.max' => 'Task title cannot be more than 255 characters',
            'category.in' => 'Category must be one of: work, personal, urgent',
            'deadline.after' => 'Deadline must be a future date and time',
            'completed.boolean' => 'Completed status must be true or false',
        ];
    }
}
