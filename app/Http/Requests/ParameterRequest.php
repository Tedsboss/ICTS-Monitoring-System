<?php

namespace App\Http\Requests;

use App\Models\Parameter;
use App\Rules\PurifyHtml;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ParameterRequest extends FormRequest
{
    public function authorize(): bool
    {
        $actor = $this->user();
        $parameter = $this->targetParameter();

        return $actor !== null
            && $parameter !== null
            && ($this->isMethod('put') || $this->isMethod('patch'))
            && $actor->can('update', [Parameter::class, $parameter]);
    }

    public function rules(): array
    {
        $withDuration = $this->targetParameter()?->with_duration === 'Y';

        return [
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'type' => ['required', Rule::in(['html', 'string', 'time', 'integer', 'boolean'])],
            'title' => ['required', 'string', 'min:2', 'max:255'],
            'description' => ['nullable', 'string', 'min:2', 'max:10000'],
            'value' => [
                'required',
                'max:1048576',
                Rule::when($this->input('type') === 'html', new PurifyHtml()),
                Rule::when($this->input('type') === 'string', 'string'),
                Rule::when($this->input('type') === 'time', 'date_format:H:i'),
                Rule::when($this->input('type') === 'integer', 'integer'),
                Rule::when($this->input('type') === 'boolean', Rule::in(['yes', 'no'])),
            ],
            'start_date' => [Rule::requiredIf($withDuration), 'nullable', 'date'],
            'end_date' => [Rule::requiredIf($withDuration), 'nullable', 'date', 'after:start_date'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $parameter = $this->targetParameter();

        if (! $parameter) {
            return;
        }

        // Recover the editor context from the route and database record.
        $this->merge([
            'parameterTitle' => 'Edit Parameter: ' . $parameter->name,
            'parameterAction' => route('parameters.update', $parameter->getKey()),
            'parameterMethod' => 'put',
            'parameterWithDuration' => $parameter->with_duration,
            'category' => $parameter->category,
        ]);
    }

    protected function getRedirectUrl(): string
    {
        return route('parameters.index');
    }

    private function targetParameter(): ?Parameter
    {
        $parameter = $this->route('parameter');

        if ($parameter instanceof Parameter) {
            return $parameter;
        }

        return is_numeric($parameter) ? Parameter::find((int) $parameter) : null;
    }

    public function messages(): array
    {
        return [
            'type.in' => 'Please select a supported parameter type.',
            'value.required' => 'Please enter a parameter value.',
            'value.date_format' => 'Use a 24-hour time in HH:MM format, for example 08:30.',
            'value.integer' => 'The parameter value must be an integer.',
            'value.in' => 'The Boolean value must be yes or no.',
            'start_date.required' => 'Please select the start date for this parameter.',
            'end_date.required' => 'Please select the end date for this parameter.',
            'end_date.after' => 'The end date must be later than the start date.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'parameter name',
            'type' => 'value type',
            'title' => 'title',
            'description' => 'description',
            'value' => 'parameter value',
            'start_date' => 'start date',
            'end_date' => 'end date',
        ];
    }
}
