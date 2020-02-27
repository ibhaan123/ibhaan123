<?php

namespace App\Traits;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Storage;
use File;
use Image;
trait UploadTrait
{
	 public function upload($image = 'file', $w = 300, $h=300 , $constrain = false)
    { 
        if (Input::hasFile($image)) {
            $currentUser = auth()->user();
            $avatar = Input::file($image);
            $filename = str_random(30).'.'.$avatar->getClientOriginalExtension();
            $save_path = storage_path().'/app/public/'; // 
            $path = $save_path.$filename;
            // Make the user a folder and set permissions
            File::makeDirectory($save_path, $mode = 0755, true, true);
            // Save the file to the server
			if($constrain){
				Image::make($avatar)->resize($w, $h, function ($constraint) {
					$constraint->aspectRatio();
					$constraint->upsize();
				})->save($save_path.$filename);
			}else{
            	Image::make($avatar)->fit($w, $h)->save($save_path.$filename);
			}
			return $filename ;
        } else {
            return false;
        }
    }
	
	
	
	
	
	
    /**
    /**
    * Method : DELETE
    *
    * @return delete images
    */
    public function delete($image) {
        $file = storage_path().'/images/'.$image;
		Storage::delete($file);
        return true;  
    }
	
	
	 public function getImage($image)
    {
		$image = explode('@',$image);
		$img = Image::make(storage_path().'/images/'.$image[0]);
		if(isset($image[1])&&!empty($image[1])){
			list($w,$h) = explode('x',strtolower($image[1]));
			$img->fit($w, $h);
		}
        return $img->response();
    }
	
	public function download($file)
    {
		$pathToFile = storage_path().'/images/'.$file;
		return response()->download($pathToFile);
		
    }
	
	public function download_pdf($file)
    {
		$pathToFile = storage_path().'/pdf/'.$file;
		return response()->download($pathToFile);
		
    }
	
	
	
	public function view($file)
    {
		$pathToFile = storage_path().'/images/'.$file;
		return response()->file($pathToFile);
		
    }
}
