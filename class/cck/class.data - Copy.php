<?
// 20110818 -- pavlovt@wph.bg -- created

class data {
  /* define class properties starts */
  public $selectString = "
    cck_data.dataId,
    cck_data.contentTypeId,
    cck_data.createdBy,
    cck_data.modifiedBy,
    cck_data.createdOn,
    cck_data.modifiedOn,
    cck_data.isActive,
    COALESCE(createdByUsers.name, '') as createdByName,
    COALESCE(modifiedByUsers.name, '') as modifiedByName,
    cck_content_type.name as contentTypeName 
    ";
    
  public $select = " 
    FROM (cck_data) 
      INNER JOIN cck_content_type USING(contentTypeId) 
      LEFT OUTER JOIN user createdByUsers ON (createdByUsers.userId = cck_data.createdBy) 
      LEFT OUTER JOIN user modifiedByUsers ON (modifiedByUsers.userId = cck_data.modifiedBy) 
    WHERE cck_data.dataId > 0 ";
    
  public $selectCount = "
    SELECT count(cck_data.dataId)
    FROM (cck_data) 
      INNER JOIN cck_content_type USING(contentTypeId) 
      LEFT OUTER JOIN user createdByUsers ON (createdByUsers.userId = cck_data.createdBy) 
      LEFT OUTER JOIN user modifiedByUsers ON (modifiedByUsers.userId = cck_data.modifiedBy) 
    WHERE cck_data.dataId > 0 ";

  public $db;
  public $result;
  public $p;

  public $totalRecords;
  public $lastError;

  /* define class properties ends */

  function __construct() {
    $this->reset();
    $this->select = 'SELECT '.$this->selectString.$this->select;

  } // constructor

  private function reset() {
    $this->result = NULL;
    $this->p = array();

    $this->totalRecords = 0;

  } // reset

  public function next() {
    if (!empty($this->result) && ($this->row = $this->result->fetch())) {
      $this->parseRecord();
      return($this->row);
    }

    return false;
  } // next

  public function loadList($skip = null, $limit = null, $orderBy = null, $filter = '', $filterParams = '') {
    // reset
    $this->reset();
    $db = dbWrapper::getDb('db');

    // update select string and format the additional FROM and where extentions
    $additionalOrderBy = $orderBy ? " ORDER BY ".trim($orderBy)." " : " ORDER BY cck_data.dataId";

    if ((int)$limit) {
      $additionalLimit = " LIMIT ".(int)$skip.", ".(int)$limit." ";
    } else {
      $additionalLimit = "";
    }

    $q = $this->select.$filter." ".$additionalOrderBy.$additionalLimit;
    //echo $q; exit;
    
    if (!$this->result = $db->run($q, $filterParams, 'result')) {
      return false;
    }

    if (!$this->totalRecords = $db->run($this->selectCount.$filter, $filterParams, 'one')) {
      return false;
    }

    return true;

  } // loadList

  public function loadById($id) {
    $db = dbWrapper::getDb('db');
    $this->reset();

    $q = $this->select." AND cck_data.dataId = :id";

    if (!$this->result = $db->run($q, array('id' => (int)$id), 'result')) {
      return false;
    }

    return $this->next();

  } // loadById

  private function reload() {
    return $this->loadById($this->p["dataId"]);

  } // reload

  public function createNew($contentTypeId, $createdBy, $isActive = 1) {
    // reset last error
    $this->lastError = NULL;

    // format input data
    $contentTypeId = (int)$contentTypeId;
    $isActive = (int)$isActive;
    $createdBy = (int)$createdBy;

    $data = new stdClass;
    $data->isActive = $isActive;
    $data->contentTypeId = $contentTypeId;
    $data->createdBy = $createdBy;
    $data->createdOn = date("Y-m-d H:i:s", strtotime("now"));

    if (!$createdBy || !$contentTypeId) {
      $this->lastError = "Some of the required fields are empty - ".print_r($data, 1);
      return false;
    }

    //print_r($data); exit;

    if (!$this->db->insertObject( 'cck_data', $data, 'dataId' )) {
      $this->lastError = $this->db->stderr();
      return false;
    }

    $this->loadById($data->dataId);

    return TRUE;

  } // createNew

  public function isLoaded() {
    // make sure anything focused
    if (!(int)$this->p["dataId"]) {
      $this->lastError = "Data was not found";
      return false;
    }

    // ok to update
    return TRUE;

  } // canUpdate

  public function update($contentTypeId, $modifiedBy, $isActive = 1) {
    // reset last error
    $this->lastError = NULL;

    if (!$this->isLoaded()) {
      return false;
    }

    // format input data
    $contentTypeId = (int)$contentTypeId;
    $isActive = (int)$isActive;
    $createdBy = (int)$createdBy;

    $data = new stdClass;
    $data->dataId = $this->p["dataId"];
    $data->isActive = $isActive;
    $data->contentTypeId = $contentTypeId;
    $data->modifiedBy = $modifiedBy;

    if (!$modifiedBy || !$contentTypeId) {
      $this->lastError = "Some of the required fields are empty - ".print_r($data, 1);
      return false;
    }

    //print_r($data); exit;

    if (!$this->db->updateObject( 'cck_data', $data, 'dataId' )) {
      $this->lastError = $this->db->stderr(true);
      //print_r($this->lastError); exit;
      return false;
    }

    $this->loadById($data->dataId);

    return TRUE;

  } // update

  public function delete() {
    // reset last error
    $this->lastError = NULL;

    if (!$this->canUpdate()) {
      return false;
    }

    $q = "DELETE FROM cck_data WHERE cck_data.dataId = '".(int)$this->p["dataId"]."'";

    $this->db->setQuery($q);
    if (!$this->db->query()) {
      $this->lastError = "Database error. ".$this->db->ErrorMsg();
      return false;
    }

    return TRUE;

  } // delete

  private function parseRecord() {
    /*if (!empty($this->p)) {
      $this->p["createdOn"] = @json_decode($this->p["createdOn"], true);
    }*/

  } // parseRecord

  // load all active fields of the current content
  public function loadFieldList($contentTypeField, $dataField, $dataTextField) {
    $this->field = array();

    if (!$this->isLoaded()) {
      return false;
    }

    $contentTypeField->loadList(null, null, null, array("#__cck_content_type_field.contentTypeId" => $this->p["contentTypeId"], "#__cck_content_type_field.isActive" => 1));

    while ($contentTypeField->next()) {
      if ($contentTypeField->p["type"] != "textarea") {
        $this->field[$contentTypeField->p["name"]] = $dataField->loadByName($this->p["dataId"], $contentTypeField->p["name"]);
        if (empty($this->field[$contentTypeField->p["name"]])) {
          $this->field[$contentTypeField->p["name"]] = $contentTypeField->p;
        }

      } else {
        $this->field[$contentTypeField->p["name"]] = $dataTextField->loadByName($this->p["dataId"], $contentTypeField->p["name"]);
        if (empty($this->field[$contentTypeField->p["name"]])) {
          $this->field[$contentTypeField->p["name"]] = $contentTypeField->p;
        }
      }
    }

    return $this->field;

  } // loadFieldList

  // load all fields of the current content
  // $dataField - dataField class
  // $dataFieldList - all fields as name=>value pairs - Array([title] => "тестово име", [type] => 2, [desc] => "тестово описание")
  public function saveFieldList($dataFieldList, $contentTypeField, $dataField, $dataTextField) {
    $this->lastError = NULL;

    if (!$this->isLoaded()) {
      return false;
    }

    if (empty($dataFieldList)) {
      $this->lastError = "The list is empty - there is nothing to save";
      return false;
    }

    $contentTypeField->loadList(null, null, null, array("#__cck_content_type_field.contentTypeId" => $this->p["contentTypeId"], "#__cck_content_type_field.isActive" => 1));

    while ($contentTypeField->next()) {
      $name = $contentTypeField->p["name"];
      $value = (!empty($dataFieldList[$name]) ? $dataFieldList[$name] : "");

      if (empty($name)) {
        $this->lastError = "Field name {$name} cannot be empty";
        return false;
      }

      if ($contentTypeField->p["type"] != "textarea") {
        // separate the filename from the path
        if (($contentTypeField->p["type"] == "file") && !empty($value)) {
          // windows address c:\zz\zz.txt
          if (explode("\\", $value) > 1) {
            $tmp = explode("\\", $value);
            // get only the file name which is the last element of the array
            $value = $tmp[count($tmp)-1];
          // linux address
          } else {
            $value = basename($value);
          }

          if (!$value = $this->saveFileToServer($name)) {
            return false;
          }
        } elseif (($contentTypeField->p["type"] == "file") && empty($value)) {
          // no file was given and there is nothing to save
          continue;
        }

        // does not exist - use creste new
        if (!$dataField->loadByName($this->p["dataId"], $name)) {
          if (!$dataField->createNew($this->p["dataId"], $name, $value)) {
            $this->lastError = "Error saving field {$name} ".nl2br($this->db->getErrorMsg());
            return false;
          }

        } else {
          if (!$dataField->update($this->p["dataId"], $name, $value)) {
            $this->lastError = "Error saving field {$name} ".nl2br($this->db->getErrorMsg());
            return false;
          }
        }

      } else {
        // does not exist - use creste new
        if (!$dataTextField->loadByName($this->p["dataId"], $name)) {
          if (!$dataTextField->createNew($this->p["dataId"], $name, $value)) {
            $this->lastError = "Error saving field {$name} ".nl2br($this->db->getErrorMsg());
            return false;
          }

        } else {
          if (!$dataTextField->update($this->p["dataId"], $name, $value)) {
            $this->lastError = "Error saving field {$name} ".nl2br($this->db->getErrorMsg());
            return false;
          }
        }

      }
    }

    return true;

  } // saveFieldList


  function saveFileToServer($fieldName) {
    global $_FILES;
    //import joomlas filesystem functions, we will do all the filewriting with joomlas functions,
    //so if the ftp layer is on, joomla will write with that, not the apache user, which might
    //not have the correct permissions
    jimport('joomla.filesystem.file');
    jimport('joomla.filesystem.folder');

    //any errors the server registered on uploading
    $fileError = $_FILES[$fieldName]['error'];
    if ($fileError > 0)
    {
            switch ($fileError)
      {
            case 1:
            $this->lastError = JText::_( 'FILE TO LARGE THAN PHP INI ALLOWS' );
            return false;

            case 2:
            $this->lastError = JText::_( 'FILE TO LARGE THAN HTML FORM ALLOWS' );
            return false;

            case 3:
            $this->lastError = JText::_( 'ERROR PARTIAL UPLOAD' );
            return false;

            case 4:
            $this->lastError = JText::_( 'ERROR NO FILE' );
            return false;
            }
    }

    //check for filesize
    $fileSize = $_FILES[$fieldName]['size'];

    /*if($fileSize > 2000000)
    {
        echo JText::_( 'FILE BIGGER THAN 2MB' );
    }*/

    //check the file extension is ok
    $fileName = $_FILES[$fieldName]['name'];

    //echo "<pre>"; print_r($_FILES);exit;
    //the name of the file in PHP's temp directory that we are going to move to our folder
    $fileTemp = $_FILES[$fieldName]['tmp_name'];

    /*$uploadedFileNameParts = explode('.',$fileName);
    $uploadedFileExtension = array_pop($uploadedFileNameParts);

    $validFileExts = explode(',', 'jpeg,jpg,png,gif');

    //assume the extension is false until we know its ok
    $extOk = false;

    //go through every ok extension, if the ok extension matches the file extension (case insensitive)
    //then the file extension is ok
    foreach($validFileExts as $key => $value)
    {
      if( preg_match("/$value/i", $uploadedFileExtension ) )
      {
        $extOk = true;
      }
    }

    if ($extOk == false)
    {
      echo JText::_( 'INVALID EXTENSION' );
            return;
    }

    //for security purposes, we will also do a getimagesize on the temp file (before we have moved it
    //to the folder) to check the MIME type of the file, and whether it has a width and height
    $imageinfo = getimagesize($fileTemp);

    //we are going to define what file extensions/MIMEs are ok, and only let these ones in (whitelisting), rather than try to scan for bad
    //types, where we might miss one (whitelisting is always better than blacklisting)
    $okMIMETypes = 'image/jpeg,image/pjpeg,image/png,image/x-png,image/gif';
    $validFileTypes = explode(",", $okMIMETypes);

    //if the temp file does not have a width or a height, or it has a non ok MIME, return
    if( !is_int($imageinfo[0]) || !is_int($imageinfo[1]) ||  !in_array($imageinfo['mime'], $validFileTypes) )
    {
      echo JText::_( 'INVALID FILETYPE' );
            return;
    }*/

    //lose any special characters in the filename
    $fileName = ereg_replace("[^A-Za-z0-9._]", "-", $fileName);

    $uploadPath = JPATH_SITE.DS.'media'.DS.'cck';
    $fileNameList = JFolder::files($uploadPath);

    // if file 'q.txt' exists then the new file will be 'q_1.txt'
    if (in_array($fileName, $fileNameList)) {
      $tmpName = pathinfo($fileName, PATHINFO_FILENAME);
      $tmpExtention = pathinfo($fileName, PATHINFO_EXTENSION);
      $numList = array();
      foreach ($fileNameList as $v) {
        if (stristr($v, $tmpName)) {
          $v = pathinfo($v, PATHINFO_BASENAME);
          $v = explode("_", $v);
          if ((int)@$v[count($v)-1]) {
            $numList[] = $v[count($v)-1];
          }
        }
      }

      if (count($numList)) {
        rsort($numList);
        $tmpNum = $numList[0] + 1;
        $fileName = $tmpName."_".$tmpNum.".".$tmpExtention;
      } else {
        $fileName = $tmpName."_1.".$tmpExtention;
      }
    }

    //exit('file '.$fileName);

    //always use constants when making file paths, to avoid the possibilty of remote file inclusion
    $uploadPath = JPATH_SITE.DS.'media'.DS.'cck'.DS.$fileName;
    //exit('file '.$uploadPath);

    if(!JFile::upload($fileTemp, $uploadPath))
    //if(!move_uploaded_file ($fileTemp, $uploadPath))
    {
      $this->lastError = JText::_( 'ERROR MOVING FILE' );
        return false;
    }

    return $fileName;

  } // saveFileToServer

} // data
