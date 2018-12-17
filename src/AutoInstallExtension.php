<?php
/**
 * Author: Mykola Chomenko
 * Email: mykola.chomenko@dipcom.cz
 * Created: 03.11.2018 18:28
 */

namespace Chomenko\AutoInstall;

use Nette\Configurator;
use Nette\DI\Compiler;
use Nette\DI\CompilerExtension;
use Nette\DI\ServiceDefinition;
use Nette\Loaders\RobotLoader;
use Doctrine\Common\Annotations\AnnotationReader;


class AutoInstallExtension extends CompilerExtension
{

    public $configuration = array(
        'dirs' => array(),
        'temp_dir' => ""
    );

    public function loadConfiguration()
    {

		$parameters = $this->getContainerBuilder()->parameters;

		if (array_key_exists("tempDir", $parameters)) {
			$this->configuration["temp_dir"] = $parameters["tempDir"]."/autoInstall";
		}

        $this->config = $this->getConfig($this->configuration);
        $builder = $this->getContainerBuilder();

        foreach ($this->getClassesList() as $class){

            $reflect = new \ReflectionClass($class);
            $definition = $builder->addDefinition(str_replace('\\', '_', $class));

            $interface_name = $this->getInterfaceName($class);
            if(interface_exists($interface_name)){
                $definition->setImplement($interface_name);
            }

            $definition->setFactory($class);
            $definition->setInject(true);

            $this->applyTags($reflect, $definition);
        }
    }

	/**
	 * @param \ReflectionClass $reflect
	 * @param ServiceDefinition $definition
	 * @throws \Doctrine\Common\Annotations\AnnotationException
	 */
    private function applyTags(\ReflectionClass $reflect, ServiceDefinition $definition)
    {

		$reader = new AnnotationReader();
		$classAnnotations = $reader->getClassAnnotations($reflect);

		foreach ($classAnnotations as $annotation) {
			if ($annotation instanceof Tag) {
				foreach ($annotation->getTags() as $tag) {
					$definition->addTag($tag);
				}
			}
		}

		$consoleClass = "Symfony\Component\Console\Command\Command";
		$kdybyClass = "Kdyby\Console\DI\ConsoleExtension";

		if (class_exists($consoleClass) && class_exists($kdybyClass)) {
			if($reflect->isSubclassOf(\Symfony\Component\Console\Command\Command::class)) {
				$definition->addTag(\Kdyby\Console\DI\ConsoleExtension::TAG_COMMAND);
			}
		}
    }


    /**
     * @param string $class
     * @return string
     */
    private function getInterfaceName(string $class) : string
    {
        $path = explode('\\', $class);
        $class_name = 'I'.end($path);
        $keys = array_keys($path);
        $key = end($keys);
        $path[$key] = $class_name;

        return implode('\\',$path);
    }


    /**
     * @return array
     * @throws \ReflectionException
     */
    private function getClassesList() : array
    {
        $this->checkTempDir();

        $class_list = array();
        $loader = new RobotLoader();
        $loader->setTempDirectory($this->config['temp_dir']);

        foreach ($this->config['dirs'] as $dir){
            $loader->addDirectory($dir);
        }

        $loader->register();
        foreach ($loader->getIndexedClasses() as $class_name => $file){
            $reflect = new \ReflectionClass($class_name);

            if($reflect->isAbstract()){
                continue;
            }

            if($reflect->isInterface()){
                continue;
            }

            if($reflect->implementsInterface(AutoInstall::class)) {
                $class_list[] = $class_name;
            }
        }

        return (array) $class_list;
    }


    private function checkTempDir()
    {
        if(!file_exists($this->config['temp_dir'])){
            mkdir($this->config['temp_dir']);
            chmod($this->config['temp_dir'], 0777);
        }
    }


    /**
     * @param Configurator $configurator
     */
    public static function register(Configurator $configurator)
    {
        $configurator->onCompile[] = function ($config, Compiler $compiler){
            $compiler->addExtension('FormModal', new AutoInstallExtension());
        };
    }

}