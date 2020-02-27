<?php

namespace App\Observers;
use Crawler;
use Illuminate\Support\Facades\Log;
use App\Models\Activity;
use Validator;
class LogObserver
{
	
    /**
     * Handle the model "created" event.
     *
     * @param  \App\Model  $model
     * @return void
     */
    public function created($model)
    {
        //
		$this->logActivity('created' , $model);
    }

    /**
     * Handle the model "updated" event.
     *
     * @param  \App\Model  $model
     * @return void
     */
    public function updated($model)
    {
        //
		$this->logActivity('updated' , $model);
    }

    /**
     * Handle the model "deleted" event.
     *
     * @param  \App\Model  $model
     * @return void
     */
    public function deleted($model)
    {
        //
		$this->logActivity('deleted' , $model);
    }

    /**
     * Handle the model "restored" event.
     *
     * @param  \App\Model  $model
     * @return void
     */
    public function restored( $model )
    {
       $this->logActivity('restored' , $model);
    }

    /**
     * Handle the model "force deleted" event.
     *
     * @param  \App\Model  $model
     * @return void
     */
    public function forceDeleted($model)
    {
        //
		$this->logActivity('forceDeleted' , $model);
    }
	
	
    /**
     * Laravel Logger Log Activity.
     *
     * @param string $description
     *
     * @return void
     */
    public  function logActivity($action , $model)
    {
        $userType = trans('LaravelLogger::laravel-logger.userTypes.guest');
        $userId = null;
        if (\Auth::check()) {
            $userType = trans('LaravelLogger::laravel-logger.userTypes.registered');
            $userId = \Request::user()->id;
        }

        if (Crawler::isCrawler()) {
            $userType = trans('LaravelLogger::laravel-logger.userTypes.crawler');
            $description = $userType.' '.trans('LaravelLogger::laravel-logger.verbTypes.crawled').' '.\Request::fullUrl();
        }

        
		switch (strtolower(\Request::method())) {
			case 'post':
				$verb = trans('LaravelLogger::laravel-logger.verbTypes.created');
				break;

			case 'patch':
			case 'put':
				$verb = trans('LaravelLogger::laravel-logger.verbTypes.edited');
				break;

			case 'delete':
				$verb = trans('LaravelLogger::laravel-logger.verbTypes.deleted');
				break;

			case 'get':
			default:
				$verb = trans('LaravelLogger::laravel-logger.verbTypes.viewed');
				break;
		}
		$classname = get_class($model);
		$name = $this->get_class_name($classname);
        $description = $name .' '. $action.' Desc:  '.$verb.' '.\Request::path();
        $data = [
            'description'   => $description,
            'userType'      => $userType,
            'userId'        => $userId,
			'subject_type'	=> $classname,
			'subject_id'	=> $model->id,
            'route'         => \Request::fullUrl(),
            'ipAddress'     => \Request::ip(),
            'userAgent'     => \Request::header('user-agent'),
            'locale'        => \Request::header('accept-language'),
            'referer'       => \Request::header('referer'),
            'methodType'    => \Request::method(),
        ];

        // Validation Instance
        $validator = Validator::make($data, Activity::Rules([]));
        if ($validator->fails()) {
            $errors = json_encode($validator->errors(), true);
            if (config('LaravelLogger.logDBActivityLogFailuresToFile')) {
                Log::error('Failed to record activity event. Failed Validation: '.$errors);
            }
        } else {
            self::storeActivity($data);
        }
    }

    /**
     * Store activity entry to database.
     *
     * @param array $data
     *
     * @return void
     */
    private static function storeActivity($data)
    {
        Activity::create([
            'description'   => $data['description'],
            'userType'      => $data['userType'],
            'userId'        => $data['userId'],
            'route'         => $data['route'],
			'subject_type'  => $data['subject_type'],
            'subject_id'    => $data['subject_id'],
            'ipAddress'     => $data['ipAddress'],
            'userAgent'     => $data['userAgent'],
            'locale'        => $data['locale'],
            'referer'       => $data['referer'],
            'methodType'    => $data['methodType'],
        ]);
    }
	
	function get_class_name($classname)
	{
		if ($pos = strrpos($classname, '\\')) return substr($classname, $pos + 1);
		return $pos;
	}


}
