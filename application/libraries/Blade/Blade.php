<?php (defined('BASEPATH')) OR exit('No direct script access allowed');
require_once "HBladeCompiler.php";
require_once "HCompilerEngine.php";
use Philo\Blade\Blade as BaseBlade;
use Illuminate\View\Engines\CompilerEngine;

class Blade extends BaseBlade{
	protected $CI;
	public function __construct(){
		$this->CI =& get_instance();
		$this->CI->config->load('blade');
		$views = $this->CI->config->item('path_blade_view');
		$cache = $this->CI->config->item('path_blade_cache');
		parent::__construct($views,$cache);
		$this->customTagBlade();
	}
	
	private function customTagBlade(){
		$complier = $this->getCompiler();
		$complier->directive('test',function($expression){
			return "<?php echo 'CustomBlade Xem sao: ".$expression."' ?>";
		});
	}
	public function registerBladeEngine($resolver)
	{
		$me = $this;
		$app = $this->container;
		$this->container->singleton('blade.compiler', function($app) use ($me)
		{
			$cache = $me->cachePath;

			return new HBladeCompiler($app['files'], $cache);
		});

		$resolver->register('blade', function() use ($app)
		{
			return new HCompilerEngine($app['blade.compiler'], $app['files']);
		});
	}	
}
 ?>