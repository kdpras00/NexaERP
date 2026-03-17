<?php

namespace App\Filament\Resources\RoleResource\Pages;

use App\Filament\Resources\RoleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditRole extends EditRecord
{
    protected static string $resource = RoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // The form fields in RoleResource handle their own hydration via afterStateHydrated
        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $permissions = [];
        
        foreach ($data as $key => $value) {
            if (str_starts_with($key, 'permissions_')) {
                if (is_array($value)) {
                    $permissions = array_merge($permissions, $value);
                    
                    // Automatically add 'view_any' if ANY permission is selected for a resource
                    if (!empty($value) && str_starts_with($key, 'permissions_') && !in_array($key, ['permissions_pages', 'permissions_widgets', 'permissions_special'])) {
                        $moduleName = str_replace('permissions_', '', $key);
                        $permissions[] = "view_any_" . $moduleName;
                        
                        // Add delete_any if delete is selected
                        if (in_array("delete_{$moduleName}", $value)) {
                            $permissions[] = "delete_any_" . $moduleName;
                        }

                        // Add restore_any and force_delete_any if delete is selected (standardizing for soft deletes)
                        if (in_array("delete_{$moduleName}", $value)) {
                            $permissions[] = "restore_any_" . $moduleName;
                            $permissions[] = "force_delete_any_" . $moduleName;
                        }
                    }
                }
                unset($data[$key]);
            }
        }

        // Clean up duplicates
        $permissions = array_unique($permissions);
        
        $record->update($data);
        
        if ($record->name === 'Super Admin') {
            // Ensure Super Admin has all permissions or skip sync since Gate::before handles it anyway
        } else {
            $record->syncPermissions($permissions);
        }
        
        return $record;
    }
}
