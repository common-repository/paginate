<?php
/*
Plugin Name: Paginate
Plugin URI: http://wordpress.org/plugins/paginate/
Description: Display your list with pagination by a simple function call.
Version: 1.0
Author: Ivan Jakesevic
Author URI: http://www.eskamoe.com

Copyright 2013  Ivan Jakesevic (email:ivan82@gmail.com)

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/


class paginate{    
    //functionality variables
    public $totalItems;
    public $itemsPerPage;
    public $adjacents;
    public $pageQuery;
    
    //the value is only fetched if no onPage value is provided
    //if the value of the page should be fetched automatically
    public $getPageValueAutomatically = true;

    
    //display variables
    public $firstAndLastButtonAsNumber = true;
    public $prevButtonVisible = true;
    public $nextButtonVisible = true;
    public $prevButtonAtEndVisible = true;
    public $nextButtonAtEndVisible = true;
    public $dotsVisible = true;
    public $firstButtonVisible = true;
    public $lastButtonVisible = true;
    
    function __construct($totalItems = 0, $itemsPerPage = 10, $adjacents = 6, $pageQuery = 'page') {
        $this->totalItems = $totalItems;
        $this->itemsPerPage = $itemsPerPage;
        $this->adjacents = $adjacents;
        $this->pageQuery = $pageQuery;
        
        add_action( 'wp_enqueue_scripts', array( $this, 'paginate'));
        add_action('init', array($this, 'init'));
    }
    
    public function init(){
        load_plugin_textdomain('paginate', PLUGINDIR . '/paginate/localization');
    }

    //checks if all variables are ok, not implemented
    function validate(){
        if($this->totalItems < 1){ return;}
    }
    
    //claculates the total pages
    function get_total_pages($totalItems = NULL, $itemsPerPage = NULL){
        if(!$totalItems){ $totalItems = $this->totalItems;}
        if(!$itemsPerPage){ $itemsPerPage = $this->itemsPerPage;}
        return ceil($totalItems / $itemsPerPage);
    }
    
    //calculates the offset
    function get_offset($onPage = NULL, $itemsPerPage = NULL){
        if(!$onPage){ $onPage = get_onPage();}
        if(!$itemsPerPage){ $itemsPerPage = $this->itemsPerPage;}
        return ($onPage - 1) * $itemsPerPage;
    }
    
    
    function get_onPage(){
        $pageQuery = $this->pageQuery;
        if(isset($_GET[$pageQuery])){
            return $_GET[$pageQuery];
        }else{
            return 1;
        }        
    }

    //returns a string containing the pagination, remember to set $totalItems before call
    function get_pagination($onPage = NULL){  
        //get onPage argument, if no argument is provided try to extract it from url
        if($this->getPageValueAutomatically && !$onPage){
            $onPage = $this->get_onPage();
        }
        
        $totalPages = $this->get_total_pages();
        $pages = $this->get_pagination_array($totalPages, $onPage, $this->adjacents);
        
        $link =  $this->query_key_add($this->pageQuery);
        return $this->get_pagination_string($pages, $totalPages, $onPage, $link);
    }

    
    //returns items based on totalpages, onPage and adjacments, first page, last page are not included in the array
    function get_pagination_array($totalPages, $onPage, $adjacents){
        //get the pagination start position
        $start = max(1, min($totalPages - $adjacents, $onPage - ceil($adjacents / 2)));
        //get the pagination end position
        $end = $onPage + ceil($adjacents / 2);
        //guard
        if($end > $totalPages){ $end = $totalPages; }
        
        return range($start , $end);
    }
    
    //returns the html pagination
    function get_pagination_string($pagesArray, $totalPages, $onPage, $link){
        $html = "<span class=\"paginate\">";
        //prev page button 
        if($this->prevButtonVisible){
            if($onPage > 1){
                $html .= "<a class=\"prev\" href=\"". $link . ($onPage - 1) ."\">". __("prev") ."</a>";
            }elseif($this->prevButtonAtEndVisible){
                $html .= "<span class=\"prev\">". __("prev") ."</span>";
            }
        }

        //add the first page button
        if($this->firstButtonVisible && $pagesArray[0] != 1){
            $buttonText = $this->firstAndLastButtonAsNumber ? '1' : __("first");
            $html .= "<a class=\"first\" href=\"". $link. "1\" \>". $buttonText ."</a>";
        }

        //add the dots
        if($this->dotsVisible && $pagesArray[0] > 2){
            $html .= "<span class=\"dots\">". __("...") ."</span>";
        }


        //add all the remaining buttons, pagination
        foreach($pagesArray as &$value){
            //check if we are on the current page
            if($value == $onPage){
                $html .= "<span class=\"current\">".$value."</span>";
            }else{
                $html .= "<a href=\"". $link. $value ."\"\>".$value."</a>";
            }       
        }

        //$value contains the last page
        $lastPage = $value;

        //add the dots before last page button ...
        if($this->dotsVisible && $lastPage < $totalPages - 1){
            $html .= "<span class=\"dots\">". __("...") ."</span>";
        }

        //add the last page button
        if($this->lastButtonVisible && $lastPage != $totalPages){
            $buttonText = $this->firstAndLastButtonAsNumber ? $totalPages : __("last") ;
            $html .= "<a class=\"last\" href=\"". $link. $totalPages ."\" \>".$buttonText."</a>";
        }

        //next page button
        if($this->nextButtonVisible){
            if($onPage < $value){
                $html .= "<a class=\"next\" href=\"" . $link . ($onPage + 1) ."\">". __("next") ."</a>";
            }elseif($this->nextButtonAtEndVisible){
                $html .= "<span class=\"next\">". __("next") ."</span>";
            }
        }

        $html .= "</span>";
        return $html;    
    }
    
    //adds the key at the end of the query
    //if there is already a matching query variable 
    //the matching variable with it´s value will be removed
    function query_key_add($key, $url = NULL){
        if(!$url){ $url = $_SERVER["REQUEST_URI"]; }
        $url = $this->query_key_remove($url, $key);
        $val = strpos($url, '?') ? '&' : '?';
        return $url . $val . $key . '=';
    }


    //removes the key with its value from the url
    function query_key_remove($url, $key) {
        /*replaces: 
         * match ? or & character and group the match, this will be used for next query variable
         * match the key with = character
         * dot match & character but anything else as many times as possible *
         * match & character (this is the next query variable)? may accour once or 0 times
         */
        $url = preg_replace("/([\?|&])". $key ."=[^&]*&?/i", '$1', $url);
        //replace the last &, may accour becouse of the regex pattern above
        return preg_replace("/[&]+$/", '', $url);
    }    
}
?>