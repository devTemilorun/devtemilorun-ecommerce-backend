<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

class AdminSystemController extends Controller
{
    public function createBackup()
    {
        try {
            Artisan::call('backup:run');
            return response()->json([
                'message' => 'Backup created successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Backup failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function listBackups()
    {
        $backups = [];
        try {
            $files = Storage::disk('local')->files('backups');
            foreach ($files as $file) {
                $backups[] = [
                    'name' => basename($file),
                    'size' => Storage::disk('local')->size($file),
                    'created_at' => Storage::disk('local')->lastModified($file),
                ];
            }
        } catch (\Exception $e) {
            // No backups folder yet
        }
        
        return response()->json([
            'data' => $backups,
            'message' => 'Backups list'
        ]);
    }
    
    public function restoreBackup($file)
    {
        try {
            Artisan::call('backup:restore', ['--file' => $file]);
            return response()->json([
                'message' => 'Backup restored successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Restore failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function deleteBackup($file)
    {
        try {
            Storage::disk('local')->delete('backups/' . $file);
            return response()->json([
                'message' => 'Backup deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Delete failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function clearCache()
    {
        try {
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');
            
            return response()->json([
                'message' => 'All caches cleared successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Cache clear failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function optimize()
    {
        try {
            Artisan::call('optimize');
            Artisan::call('optimize:clear');
            
            return response()->json([
                'message' => 'System optimized successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Optimization failed: ' . $e->getMessage()
            ], 500);
        }
    }
}