<?php

namespace App\Filament\Resources\RoleResource\Pages;

use App\Filament\Resources\RoleResource;
use Filament\Resources\Pages\CreateRecord;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Eloquent\Model;

class CreateRole extends CreateRecord
{
    protected static string $resource = RoleResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        // Extract all permissions fields
        $permissions = [];
        
        foreach ($data as $key => $value) {
            if (str_starts_with($key, 'permissions_')) {
                if (is_array($value)) {
                    $permissions = array_merge($permissions, $value);
                    
                    // Automatically add auxiliary permissions
                    if (!empty($value) && str_starts_with($key, 'permissions_') && !in_array($key, ['permissions_pages', 'permissions_widgets', 'permissions_special'])) {
                        $moduleName = str_replace('permissions_', '', $key);
                        $permissions[] = "view_any_" . $moduleName;
                        
                        // Add delete_any and related if delete is selected
                        if (in_array("delete_{$moduleName}", $value)) {
                            $permissions[] = "delete_any_" . $moduleName;
                            $permissions[] = "restore_any_" . $moduleName;
                            $permissions[] = "force_delete_any_" . $moduleName;
                        }
                    }
                }
                unset($data[$key]);
            }
        }
        
        $role = static::getModel()::create($data);
        
        if (!empty($permissions)) {
            $role->syncPermissions(array_unique($permissions));
        }
        
        return $role;
    }
}
