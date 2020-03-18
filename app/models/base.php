<?php
class Base_Model extends Model
{
    public $page_title;
    public $active_button;
    
    private $_metaTags;
    private $_javascriptTags;
    private $_cssTags;
    
    private $uri_string;

    public function __construct($page=null, $uri_string)
    {
        parent::__construct();
        
        $this->uri_string = $uri_string;
        
        $this->_metaTags = array();
        $this->_javascriptTags = array();
        $this->_cssTags = array();
        
        $this->session = Session::instance();
        
        $this->active_button = $page ? $page : 'home';
        $this->loadDefaultTags();
    }
    
/////////////////////////////////////////////////////////////////////////
// Public Methods
/////////////////////////////////////////////////////////////////////////

    public function primaryNavigation()
    {
        return array(
            array('Internet Retailers', 'internet_retailers', 35), // url/active_button, css margin
            array('Networks', 'networks', 30),
            array('Publishers', 'publishers', 35),
            array('About Us', 'about', 35),
            array('Resources', 'resources_events', 30),
            array('Contact Us', 'contact', 0)
        );
    }
    
    ////////////////////////////////////////////
    // Collections
    ////////////////////////////////////////////
    
    public function getMetaTags()
    {
        return $this->_metaTags;
    }
    
    public function addMetaTag($tag)
    {
        array_push( $this->_metaTags, $tag );
    }
    
    
    public function getJavascriptTags()
    {
        return $this->_javascriptTags;
    }
    
    public function addJavascriptTag($tag)
    {
        array_push( $this->_javascriptTags, $tag );
    }
    
    
    public function getCssTags()
    {
        return $this->_cssTags;
    }
    
    public function addCssTag($tag)
    {
        array_push( $this->_cssTags, $tag );
    }

/////////////////////////////////////////////////////////////////////////
// Private Methods
/////////////////////////////////////////////////////////////////////////

    /**
    * get the website meta tags
    */
    private function loadDefaultTags()
    {
        $result = $this->db->query('SELECT * FROM website_meta_tags WHERE uri_segment = "' . $this->uri_string . '"');
        
        if( !count($result) )
            // meta tags do not exist for requested page.
            $result = $this->db->query('SELECT * FROM website_meta_tags WHERE uri_segment = "home"');

        foreach($result as $row)
        {
            $this->page_title = $row->title;
            $this->addMetaTag('<meta name="description" content="'. $row->description .'" />');
            $this->addMetaTag('<meta name="keywords" content="'. $row->keywords .'" />');
            $this->addMetaTag('<meta name="robots" content="'. $row->robots .'" />');
        }

        $this->addCssTag('<link rel="stylesheet" type="text/css" href="/media/css/base.css" />');
        $this->addCssTag('<link rel="stylesheet" type="text/css" href="/media/css/legacy.css" />');
        //$this->addCssTag('<link rel="stylesheet" href="/media/css/lightbox.css" type="text/css" media="screen" />');
        
        $this->addJavascriptTag('<script language="javascript">AC_FL_RunContent = 0;</script>');
        $this->addJavascriptTag('<script src="/media/js/AC_RunActiveContent.js" language="javascript"></script>');
    }
    
}
