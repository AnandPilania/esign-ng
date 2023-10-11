<?php

namespace Core\Helpers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GenerateContractCode
{
    public static function generatorCodeId($code, $lastCode)
    {
        $check = DB::table('sc_sys_uniqieid_counter')
            ->where('context_id', $code)
            ->first();
        Log::info($lastCode);
        if (!$check) {
            DB::table('sc_sys_uniqieid_counter')
                ->insert([
                    'context_id' => $code,
                    'count' => ((integer)$lastCode + 1),
                ]);
            Log::info("[GenerateContractCode] generatorCodeId id = 1");
            $sequence = $lastCode + 1;
        } else {
            $id = DB::select("CALL COUNT_UNIQUE ( ? , @id)", array($code));

            Log::info("[GenerateContractCode] generatorCodeId id = " . json_encode($id) . ' ' . $id[0]->COUNTER);

            $sequence = $id[0]->COUNTER;
        }


        return $sequence;
    }
}
