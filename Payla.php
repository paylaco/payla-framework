<?php

/**
 * This constant defines the framework installation directory.
 */
defined('PAYLA_PATH') or define('PAYLA_PATH', __DIR__);

/**
 * Payla is the core helper class for the Payla framework.
 *
 * This class uses some Yii1 and Yii2 functions
 *
 * @author Saeed Gholizadeh <gholizade.saeed@yahoo.com>
 * @since 1.5
 */
Class Payla{

    private static $_app;
    private static $_aliases=['payla'=>PAYLA_PATH]; // alias => path
    private static $_imports=[]; // alias => class name or directory

    // The singleton method
    public static function app()
    {
        return self::$_app;
    }

    public static function setApplication($app)
    {
        if(self::$_app===null || $app===null)
            self::$_app=$app;
        else
            throw new Exception('application can only be created once.');
    }

    public static function createComponent($config)
    {
        $args = func_get_args();
        if(is_string($config))
        {
            $type=$config;
            $config=[];
        }
        elseif(isset($config['class']))
        {
            $type=$config['class'];
            unset($config['class']);
        }
        else
            throw new Exception('Object configuration must be an array containing a "class" element.');

        if(!class_exists($type,false))
            $type=Payla::import($type,true);

        if(($n=func_num_args())>1)
        {
            if($n===2)
                $object=new $type($args[1]);
            elseif($n===3)
                $object=new $type($args[1],$args[2]);
            elseif($n===4)
                $object=new $type($args[1],$args[2],$args[3]);
            else
            {
                unset($args[0]);
                $class=new ReflectionClass($type);
                // Note: ReflectionClass::newInstanceArgs() is available for PHP 5.1.3+
                // $object=$class->newInstanceArgs($args);
                $object=call_user_func_array([$class,'newInstance'],$args);
            }
        }
        else
            $object=new $type;
        foreach($config as $key=>$value)
            $object->$key=$value;
        return $object;
    }


    /**
     * Imports a class or a directory.
     *
     * Importing a class is like including the corresponding class file.
     * The main difference is that importing a class is much lighter because it only
     * includes the class file when the class is referenced the first time.
     *
     * Importing a directory is equivalent to adding a directory into the PHP include path.
     * If multiple directories are imported, the directories imported later will take
     * precedence in class file searching (i.e., they are added to the front of the PHP include path).
     *
     * Path aliases are used to import a class or directory. For example,
     * <ul>
     *   <li><code>application.components.GoogleMap</code>: import the <code>GoogleMap</code> class.</li>
     *   <li><code>application.components.*</code>: import the <code>components</code> directory.</li>
     * </ul>
     *
     * The same path alias can be imported multiple times, but only the first time is effective.
     * Importing a directory does not import any of its subdirectories.
     *
     * Starting from version 1.1.5, this method can also be used to import a class in namespace format
     * (available for PHP 5.3 or above only). It is similar to importing a class in path alias format,
     * except that the dot separator is replaced by the backslash separator. For example, importing
     * <code>application\components\GoogleMap</code> is similar to importing <code>application.components.GoogleMap</code>.
     * The difference is that the former class is using qualified name, while the latter unqualified.
     *
     * Note, importing a class in namespace format requires that the namespace corresponds to
     * a valid path alias once backslash characters are replaced with dot characters.
     * For example, the namespace <code>application\components</code> must correspond to a valid
     * path alias <code>application.components</code>.
     *
     * @param string $alias path alias to be imported
     * @param boolean $forceInclude whether to include the class file immediately. If false, the class file
     * will be included only when the class is being used. This parameter is used only when
     * the path alias refers to a class.
     * @return string the class name or the directory that this alias refers to
     * @throws Exception if the alias is invalid
     */
    public static function import($alias,$forceInclude=false)
    {
        if(isset(self::$_imports[$alias]))  // previously imported
            return self::$_imports[$alias];

        if(class_exists($alias,false) || interface_exists($alias,false))
            return self::$_imports[$alias]=$alias;

        if(($pos=strrpos($alias,'\\'))!==false) // a class name in PHP 5.3 namespace format
        {
            $namespace=str_replace('\\','.',ltrim(substr($alias,0,$pos),'\\'));

            if(($path=self::getPathOfAlias($namespace))!==false)
            {
                $classFile=$path.DIRECTORY_SEPARATOR.substr($alias,$pos+1).'.php';
                if($forceInclude)
                {
                    if(is_file($classFile))
                        require($classFile);
                    else
                        throw new Exception("Alias '{$alias}' is invalid. Make sure it points to an existing PHP file and the file is readable.");
                    self::$_imports[$alias]=$alias;
                }

                return $alias;
            }
            else
            {
                // try to autoload the class with an autoloader
                if (class_exists($alias,true))
                    return self::$_imports[$alias]=$alias;
                else
                    throw new Exception("Alias '{$namespace}' is invalid. Make sure it points to an existing directory or file.");
            }
        }

        if(($pos=strrpos($alias,'.'))===false)  // a simple class name
        {
            // try to autoload the class with an autoloader if $forceInclude is true
            if($forceInclude && class_exists($alias,true))
                self::$_imports[$alias]=$alias;
            return $alias;
        }

        $className=(string)substr($alias,$pos+1);
        $isClass=$className!=='*';

        if($isClass && (class_exists($className,false) || interface_exists($className,false)))
            return self::$_imports[$alias]=$className;

        if(($path=self::getPathOfAlias($alias))!==false)
        {
            if($isClass)
            {
                if($forceInclude)
                {
                    if(is_file($path.'.php'))
                        require($path.'.php');
                    else
                        throw new Exception("Alias '{$alias}' is invalid. Make sure it points to an existing PHP file and the file is readable.");
                    self::$_imports[$alias]=$className;
                }

                return $className;
            }
            else  // a directory
            {
                if(self::$_includePaths===null)
                {
                    self::$_includePaths=array_unique(explode(PATH_SEPARATOR,get_include_path()));
                    if(($pos=array_search('.',self::$_includePaths,true))!==false)
                        unset(self::$_includePaths[$pos]);
                }

                array_unshift(self::$_includePaths,$path);

                if(self::$enableIncludePath && set_include_path('.'.PATH_SEPARATOR.implode(PATH_SEPARATOR,self::$_includePaths))===false)
                    self::$enableIncludePath=false;

                return self::$_imports[$alias]=$path;
            }
        }
        else
            throw new Exception("Alias '{$alias}' is invalid. Make sure it points to an existing directory or file.");
    }

    /**
     * Translates an alias into a file path.
     * Note, this method does not ensure the existence of the resulting file path.
     * It only checks if the root alias is valid or not.
     * @param string $alias alias (e.g. system.web.CController)
     * @return mixed file path corresponding to the alias, false if the alias is invalid.
     */
    public static function getPathOfAlias($alias)
    {
        if(isset(self::$_aliases[$alias]))
            return self::$_aliases[$alias];
        elseif(($pos=strpos($alias,'.'))!==false)
        {
            $rootAlias=substr($alias,0,$pos);
            if(isset(self::$_aliases[$rootAlias]))
                return self::$_aliases[$alias]=rtrim(self::$_aliases[$rootAlias].DIRECTORY_SEPARATOR.str_replace('.',DIRECTORY_SEPARATOR,substr($alias,$pos+1)),'*'.DIRECTORY_SEPARATOR);
        }
        return false;
    }
}