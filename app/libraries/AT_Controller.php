<?php
abstract class Controller extends Controller_Core
{
    private $base_view;
    private $base_model;
    
    private $content_view;
    private $content_model;
    
    // Default to do auto-rendering
    public $auto_render = TRUE;

    /**
     * Template loading and setup routine.
     */
    public function __construct(View $view=null, Model $model=null)
    {
        parent::__construct();
        
        // Load TEMPLATE View
        $this->base_view = $view ? $view : new View('__templates/base_view');
        // Load and pass the TEMPLATE Model to the TEMPLATE View
        $this->base_model = $model ? $model : new Base_Model($this->uri->segment(1), $this->uri->string());
        $this->base_view->model = $this->base_model;

        // Render the template immediately after the controller method
        if ($this->auto_render == TRUE)
            Event::add('system.post_controller', array($this, '_render'));
    }
    
    /**
    * Bind the view class to $content in the base view.
    * Passes the model class to $view->model.
    * 
    * @param View $view
    * @param Model $model
    */
    public function bindContentView(View $view, Model $model=null)
    {
        $this->base_view->content = $view;
        $this->content_view = $view;
        
        if( $model != null )
        {
            $this->content_model = $model;
            $view->model = $model;
        }
    }
    
    /**
    * Gets the model for the page template.
    */
    public function getBaseModel()
    {
        return $this->base_model;   
    }

    /**
    * Gets the CONTENT view after bindContentView() is called.
    */
    public function getContentView()
    {
        return $this->content_view;
    }
    
    /**
    * Gets the CONTENT model after bindContentView() is called.
    */
    public function getContentModel()
    {
        return $this->content_model;
    }

    /**
    * If not method is in the subclass, the uir->string will call a view.
    * 
    * @param String $name
    * @param mixed $arguments
    * @return void
    */
    public function __call($name, $arguments=null) {
        //echo APPPATH . 'views/' . $this->uri->string();
        
        if (! file_exists(APPPATH . 'views/' . $this->uri->string() . '.php') )
        {
        	header("HTTP/1.0 404 Not Found");
			$this->content_view = new View( 'home/index_view' );
       		$this->base_view->content = $this->content_view;
            return;
		}
        
        $this->content_view = new View( $this->uri->string() );
        $this->base_view->content = $this->content_view;
    }
    
    /**
     * Render the loaded template.
     */
     public function _render()
     {
        if ($this->auto_render == TRUE)
        {
            // Render the Views when the class is destroyed
            $this->base_view->render(TRUE);
        }
     }
     
     abstract public function index();
}