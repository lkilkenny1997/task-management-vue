<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category' => [
                'required',
                Rule::in(['work', 'personal', 'urgent']),
            ],
            'deadline' => [
                'required',
                'date',
                'after:now',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'A task title is required',
            'title.max' => 'Task title cannot be more than 255 characters',
            'category.required' => 'A task category is required',
            'category.in' => 'Category must be one of: work, personal, urgent',
            'deadline.required' => 'A task deadline is required',
            'deadline.after' => 'Deadline must be a future date and time',
        ];
    }
}
