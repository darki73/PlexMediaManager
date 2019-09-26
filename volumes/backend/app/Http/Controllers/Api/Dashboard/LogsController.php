<?php namespace App\Http\Controllers\Api\Dashboard;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Api\APIController;

/**
 * Class LogsController
 * @package App\Http\Controllers\Api\Dashboard
 */
class LogsController extends APIController {

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function retrieveLogs(Request $request) : JsonResponse {
        $entries = [];
        /**
         * @var \App\Classes\LogReader\Entities\LogEntry $entry
         */
        foreach (\LogReader::get() as $entry) {
            $context = $entry->context;
            $group = $entry->date->format('d-m-Y');
            $entries[$group][] = [
                'id'            =>  $entry->id,
                'environment'   =>  $entry->environment,
                'level'         =>  $entry->level,
                'file_path'     =>  $entry->file_path,
                'date'          =>  $entry->date->toDateTimeString(),
                'context'       =>  $context !== null  ? $context->toArray() : null,
                'stack_traces'  =>  $entry->getStackTracesAsArray()
            ];
        }
        return $this->sendResponse('Successfully fetched list of all logs', array_reverse($entries));
    }

}
