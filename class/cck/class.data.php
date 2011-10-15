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

  public $validationRules = array(
      "required,contentTypeId,Полето contentTypeId задължително"
      );    


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
