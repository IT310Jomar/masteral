<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SystemConfiguration;
use App\Http\Controllers\Controller;

class SystemConfigurationController extends Controller
{

    public function toggleMaintenanceMode(Request $request)
    {
        // Validate the request if needed
        $isMaintenance = $request->input('isUnderMaintenance');

        $systemConfiguration = SystemConfiguration::firstOrNew([]);
        $systemConfiguration->isUnderMaintenance = $isMaintenance;
        $systemConfiguration->save();

        return response()->json([
            'message' => 'Maintenance mode updated successfully',
            'isUnderMaintenance' => $systemConfiguration->isUnderMaintenance,
        ]);
    }

    public function getMaintenance()
    {
        $maintenance = SystemConfiguration::get();

        return response()->json([
            'success' => true,
            'maintenance' => $maintenance,
        ]);
    }
}
