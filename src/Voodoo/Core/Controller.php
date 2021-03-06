<?php

/**
 * -----------------------------------------------------------------------------
 * VoodooPHP
 * -----------------------------------------------------------------------------
 * @author      Mardix (http://twitter.com/mardix)
 * @github      https://github.com/VoodooPHP/Voodoo
 * @package     VoodooPHP
 *
 * @copyright   (c) 2013 Mardix (http://github.com/mardix)
 * @license     MIT
 * -----------------------------------------------------------------------------
 *
 * @name        Controller
 * @desc        This is the abstract controller which must be extended to read the proper controller name and actions
 *              Controller contains necessary methods for your own controller
 *
 */

namespace Voodoo\Core;

use ReflectionClass,
    DateTime;

abstract class Controller
{
    use Controller\TAnnotation;
    use Controller\TPagination;


    /**
     * Segments passed
     * @var array
     */
    private $segments = array();

    /**
     * The full namespace of the controller
     * @var string
     */
    protected $namespace = "";

    /**
     * The module name
     * @var String
     */
    protected $moduleName = "";

    /**
     * The controller's name
     * @var String
     */
    protected $controllerName = "";

    /**
     * The action's name being called
     * @var string
     */
    protected $actionName = "";
    // The action view to use
    protected $actionView = "";

    /**
     * @var Core\View()
     */
    private $view = null;

    /**
     * @var Core\INI
     */
    private $config = null;

    /**
     * Local vars
     * @var bool
     */
    private $disableView = false;
    private $moduleDir = "";
    private $applicationDir = "";
    private $moduleNamespace = "";
    private $controllerNamespace = "";

    /**
     * The default status code
     * @var int
     */
    protected $httpStatusCode = 200;

    /**
     * Flag to abort the execution of a controller
     * @var bool
     */
    protected $abort = false;

    /**
     * The reflection of the called class
     * @var \Reflection
     */
    protected $reflection = null;

//------------------------------------------------------------------------------
    /**
     * This is the index action.
     * It is loaded by default or an action is missing
     * Every controller requires it
     */
    abstract public function actionIndex();
//------------------------------------------------------------------------------

    /**
     * final construct so no other class can override it
     * To load something in the constructor, use init()
     * 
     * @params array $segments - extra segments from that can be accessed with getParams()
     */
    final public function __construct(Array $segments = [])
    {
        /**
         * Built variables based on the controller
         */
        $this->reflection = new ReflectionClass(get_called_class());

        $namespace = $this->reflection->getNamespaceName();

        $nsArr = explode("\\", $namespace);
        $this->moduleName = current(array_splice($nsArr, -2));
        $this->namespace = $this->reflection->getName();
        $this->controllerName = $this->reflection->getShortName();
        $this->controllerNamespace = $namespace;
        $this->moduleDir = dirname(dirname($this->reflection->getFileName()));
        $this->applicationDir = dirname($this->moduleDir);
        $this->moduleNamespace = $this->getParentNamespace($namespace);

        $this->segments = array_values(array_filter($segments));

        $this->init();
    }

    /**
     * init()
     * __construct is and can't be overriden by any child class
     * init() lets you put code that could be executed in __construct()
     * @return \Voodoo\Core\Controller
     */
    protected function init()
    {}

    /**
     * beforeAction()
     * Execute the method before the Action is executed
     * @return \Voodoo\Core\Controller
     */
    protected  function beforeAction()
    {}
    
    /**
     * afterAction()
     * Execute the method after the Action is executed
     * @return \Voodoo\Core\Controller
     */    
    protected function afterAction()
    {}    
    
    /**
     * finalize()
     * Code to excute before rendering
     * @return \Voodoo\Core\Controller
     */
    protected function finalize()
    {
        Http\Response::setStatus($this->httpStatusCode);
        return $this;
    }

    /**
     * It's a wrap
     * By default, when the destructor is called, it will render the views
     * To disable view, in your controller set: $this->disableView(true)
     */
    final public function __destruct()
    {
        if (! $this->abort) {
            $this->finalize();
            $this->renderView();
        } 
    }

    /**
     * __destruct() will still be executed even if the explicit call of exit();
     * abort() will force the destructor to not execute the finalize or render
     * An explicit call to exit() is also necessary after $this->abort() to 
     * completely exit out
     * 
     * @param bool $abort
     * @return \Voodoo\Core\Controller
     */
    final protected function abort($abort = true)
    {
        $this->abort = $abort;
        return $this;
    }
//------------------------------------------------------------------------------

    /**
     * Get POST ot GET params
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getParam($key = null, $default = null)
    {
        return Http\Request::getParam($key, $default);
    }

    /**
     * Segements are part of the URL separated by /
     * ie: /gummy/bear/?q=hello 'gummy' and 'bear' are segments.
     * @param mixed (int | string) - if int, i will pick the index of the . If a string, it will return the k/v pair of the segemnt
     * @param int Where to start the segment
     * @return mixed
     */
    public function getSegment($key = null, $offset = 0)
    {
        return Http\Request::getSegment($key, $offset, $this->segments);
    }

    /**
     * To catch the first numeric value from the URL segment. ie: /music/rap/12573/Where-Have-You-Been. Will return 12573
     * @return mixed (int | null)
     */
    public function catchNumericSegment()
    {
        foreach ($this->segments as $s) {
            if (is_numeric($s)) {
                return $s;
            }
        }
        return null;
    }

//------------------------------------------------------------------------------

    /**
     * Return the module's name
     * @return string
     */
    public function getModuleName()
    {
        return $this->moduleName;
    }

    public function getModuleNamespace()
    {
        return $this->moduleNamespace;
    }
    /**
     * Return the controller's name
     * @return string
     */
    public function getControllerName()
    {
        return $this->controllerName;
    }

    /**
     * Get the module directory
     *
     * @return string
     */
    public function getModuleDir()
    {
        return $this->moduleDir;
    }

    /**
     * Get the application
     *
     * @return string
     */
    public function getApplicationDir()
    {
        return $this->applicationDir;
    }

    /**
     * To get the request uri. It includes everything in the URI
     *
     * @return string
     */
    public function getRequestURI()
    {
        return $_SERVER["REQUEST_URI"];
    }

    /**
     * Return the root dir relative to the Application dir
     * Use it to include files, or get relative path of file
     * @return string
     */
    public function getBaseDir()
    {
        return Env::getApplicationBaseDir();
    }

    /**
     * Return the root url which will properly format the url so it adds or not ? to make the relative link
     * @uses    : to make links
     * @example : http://mysite.com/? -> http://mysite.com/?/ModuleName if htaccess is missing, or http://mysite.com/ModuleName is htaccess is here
     * @return string
     */
    public function getBaseUrl()
    {
        $questionMark = Config::System()->get("useUrlQuestionMark");
        return $this->getSiteUrl().($questionMark ? "/?" : "");
    }

    /**
     * Return the site url itself
     *
     * @uses    : To get the site url
     * @example : http://mysite.com/
     * @return string
     */
    public function getSiteUrl()
    {
        return Env::getUrl();
    }

    /**
     * Return the URL of the module
     *
     * @uses    : Get the module url
     * @return string
     */
    public function getModuleUrl()
    {
        $module = (($this->moduleName == "Main") ? "" : $this->moduleName);
        $module = $this->dasherizeUrl($module);
        return preg_replace("/\/$/", "", $this->getBaseUrl() . "/" . $module );
    }
    
    /**
     * Return the url for the controller
     * 
     * @return string
     */
    public function getControllerUrl()
    {
        $url = $this->getModuleUrl();
        $controller = (($this->controllerName == "Index") ? "" : $this->controllerName);
        if($controller) {
            $url .= "/".($this->dasherizeUrl($controller));
        }
        return preg_replace("/\/$/", "", $url );
    }

    /**
     * Return the action url
     * 
     * @return string
     */
    public function getActionUrl()
    {
        $url = $this->getControllerUrl();
        $action = $this->getActionName() == "Index" ? "" : $this->getActionName();
        if ($action) {
            $url .= "/".($this->dasherizeUrl($action));
        }
        return preg_replace("/\/$/", "", $url );
    }
    
    /**
     * Dasherize part for a url
     * 
     * @param type $str
     * @return string
     */
    private function dasherizeUrl($str)
    {
        return strtolower(Helpers::dasherize($str));
    }
    /*     * **************************************************************************** */

// CONTROLLER
    /**
     * To access another controller without rendering it
     *
     * @param string $controllerName. If it starts with \ (backslash)
     * it will load it from the absolute path, otherwise it loads from the current namespace
     * @param array $params
     * @return \Voodoo\Core\controller
     * @throws Exception
     */
    protected function getController($controllerName, Array $params = [])
    {
        $controller = (strpos('\\',$controllerName) === 0)
                        ? $controller
                        : $this->controllerNamespace."\\".Helpers::camelize($controllerName, true);

        $clsRef = new ReflectionClass($controller);

        if ($clsRef->isSubclassOf(__CLASS__)) {
            return (new $controller($params))->disableView();
        } else {
            throw new Exception\Controller("Controller '$controller' doesn't 
                            exists or not an instance of Voodoo\Core\Controller");
        }
    }

    /**
     * forward, like getController, forward the current controller to a new controller
     * and allows it to render the view, while it deactivate the current controller view.
     * All the settings and params will be forwarded to the new controller
     * @param string $controllerName
     * @return \Voodoo\Core\Controller
     */
    protected function forwardTo($controllerName, Array $params = [])
    {
        $this->disableView()->abort();
        $params = array_merge_recursive($this->segments, $params);
        $controller = $this->getController($controllerName, $params)
                            ->disableView(false);
        return $controller;
    }

    /*     * **************************************************************************** */

// ACTION
    /**
     * Load an action by providing just the name without the Action suffix.
     * Its purpose is to set the action to be rendered. You still can access the method the normal way $this->action_index
     * i.e $this->getAction("index");
     * @param  string     $action       - The action name without Action as suffix. ie: action_index() =  getAction("index")
     * @return \Voodoo\Core\Controller
     * 
     * NOTE:
     * actions make use of the annotations
     * 
     *  @action_view 
     *  @use_layout
     *  @request
     *  @render_as
     */
    public function getAction($action = "Index")
    {
        $executeAction = true;
        $layout = "";
        $render = "";
        $this->setActionName($action);

        $actionName = $this->getActionMethodName();

        if (method_exists($this, $actionName)) {
            $actionView = $this->getActionName();
            
            /** @Actions Annotations **/
                // @action_view $view-file-name : To change the default view
                if($this->getActionAnnotation("action_view")) {
                   $actionView =  $this->getActionAnnotation("action_view");
                }
                // @use_layout $layout-file-name (_layout/main): to change the layout
                if($this->getActionAnnotation("use_layout")) {
                   $layout =  $this->getActionAnnotation("use_layout");
                }              
                // @render_as (JSON|HTML) : By default it will render HTML, set to JSON the view will be rendered as JSON
                if($this->getActionAnnotation("render_as")) {
                   $render =  $this->getActionAnnotation("render_as");
                }               
                /**
                 * @request
                 * Requires an action to accept a request method: POST | GET | PUT | DELETE
                 * The @request annotation is array containing the keys: method, response, view
                 * - Annotation
                 *      @request Array
                 * - arguments
                 *      method (POST|GET|PUT|DELETE) - The request method to accept
                 *      response - a message to display if the request method fails
                 *      action_view -  a view to display instead of the _includes/error/405
                 * - example: 
                 *      @request [method=POST, response="This is an error message", action_view="_includes/error/405"]
                 */
                $request = $this->getActionAnnotation("request");
                if(is_array($request) && $request["method"]) {
                    if (! Http\Request::is($request["method"])) {
                        $this->setHttpCode(405);
                        
                        if ($this->view() instanceof View) {
                            $this->view()->setError($request["response"]);
                            $this->view()->assign("error", $request["response"]);                            
                        }

                        if (isset($request["action_view"])) {
                            $actionView = $request["action_view"];
                        } else {
                            if ($this->view() instanceof View) {
                                $this->view()->setViewError(405);    
                            }
                            return $this;
                        }
                        $executeAction = false;
                    }
                }                
             /** ~~~~~~ **/
                
            $this->setActionView($actionView);
            
            if ($this->view() instanceof View) {
                $this->view()->setActionView($actionView);
                if ($layout) {
                   $this->view()->useLayout($layout); 
                }  
                if (strtoupper($render) == "JSON") {
                    $this->view()->renderToJson();
                }
            }

            if ($executeAction) {
                
                $this->beforeAction();
                
                $this->{$actionName}();
                
                $this->afterAction();
            }
            
        } else {
            throw new Exception\Action("Action '{$actionName}' is missing");
        }
        return $this;
    }

    /**
     * Set the action name
     *
     * @param string $action
     */
    protected function setActionName($action)
    {
       $this->actionName = Helpers::camelize($action, true);
       return $this;
    }

    /**
     * Return the full name of the method
     * @return string
     */
    public function getActionMethodName()
    {
        return "action".$this->getActionName();
    }

    /**
     * Return the last action name saved
     * @return string
     */
    public function getActionName()
    {
        return $this->actionName;
    }

    /**
     * Set the action view to be displayed
     * @param  type       $view
     * @return Controller
     */
    protected function setActionView($view)
    {
        $this->actionView = $view;
        return $this;
    }

    /**
     * Return the action view
     * @return type
     */
    protected function getActionView()
    {
        return $this->actionView;
    }

// VIEW

    /**
     * Return the View instance
     * @param Array $dataModel - Data model to assign to the view
     * 
     * @return Voodoo\Core\View
     */
    protected function view(Array $dataModel = null)
    {
        if (! $this->view){
            $this->view = new View($this);
        }
        if ($dataModel) {
            $this->view->assign($dataModel);
        }

        return $this->view;
    }

    /**
     * To render the controller's view
     * 
     * @param  bool    $echoView - to print the view or just return it
     * @return boolean | string
     */
    protected function renderView($echoView = true)
    {
        if ($this->disableView || !$this->viewExists()) {
            return false;
        } else {
            
            if ($this->issetPagination()) {
                $this->view()->setPagination($this->pagination()->toArray());
            }
            
            if (! $this->view()->issetLayout()) {
                $layout = $this->getConfig("views.layout");    
                if($layout) {
                    $this->view()->useLayout($layout);
                }                
            }
            
            $content = $this->view()->render();
            
            if ($echoView) {
                echo $content;
            } else {
                return $content;
            }
        }
    }

    /**
     * To enable render view. on __destruct, it will render the view, otherwise it's up to the controller to launch it.
     * @param  bool       $en
     * @return \Voodoo\Core\Controller
     */
    public function disableView($bool = true)
    {
        $this->disableView = $bool;
        return $this;
    }

    /**
     * Verify if the view directory exists
     * 
     * @return bool
     */
    protected function viewExists()
    {
        return is_dir($this->moduleDir."/Views");
    }
    
    /*     * **************************************************************************** */
// CONFIG

    /**
     * To access config info
     * @param  type  $key
     * @return mixed
     */
    public function getConfig($key = null)
    {
        if (!$this->config) {
            $appConfigFile = "{$this->applicationDir}/Config".Config::EXT;
            $this->config = (new Config($this->controllerNamespace))
                                ->loadFile($appConfigFile);
            
            // If a config file exist in the module, we'll load it
            $moduleConfigFile = "{$this->moduleDir}/Config".Config::EXT;
            if (file_exists($moduleConfigFile)) {
                $this->config->loadFile($moduleConfigFile);    
            }                  
        }
        return $this->config->get($key);
    }

    /*     * **************************************************************************** */

    /**
     * Return a formatted date
     * @param mixed $datetime
     * @param string format ie: M-d-Y - the format to use, or will use the config default
     * @return string
     */
    public function toDate($datetime, $format = null)
    {
        $format = $format ? : $this->getConfig("views.dateFormat");
        return (new DateTime($datetime))->format($format);
    }

    /**
     * Return a string to friendly url
     * @param  type   $url
     * @return string
     */
    public function toFriendlyUrl($url)
    {
        return Helpers::toFriendlyUrl($url);
    }

    /*     * **************************************************************************** */

    /**
     * Bool if the request method is a POST
     * @return bool
     */
    public function isPost()
    {
        return Http\Request::is("POST");
    }

    /**
     * Bool if the request method is a GET
     * @return bool
     */
    public function isGet()
    {
        return Http\Request::is("GET");
    }

    /**
     * CHeck request if it's an ajax request
     * @return bool
     */
    public function isAjax()
    {
        return Http\Request::isAjax();
    }


    /**
     * Set the http status code
     *
     * @param int $code
     * @return Voodoo\Core\Controller
     */
    public function setHttpCode($code = 200)
    {
        $this->httpStatusCode = $code;
        return $this;
    }
    /*     * **************************************************************************** */

    /**
     * To redirect the page to a new page
     * @param string $path
     * @param int $httpCode
     */
    public function redirect($url = "", $httpCode = 302)
    {
        if (! $url) {
            $url = $this->getControllerUrl();
        }
        
        if (preg_match("/^http/", $url)) { // http://xyz
            $url = $url;
        } else if(preg_match("/^\//", $url)) { // we'll add the ? if possible
            $url = $this->getBaseUrl().$url;
        } else { // go to the current module
            $url = $this->getModuleUrl()."/{$url}";
        }

        $this->abort();
        return Http\Response::redirect($url, $httpCode);
    }

    /**
     * Return the parent namespace
     * @param type $namespace
     * @return string
     */
    private function getParentNamespace($namespace)
    {
        $nsArr = explode("\\", $namespace);
        return implode("\\",array_splice($nsArr,0,-1));
    }

    /**
     * Return the full name of the class
     *
     * @return string
     */
    public function __toString()
    {
        return $this->controllerNamespace;
    }
}
