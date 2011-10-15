<?
// 20110818 -- pavlovt@wph.bg -- created

class baseTable {
  /* define class properties starts */
  public $primaryKey = "";
  public $talbleName = ""; 
 
  public $selectFields = "";
  public $select = "";
  
  public $dataType = array();
  public $validationRules = array();

  public $db;
  public $result;
  public $p;

  public $totalRecords;
  public $error;

  /* define class properties ends */

  function __construct() {
    $this->reset();

  } // constructor

  private function reset() {
    $this->result = NULL;
    $this->p = array();

    $this->totalRecords = 0;

  } // reset

  public function next() {
    if (!empty($this->result) && ($this->p = $this->result->fetch())) {
      $this->parseRecord();
      return($this->p);
    }

    return false;
  } // next

  public function loadList($skip = null, $limit = null, $orderBy = null, $filter = '', $filterParams = '') {
    // reset
    $this->reset();
    $db = dbWrapper::getDb('db');

    // update select string and format the additional FROM and where extentions
    $additionalOrderBy = $orderBy ? " ORDER BY ".trim($orderBy)." " : " ORDER BY {$this->talbleName}.{$this->primaryKey}";

    if ((int)$limit) {
      $additionalLimit = " LIMIT ".(int)$skip.", ".(int)$limit." ";
    } else {
      $additionalLimit = "";
    }

    $q = 'SELECT '.$this->selectFields." ".$this->select." ".$filter." ".$additionalOrderBy." ".$additionalLimit;
    //echo $q; exit;
    
    if (!$this->result = $db->run($q, $filterParams)->getResult()) {
      return false;
    }

    if (!$this->totalRecords = $db->run("SELECT COUNT({$this->talbleName}.{$this->primaryKey}) ".$this->select.$filter, $filterParams, 'one')) {
      return false;
    }

    return true;

  } // loadList

  public function loadById($id) {
    $db = dbWrapper::getDb('db');
    $this->reset();

    $q = $this->select." AND {$this->talbleName}.{$this->primaryKey} = :id";

    if (!$this->result = $db->run($q, array('id' => (int)$id))->getResult()) {
      return false;
    }

    return $this->next();

  } // loadById

  private function reload() {
    return $this->loadById($this->p[$this->primaryKey]);

  } // reload
  
  // define data and type validation
  public function isValid($data) {
    $this->errors = array();
    
    // make shure this is not an object
    $data = (array)$data;
    
    // uses functions.validate.php
    $this->errors = validateFields($data, $this->validationRules);
    if(!empty($this->errors)) {
      return false;
      
    } elseif(!$this->isValidCustom($data)) {
      return true;
      
    } else {
      return true;
    }
    
  }
  
  // class custom validation
  private function isValidCustom($data) {
    return true;
    
  }

  public function createNew($data) {
    $db = dbWrapper::getDb($this->dbName);

    if (!$this->isValid($data)) {
      return false;
    }

    if (!$db->insert( $this->talbleName, $data )) {
      return false;
    }

    if (!$this->loadById($db->lastInsertId())) {
      return false;
    }

    return true;

  } // createNew

  public function isLoaded() {
    // make sure anything focused
    if (!(int)$this->p[$this->primaryKey]) {
      return false;
    }

    // ok to update
    return true;

  } // isLoaded

  public function update($data) {
    $db = dbWrapper::getDb($this->dbName);

    if (!$this->isLoaded()) {
      return false;
    }

    if (!$this->isValid($data)) {
      return false;
    }

    if (!$db->update( $this->talbleName, $data, "{$this->primaryKey} = ".(int)$this->p[$this->primaryKey] )) {
      return false;
    }

    if (!$this->reload()) {
      return false;
    }

    return true;

  } // update

  public function delete() {
    $db = dbWrapper::getDb($this->dbName);

    if (!$this->isLoaded()) {
      return false;
    }

    $q = "DELETE FROM {$this->talbleName} WHERE {$this->talbleName}.{$this->primaryKey} = ".(int)$this->p[$this->primaryKey];

    if (!$db->run($q)) {
      return false;
    }

    return true;

  } // delete

  private function parseRecord() {
    // p is object - make it array to be able to do the following operations
    $this->p = (array)$this->p;
    if (!empty($this->p) && stristr(implode(",", array_keys($this->p)),"json")) {
      foreach ($this->p as $k => $v) {
        if (stristr($k,'json')) {
          $this->p[$k] = @json_decode($this->p[$k]);
        }
      }
    }
    
    $this->p = (object)$this->p;

  } // parseRecord

} // baseTable