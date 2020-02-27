<?php 
namespace App\Traits;
trait HasPolyMorphicTranformer
{

    /**
     * Boot function from laravel.
     */
    protected static function morphTranformer($transformable_type)
    {
		$class = $this->class_name(get_class($transformable_type));
		$transformer = "\\App\\Transformers\\".ucfirst($class)."Transformer";
        return new $transformer();
		
    }
	
	function class_name($classname)
	{
		if ($pos = strrpos($classname, '\\')) return substr($classname, $pos + 1);
		return $pos;
	}
}