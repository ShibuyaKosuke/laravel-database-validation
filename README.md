# laravel-database-validation

## Install

```
composer require shibuyakosuke/laravel-database-validation
```

## Publish validation rules

```
php artisan rule:publish
```

## Usage

Simple usage

```
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use ShibuyaKosuke\LaravelDatabaseValidator\Facades\ValidationRule;

class CompanyFormRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return ValidationRule::get('companies');
    }
}
```