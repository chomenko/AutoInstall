<?php
/**
 * Author: Mykola Chomenko
 * Email: mykola.chomenko@dipcom.cz
 * Created: 03.11.2018 18:28
 */

namespace Chomenko\AutoInstall;

use Chomenko\AutoInstall\Config\Implement;
use Chomenko\AutoInstall\Config\Tag;
use Doctrine\Common\Annotations\AnnotationException;
use Nette\Configurator;
use Nette\DI\Compiler;
use Nette\DI\CompilerExtension;
use Nette\DI\ServiceDefinition;
use Nette\Loaders\RobotLoader;
use Doctrine\Common\Annotations\AnnotationReader;

class AutoInstallExtension extends CompilerExtension
{

	/**
	 * @var AnnotationReader
	 */
	protected $annotReader;

	/**
	 * @var array
	 */
	public $configuration = [
		'dirs' => [],
		'temp_dir' => "",
	];

	/**
	 * @throws AnnotationException
	 */
	public function __construct()
	{
		$this->annotReader = new AnnotationReader();
	}

	/**
	 * @throws \ReflectionException
	 */
	public function loadConfiguration()
	{
		$parameters = $this->getContainerBuilder()->parameters;

		if (array_key_exists("tempDir", $parameters)) {
			$this->configuration["temp_dir"] = $parameters["tempDir"] . "/autoInstall";
		}

		$this->config = $this->getConfig($this->configuration);
		$builder = $this->getContainerBuilder();

		foreach ($this->getClassesList() as $class) {

			$reflect = new \ReflectionClass($class);
			$definition = $builder->addDefinition(str_replace('\\', '_', $class));

			if (!$this->applyImplements($reflect, $definition)) {
				$interfaceName = $this->getInterfaceName($class);
				if (interface_exists($interfaceName)) {
					$definition->setImplement($interfaceName);
				}
			}

			$definition->setFactory($class);
			$definition->setInject(TRUE);
			$this->applyTags($reflect, $definition);
		}
	}

	/**
	 * @param \ReflectionClass $reflect
	 * @param ServiceDefinition $definition
	 */
	private function applyTags(\ReflectionClass $reflect, ServiceDefinition $definition)
	{
		$classAnnotations = $this->annotReader->getClassAnnotations($reflect);

		foreach ($classAnnotations as $annotation) {
			if ($annotation instanceof Tag) {
				foreach ($annotation->getTags() as $tag) {
					$definition->addTag($tag);
				}
			}
		}
	}

	/**
	 * @param \ReflectionClass $reflect
	 * @param ServiceDefinition $definition
	 * @return bool
	 */
	private function applyImplements(\ReflectionClass $reflect, ServiceDefinition $definition)
	{
		$classAnnotations = $this->annotReader->getClassAnnotations($reflect);

		foreach ($classAnnotations as $annotation) {
			if ($annotation instanceof Implement) {
				$definition->setImplement($annotation->getValue());
				return TRUE;
			}
		}
		return FALSE;
	}

	/**
	 * @param string $class
	 * @return string
	 */
	private function getInterfaceName(string $class): string
	{
		$path = explode('\\', $class);
		$className = 'I' . end($path);
		$keys = array_keys($path);
		$key = end($keys);
		$path[$key] = $className;
		return implode('\\', $path);
	}

	/**
	 * @return array
	 * @throws \ReflectionException
	 */
	private function getClassesList() : array
	{
		$this->checkTempDir();

		$classList = [];
		$loader = new RobotLoader();
		$loader->setTempDirectory($this->config['temp_dir']);

		foreach ($this->config['dirs'] as $dir) {
			$loader->addDirectory($dir);
		}

		$loader->register();
		foreach ($loader->getIndexedClasses() as $className => $file) {
			$reflect = new \ReflectionClass($className);

			if ($reflect->isAbstract()) {
				continue;
			}

			if ($reflect->isInterface()) {
				continue;
			}

			if ($reflect->implementsInterface(AutoInstall::class)) {
				$classList[] = $className;
			}
		}
		return (array)$classList;
	}

	private function checkTempDir()
	{
		if (!file_exists($this->config['temp_dir'])) {
			mkdir($this->config['temp_dir']);
			chmod($this->config['temp_dir'], 0777);
		}
	}


	/**
	 * @param Configurator $configurator
	 */
	public static function register(Configurator $configurator)
	{
		$configurator->onCompile[] = function ($config, Compiler $compiler) {
			$compiler->addExtension('FormModal', new AutoInstallExtension());
		};
	}

}