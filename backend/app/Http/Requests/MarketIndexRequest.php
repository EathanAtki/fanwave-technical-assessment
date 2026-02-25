<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

final class MarketIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:32'],
        ];
    }

    public function queryValue(): ?string
    {
        $query = $this->string('q')->trim()->value();

        return $query === '' ? null : Str::lower($query);
    }
}
