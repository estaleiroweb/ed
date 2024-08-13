<?php
namespace Sys;

class Callable_Details{
	use \Traits\OO\Singleton,\SessionConfig;
	
	public function __invoke($func=false){
		if (!$func) return $this->get_All();
		return is_array($func)?$this->get_Method_or_Arg($func):$this->get_Function($func);
	}
	private function get_All() {
		$cache=array('functions'=>[],'classes'=>[],);
		$fns=get_defined_functions();
		//foreach ($fns['user'] as $fn) $cache['functions'][$fn]=$this->get_Function($fn);
		$cls=get_declared_classes();
		$cls=array_slice($cls,143);
		foreach ($cls as $class)      $cache['classes'][$class]=$this->get_Class($class);
		return $cache;
	}
	private function get_Class($class) {
		$out=$this->getSession($id="[class]$class");
		if(is_null($out)){
			try {
				$obj=new \ReflectionClass($class);
				$out=array(
					'name'=>$obj->name,
					'shortName'=>$obj->getShortName(),
					'extensionName'=>$obj->getExtensionName(),
					'inNamespace'=>$obj->inNamespace(),
					'namespace'=>$obj->inNamespace()?$obj->getNamespaceName():null,
					'isInternal'=>$obj->isInternal(),
					'isUserDefined'=>$obj->isUserDefined(),
					'isAbstract'=>$obj->isAbstract(),
					'isCloneable'=>$obj->isCloneable(),
					'isFinal'=>$obj->isFinal(),
					'isInstantiable'=>$obj->isInstantiable(),
					'isInterface'=>$obj->isInterface(),
					'isIterateable'=>$obj->isIterateable(),
					'isTrait'=>$obj->isTrait(),
					'modifier'=>($obj->isAbstract()?'abstract ':($obj->isFinal()?'final ':'')).($obj->isInterface()?'interface ':($obj->isTrait()?'trait ':'class ')),
					'getModifiers'=>$obj->getModifiers(),
					'getExtension'=>$obj->getExtension(),

					'fileName'=>$obj->getFileName(),
					'startLine'=>$obj->getStartLine(),
					'endLine'=>$obj->getEndLine(),
					
					'extends'=>$o=$obj->getParentClass()?get_class($o):null,
					'interfaces'=>$obj->getInterfaceNames(),
					'traits'=>$obj->getTraitAliases(),
					'constants'=>$obj->getConstants(),
					'properties'=>[], 
					'methods'=>[],
					'docComment'=>$obj->getDocComment(),
				);
				/*
				/**/
				$props=$obj->getProperties();
				foreach($props as $arg) $out['properties'][$arg->name]=$method=$this->get_Property($arg);
				$methods=$obj->getMethods();
				foreach($methods as $func) $out['methods'][$func->name]=$method=$this->get_Method($func);
			} catch (\ReflectionException $e) {
				trigger_error($e->getMessage());
				$out=array();
			}
			$this->setSession($out,$id);
		}
		return $out;
	}
	private function get_Method_or_Arg(array $func) {
		try {
			$method=new \ReflectionMethod($func[0],$func[1]);
			return $this->get_Method($method);
		} catch (\ReflectionException $e) {
			trigger_error($e->getMessage());
			$out=array();
		}
	}
	private function get_Method(\ReflectionMethod $func) { //ReflectionFunctionAbstract
		$out=$this->getSession($id="[method]{$func->class}::{$func->name}");
		if(is_null($out)){
			try {
				$func=new \ReflectionMethod($func->class,$func->name);
				$out=$this->get_FunctionDetails($func);
			} catch (\ReflectionException $e) {
				trigger_error($e->getMessage());
				$out=array();
			}
			$this->setSession($out,$id);
		}
		return $out;
	}
	private function get_Function($func) { //ReflectionFunctionAbstract
		$out=$this->getSession($id="[func]$func");
		if(is_null($out)){
			try {
				$func=new \ReflectionFunction($func);
				$out=$this->get_FunctionDetails($func);
			} catch (\ReflectionException $e) {
				trigger_error($e->getMessage());
				$out=array();
			}
			$this->setSession($out,$id);
		}
		return $out;
	}
	private function get_FunctionDetails(\ReflectionFunctionAbstract $func) {
		$class=get_class($func);
		$isAbstract=$isConstructor=$isDestructor=$isFinal=$isPrivate=$isProtected=$isPublic=$isStatic=$modifier=$getModifiers=$isDisabled=null;
		if($class=='ReflectionMethod'){
			$isAbstract=$func->isAbstract();
			$isConstructor=$func->isConstructor();
			$isDestructor=$func->isDestructor();
			$isFinal=$func->isFinal();
			$isPrivate=$func->isPrivate();
			$isProtected=$func->isProtected();
			$isPublic=$func->isPublic();
			$isStatic=$func->isStatic();
			$modifier=($func->isAbstract()?'abstract ':($func->isFinal()?'final ':'')).($func->isPublic()?'public ':($func->isPrivate()?'private ':($func->isProtected()?'protected ':''))).($func->isStatic()?'static ':'');
			$getModifiers=$func->getModifiers();
			//ReflectionClass getDeclaringClass()
		} else {
			$isDisabled=$func->isDisabled();;
		}
		return array(
			'name'=>$func->name,
			'shortName'=>$func->getShortName(),
			'extensionName'=>$func->getExtensionName(),
			'inNamespace'=>$func->inNamespace(),
			'namespace'=>$func->inNamespace()?$func->getNamespaceName():null,
			
			'isInternal'=>$func->isInternal(),
			'isUserDefined'=>$func->isUserDefined(),
			'isClosure'=>$func->isClosure(),
			'isDeprecated'=>$func->isDeprecated(),
			//'isGenerator'=>@$func->isGenerator(),
			//'isVariadic'=>$func->isVariadic(),
			//'hasReturnType'=>$func->hasReturnType(),
			//'returnType'=>$func->getReturnType(),
			'isAbstract'=>$isAbstract,
			'isConstructor'=>$isConstructor,
			'isDestructor'=>$isDestructor,
			'isFinal'=>$isFinal,
			'isPrivate'=>$isPrivate,
			'isProtected'=>$isProtected,
			'isPublic'=>$isPublic,
			'isStatic'=>$isStatic,
			'modifier'=>$modifier,
			'getModifiers'=>$getModifiers,
			'isDisabled'=>$isDisabled,
			
			'getClosureScopeClass'=>$func->getClosureScopeClass(),
			'getClosureThis'=>$func->getClosureThis(),
			'getExtension'=>$func->getExtension(),
			
			'fileName'=>$func->getFileName(),
			'startLine'=>$func->getStartLine(),
			'endLine'=>$func->getEndLine(),
			'staticVariables'=>$func->getStaticVariables(),
			'returnsReference'=>$func->returnsReference(),
			'numberOfParameters'=>$func->getNumberOfParameters(),
			'numberOfRequiredParameters'=>$func->getNumberOfRequiredParameters(),
			'parameters'=>$this->get_Parameters($func),
			'docComment'=>$func->getDocComment(),
		);
	}
	private function get_Parameters(\ReflectionFunctionAbstract $func) {
		$args=array();
		$a=$func->getParameters();
		foreach ($a as $param) $args[]=$this->get_Parameter($param);
		return $args;
	}
	private function get_Parameter(\ReflectionParameter $param) {
		return array(
			'name'=>$param->name,
			'defaultValue'=>$param->isDefaultValueAvailable()?$param->getDefaultValue():null,
			'isOptional'=>$param->isOptional(),
			'isPassedByReference'=>$param->isPassedByReference(),
			'class'=>($o=$param->getClass())?$o->getName():null,
			'text'=>"$param",
		);
	}
	private function get_Property(\ReflectionProperty $prop){
		return array(
			'name'=>$prop->name,
			'isDefault'=>$prop->isDefault(),
			'isPrivate'=>$prop->isPrivate(),
			'isProtected'=>$prop->isProtected(),
			'isPublic'=>$prop->isPublic(),
			'isStatic'=>$prop->isStatic(),
			'modifier'=>($prop->isPublic()?'public ':($prop->isPrivate()?'private ':($prop->isProtected()?'protected ':''))).($prop->isStatic()?'static ':''),
			'getModifiers'=>$prop->getModifiers(),
			'docComment'=>$prop->getDocComment(),
			'text'=>"$prop",
		);
	}
}