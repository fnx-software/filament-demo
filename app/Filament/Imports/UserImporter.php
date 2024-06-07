<?php

namespace App\Filament\Imports;

use App\Models\User;
use Filament\Actions\Imports\Exceptions\RowImportFailedException;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

/**
 * @property string $password
 */
class UserImporter extends Importer
{
    protected static ?string $model = User::class;

    public static function getColumns(): array
    {
        /*$lang= [];
        foreach (config('app.locales') as $locale) {
            $lang[]=ImportColumn::make('name_'.$locale)
                ->requiredMapping()
                ->rules(['required', 'max:255']);
        }*/
        return [
            ImportColumn::make('name')
                ->requiredMapping()
                ->rules(['required', 'max:255','unique:users,name']),
           // ...$lang,
            ImportColumn::make('email')
                ->requiredMapping()
                ->rules(['required', 'email','unique:users,email']),
        ];
    }

    public function resolveRecord(): ?User
    {

        /*$lang= [];
        foreach (config('app.locales') as $locale) {
            $lang[]=array('name_'.$locale =>'required|max:255');
        }*/
        $validator = Validator::make($this->data, [
            'name' =>'required|max:255|unique:users,name',
            'email' => 'required|email|unique:users,email',
        ]);

        if ($validator->fails()) {
            throw new RowImportFailedException("Validation error: ");

        }
        return User::create([
            'name' =>  $this->data['name'],
            'email' =>  $this->data['email'],
            'email_verified_at' => now(),
            'password' => Hash::make('123456789'),
            'remember_token' => Str::random(10),
        ]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your user import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
