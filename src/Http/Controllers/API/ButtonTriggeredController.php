<?php

namespace Amprest\DtTables\Http\Controllers\API;

use Amprest\DtTables\Events\ButtonTriggered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class ButtonTriggeredController
{
    /**
     * Handle the event.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function __invoke(Request $request): JsonResponse
    {
        try {
            //  Set the status
            $status = 200;

            //  Set the message
            $message = 'Button triggered successfully';

            //  Dispatch the event
            ButtonTriggered::dispatch($request->all());

        //  Define the catch block
        } catch (Throwable $e) {
            //  Set the status
            $status = $e->getCode() ?: 500;

            //  Set the message
            $message = $e->getMessage() ?: 'An error occurred while processing the request';
        }

        //  Return a JSON response
        return response()->json(['message' => $message], $status);
    }
}