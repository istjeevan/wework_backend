<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\BaseController;

class PropertyController extends BaseController
{
    public function updateClass()
    {
        $affectedRows = DB::table('properties')
            ->whereNull('contract_type')
            ->orWhereNotIn('contract_type', ['months', 'years'])
            ->update(['contract_type' => 'months']);

        return response()->json([
            'message' => 'Update successful',
            'affectedRows' => $affectedRows
        ]);
    }
    public function updateMaxContractLengthType()
    {
        $affectedRows = DB::table('properties')
            ->whereNull('max_contract_length_type')
            ->orWhereNotIn('max_contract_length_type', ['months', 'years'])
            ->update(['max_contract_length_type' => DB::raw('contract_type')]);

        return response()->json([
            'message' => 'Update successful',
            'affectedRows' => $affectedRows
        ]);
    }
}
