<?php (defined('BASEPATH')) OR exit('No direct script access allowed');
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\Filesystem\Filesystem;
class HBladeCompiler extends BladeCompiler{
	protected $getTags = ['{\(', '\)}']; 
	protected $settingsTags = ['{\[', '\]}']; 
	protected $langTags = ['{:', ':}']; 
	
	public function __construct(Filesystem $files, $cachePath){
		parent::__construct($files,$cachePath);
		
	}
	protected function getEchoMethods()
    {
        $methods = [
            'compileRawEchos' => strlen(stripcslashes($this->rawTags[0])),
            'compileEscapedEchos' => strlen(stripcslashes($this->escapedTags[0])),
            'compileRegularEchos' => strlen(stripcslashes($this->contentTags[0])),
            'compileGetEchos' => strlen(stripcslashes($this->getTags[0])),
            'compileSettingEchos' => strlen(stripcslashes($this->settingsTags[0])),
            'compileLangEchos' => strlen(stripcslashes($this->langTags[0])),
        ];

        uksort($methods, function ($method1, $method2) use ($methods) {
            if ($methods[$method1] > $methods[$method2]) {
                return -1;
            }
            if ($methods[$method1] < $methods[$method2]) {
                return 1;
            }
            if ($method1 === 'compileRawEchos') {
                return -1;
            }
            if ($method2 === 'compileRawEchos') {
                return 1;
            }

            if ($method1 === 'compileEscapedEchos') {
                return -1;
            }
            if ($method2 === 'compileEscapedEchos') {
                return 1;
            }

        });
        return $methods;
    }

    protected function compileGetEchos($value){

    	$pattern = sprintf('/%s((.+)\.)?(.+)\.(0|1)%s(\r?\n)?/', $this->getTags[0], $this->getTags[1]);
        $callback = function ($matches) {
	       	return sprintf("<?php echom(%s,'%s',%s); ?>",isNull($matches[2])?'$dataitem':"$".trim($matches[2]),trim($matches[3]),trim($matches[4]));
        };

        return preg_replace_callback($pattern, $callback, $value);
    }
    protected function compileSettingEchos($value){

    	$pattern = sprintf('/%s(.+?)%s(\r?\n)?/', $this->settingsTags[0], $this->settingsTags[1]);
        $callback = function ($matches) {
	       	return sprintf(@"<?php echo %s->CI->Dindex->getSettings('%s'); ?>",'$this',trim(strtoupper($matches[1])));
        };

        return preg_replace_callback($pattern, $callback, $value);
    }

    protected function compileLangEchos($value){

    	$pattern = sprintf('/%s(.+?)%s(\r?\n)?/', $this->langTags[0], $this->langTags[1]);
        $callback = function ($matches) {
	       	return sprintf("<?php echo lang('%s'); ?>",trim(strtoupper($matches[1])));
        };

        return preg_replace_callback($pattern, $callback, $value);
    }


}
 ?>
