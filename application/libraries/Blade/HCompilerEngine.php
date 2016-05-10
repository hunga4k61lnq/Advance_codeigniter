<?php (defined('BASEPATH')) OR exit('No direct script access allowed');
use Illuminate\View\Engines\CompilerEngine;
use Illuminate\View\Compilers\CompilerInterface;
class HCompilerEngine extends CompilerEngine{
	protected $CI;
	public function __construct(CompilerInterface $compiler)
    {
    	parent::__construct($compiler);
    	if($this->CI==NULL){
			$this->CI =& get_instance();
		}
    }
}

 ?>