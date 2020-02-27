<?php

namespace App\Console\Commands;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Illuminate\Console\Command;
use App\Traits\BitcoinTrait;

class geolite extends Command
{
	use BitcoinTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'geolite:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Geololite DB File';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //
        $db = 'http://geolite.maxmind.com/download/geoip/database/GeoLite2-City.mmdb.gz';
        // $db = 'https://mirrors-cdn.liferay.com/geolite.maxmind.com/download/geoip/database/GeoIP.dat.gz';
        $path = database_path('maxmind');
        $this->download($db,$path);
        // $this->downloadzip($db);
        
    }
	
	 public static function download($remote, $desDir)
    {
        $adapter = new Local($desDir);
        $filesystem = new Filesystem($adapter);
        $pathInfo = pathinfo($remote);
       
        $stream = fopen($remote, 'r');
        echo "working till here";
        exit;
        try{
			$saved = $filesystem->putStream($pathInfo['basename'], $stream);
			fclose($stream);
            $file_name = trim($desDir) . DIRECTORY_SEPARATOR . $pathInfo['basename'];
			
		}catch(\Exception $e){
            throw $e;
        }
		
		 
		$buffer_size = 4096; // read 4kb at a time
		$out_file_name = str_replace('.gz', '', $file_name); 
		// Open our files (in binary mode)
		$file = gzopen($file_name, 'rb');
		$out_file = fopen($out_file_name, 'wb'); 

		// Keep repeating until the end of the input file
		while (!gzeof($file)) {
			// Read buffer-size bytes
			// Both fwrite and gzread and binary-safe
			fwrite($out_file, gzread($file, $buffer_size));
		}

		// Files are done, close files
		fclose($out_file);
		gzclose($file);
		unlink($file_name);
    }
}
