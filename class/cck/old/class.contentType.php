<?
// 20110701 -- pavlovt@wph.bg -- created

class contentType {
	/* define class properties starts */
	private $selectString = "
		#__cck_content_type.contentTypeId,
		#__cck_content_type.isActive,
		#__cck_content_type.name";

	private $allowedFilterFields = array("#__cck_content_type.contentTypeId" => 1, "#__cck_content_type.isActive" => 1);
	public $fieldType = array("text" => "Short text", "textarea" => "Long text", "date" => "Date", "gmaps" => "Google maps", "checkbox" => "Checkbox", "select" => "Select");

	public $db;
	public $result;
	public $index;

	public $salesPersonId;
	public $salesPersonUserName;

	public $properties;
	public $p;

	public $totalRecords;

	public $lastError;
	public $lastErrors; // array for when we may want to return multiple errors line on submitting a new form
	/* define class properties ends */

	function __construct($db) {
		$this->reset();
		$this->db = $db;

	} // constructor

	private function reset() {
		$this->index = -1;
		$this->result = NULL;
		$this->properties = array();
		$this->p = array();

		$this->totalRecords = 0;

	} // reset

	public function next() {
		$this->index++;
		if (isset($this->result[$this->index])) {
			$this->p = $this->result[$this->index];
			$this->parseRecord();
			return $this->p;

		} else {
			return NULL;

		}

		return NULL;

	} // next

	public function loadList($skip = NULL, $limit = NULL, $orderBy = NULL, $filter = NULL) {
		// reset
		$this->reset();

		$allowedFilterFields = $this->allowedFilterFields;
		$additionalWhere = " ";

		if (is_array($filter)) {
			foreach ($filter as $field => $value) {
				if (array_key_exists($field, $allowedFilterFields)) {
					if (is_array($value)) {
						$additionalWhere .= " AND ".$field." >= '".$value[0]."' AND  ".$field." <= '".$value[1]."' ";
					} else {
						if ($allowedFilterFields[$field]) {
							$additionalWhere .= " AND ".$field." = '".$value."' ";
						} else {
							$additionalWhere .= " AND ".$field." LIKE '%".$value."%' ";
						}
					}
				}
			}
		}

		// update select string and format the additional FROM and where extentions
		$additionalOrderBy = $orderBy ? " ORDER BY ".trim($orderBy)." " : " ORDER BY #__cck_content_type.contentTypeId";

		if ((int)$limit) {
			$additionalLimit = " LIMIT ".(int)$skip.", ".(int)$limit." ";
		} else {
			$additionalLimit = "";
		}

		$q = "SELECT ".$this->selectString." FROM (#__cck_content_type) WHERE #__cck_content_type.contentTypeId > 0 ".$additionalWhere." ".$additionalOrderBy.$additionalLimit;
		//echo $q; exit;
		$this->db->setQuery($q);
		if (!$this->db->query()) {
			$this->lastError = "Database error. ".$this->db->ErrorMsg();
			return FALSE;
		}

		$this->totalRecords = $this->db->getNumRows();
		$this->result = $this->db->loadAssocList();

		return TRUE;

	} // loadList

	public function loadById($id) {

		$this->reset();

		$q = "SELECT ".$this->selectString." FROM (#__cck_content_type) WHERE #__cck_content_type.contentTypeId = '".(int)$id."'";

		$this->db->setQuery($q);
		if (!$this->db->query()) {
			$this->lastError = "Database error. ".$this->db->ErrorMsg();
			return FALSE;
		}

		$this->result[0] = $this->db->loadAssoc();

		return $this->next();

	} // loadById

	private function reload() {
		return $this->loadById($this->p["contentTypeId"]);

	} // reload

	public function createNew($name, $isActive = 1) {
		// reset last error
		$this->lastError = NULL;

		// format input data
		$isActive = (int)$isActive;
		$name = trim($name);

		if (!strlen($name)) {
			$this->lastError = "Name is required";
			return false;
		}

		$data = new stdClass;
		$data->isActive = $isActive;
		$data->name = $name;
		//print_r($data); exit;

		if (!$this->db->insertObject( '#__cck_content_type', $data, 'contentTypeId' )) {
			$this->lastError = $this->db->stderr();
			return false;
		}

		$this->loadById($data->contentTypeId);

		return TRUE;

	} // createNew

	public function isLoaded() {
		// make sure anything focused
		if (!(int)$this->p["contentTypeId"]) {
			$this->lastError = "Отстъпката не е открита";
			return FALSE;
		}

		// ok to update
		return TRUE;

	} // canUpdate

	public function update($name, $isActive = 1) {
		// reset last error
		$this->lastError = NULL;
		$this->lastErrors = array();

		if (!$this->isLoaded()) {
			return FALSE;
		}

		// format input data
		$isActive = (int)$isActive;
		$name = trim($name);

		if (!strlen($name)) {
			$this->lastError = "Name is required";
			return false;
		}

		$data = new stdClass;
		$data->isActive = $isActive;
		$data->contentTypeId = $this->p["contentTypeId"];
		$data->name = $name;

		//print_r($data); exit;

		if (!$this->db->updateObject( '#__cck_content_type', $data, 'contentTypeId' )) {
			$this->lastError = $this->db->stderr();
			return false;
		}

		$this->loadById($data->contentTypeId);

		return TRUE;

	} // update

	public function delete() {
		// reset last error
		$this->lastError = NULL;
		$this->lastErrors = array();

		if (!$this->isLoaded()) {
			return FALSE;
		}

		$q = "DELETE FROM #__cck_content_type WHERE #__cck_content_type.contentTypeId = '".(int)$this->p["contentTypeId"]."'";

		$this->db->setQuery($q);
		if (!$this->db->query()) {
			$this->lastError = "Database error. ".$this->db->ErrorMsg();
			return FALSE;
		}

		return TRUE;

	} // delete

	public function deleteAllFields() {
		// reset last error
		$this->lastError = NULL;
		$this->lastErrors = array();

		if (!$this->isLoaded()) {
			return FALSE;
		}

		$q = "DELETE FROM #__cck_content_type_field WHERE #__cck_content_type_field.contentTypeId = '".(int)$this->p["contentTypeId"]."'";

		$this->db->setQuery($q);
		if (!$this->db->query()) {
			$this->lastError = "Database error. ".$this->db->ErrorMsg();
			return FALSE;
		}

		return TRUE;

	} // deleteAllFields()

	private function parseRecord() {
		/*if (!empty($this->p)) {
			$this->p["repair"] = @json_decode($this->p["repair"], TRUE);
			$this->p["customer"] = @json_decode($this->p["customer"], TRUE);
		}*/

	} // parseRecord

	public function loadFieldList($contentTypeField) {
		$this->field = array();

		if (!$this->isLoaded()) {
			return FALSE;
		}

		$contentTypeField->loadList(null, null, null, array("#__cck_content_type_field.contentTypeId" => $this->p["contentTypeId"]));

		while ($contentTypeField->next()) {
			$this->field[$contentTypeField->p["name"]] = $contentTypeField->p;
		}

		return $this->field;

	} // loadById

	// save all fields of the current content type
	// $contentTypeField - contentTypeField class
	// $fieldList - all fields as name=>value pairs - Array([seq] => 1, [label] => "име", [name] => "title", [type] => "text", [option] => "{\"isRequired\":1}", [isActive] => 1)
	public function saveFieldList($fieldList, $contentTypeField) {
		$this->lastError = NULL;

		if (!$this->isLoaded()) {
			return false;
		}
//echo '<pre>'; print_r($fieldList);exit;

		if (empty($fieldList)) {
			$this->lastError = "The list is empty - there is nothing to save";
			return false;
		}

		// delete all fields for this content type id so we can create again only those that ware not deleted
		if (!$this->deleteAllFields()) {
			return false;
		}

		foreach ($fieldList as $k => $v) {
			$v["option"] = str_replace("\\", "", $v["option"]);
			//echo '<pre>'; print_r($v["option"]);exit;
			if (!$contentTypeField->createNew($this->p["contentTypeId"], $ordering = $k, $label = $v["label"], $name = $v["name"], $type = $v["type"],  $option = $v["option"], $isActive = $v["isActive"])) {
				$this->lastError = "Error saving field {$name} ".nl2br($this->db->getErrorMsg());
				return false;
			}
		}

		return true;

	} // saveFieldList



} // contentType

?>