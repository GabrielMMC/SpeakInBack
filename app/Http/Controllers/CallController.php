<?php

namespace App\Http\Controllers;

use App\Events\CallingEvent;
use App\Events\Chat\MessageDisplayed;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;

class CallController extends Controller
{
    public function roomConnection(Request $request)
    {
        try {
            Event::dispatch(new CallingEvent($request->room_id, $request->rtc_response, $request->type));

            return response()->json([
                'message' => 'Conta criada com sucesso'
            ], 200, [], JSON_PRETTY_PRINT);
        } catch (\Exception $ex) {
            return response()->json([
                'message' => 'Erro',
                'debug' => [
                    'error' => $ex->getMessage(),
                    'file' => $ex->getFile(),
                    'line' => $ex->getLine()
                ],
            ], 500, [], JSON_PRETTY_PRINT);
        }
    }
}
