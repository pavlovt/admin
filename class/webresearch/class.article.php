<?php

class Article extends baseTable {

  public $primaryKey = "id";
  public $tableName = "data"; 
 
  public $selectFields = " data.id, data.title, data.subtitle, data.resume, data.body, data.link, data.author, data.date, DATE_FORMAT(data.date, '%d.%m.%Y') date_formated, sources.name source_name ";
  public $select = " FROM (data) INNER JOIN sources ON (sources.id = data.source) WHERE data.id > 0 ";
    
  public $validationRules = array();

  public $connections = array(
    "has_many" => "Category"
  );

  # returns a list of articles or empty list
  # cats - array of category ids or single category id
  function find_by_category($cats, $filter = '', $skip = null) {

    # make sure the user is subscribed to the categories he submited
    $cats = get_user_category($cats);

    # if cats was empty or incorrect use the standart categories
    if (empty($cats))
      $cats = get_user_category();


    $db = dbWrapper::getDb('db');
    // get all data.ids for this category
    $cats = implode(',', $cats);
    $query = 
      "SELECT GROUP_CONCAT(DISTINCT data_cats.did) ids
      FROM (data_cats) INNER JOIN cats ON (cats.id = data_cats.cid)
      WHERE cats.id IN ({$cats})
      ";

    if (($ids = $db->query($query)->getOne()) && $this->loadList($skip, articles_per_page, " data.id DESC ", " AND data.id IN ({$ids}) ".$filter)) {
      return $this->getAll();
    }
      
    return $this->emptyResult();
    
  }

  # returns total number of results
  # cats - array of category ids or single category id
  function find_by_category_count($cats, $filter) {

    # make sure the user is subscribed to the categories he submited
    $cats = get_user_category($cats);

    # if cats was empty or incorrect use the standart categories
    if (empty($cats))
      $cats = get_user_category();

    $db = dbWrapper::getDb('db');
    // get all data.ids for this category
    $cats = implode(',', $cats);
    $query = 
      "SELECT GROUP_CONCAT(DISTINCT data_cats.did) ids
      FROM (data_cats) INNER JOIN cats ON (cats.id = data_cats.cid)
      WHERE cats.id IN ({$cats})
      ";
      //echo $filter."<br>"; //exit;
      //echo $query."<br>"; //exit;

    if ($ids = $db->query($query)->getOne()) {
      //echo "SELECT COUNT({$this->tableName}.{$this->primaryKey}) ".$this->select." AND data.id IN ({$ids}) {$filter} <br>";
      $count = $db->run("SELECT COUNT({$this->tableName}.{$this->primaryKey}) ".$this->select." AND data.id IN ({$ids}) ".$filter)->getOne();
      if ($count)
        return $count;
    }
      
    return 0;
    
  }

}