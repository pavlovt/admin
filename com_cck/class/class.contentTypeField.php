<?
// 20110701 -- pavlovt@wph.bg -- created

class contentTypeField {
	/* define class properties starts */
	private $selectString = "
		#__cck_content_type_field.contentTypeFieldId,
		#__cck_content_type_field.contentTypeId,
		#__cck_content_type_field.ordering,
		#__cck_content_type_field.label,
		#__cck_content_type_field.name,
		#__cck_content_type_field.type,
		#__cck_content_type_field.option,
		#__cck_content_type_field.isActive";

	private $allowedFilterFields = array("#__cck_content_type_field.contentTypeFieldId" => 1, "#__cck_content_type_field.contentTypeId" => 1, "#__cck_content_type_field.isActive" => 1);

	private $allowedFormFields = array("text", "textarea", "select", "file", "mapLocation");

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
		$additionalOrderBy = $orderBy ? " ORDER BY ".trim($orderBy)." " : " ORDER BY #__cck_content_type_field.contentTypeFieldId, #__cck_content_type_field.ordering";

		if ((int)$limit) {
			$additionalLimit = " LIMIT ".(int)$skip.", ".(int)$limit." ";
		} else {
			$additionalLimit = "";
		}

		$q = "SELECT ".$this->selectString." FROM (#__cck_content_type_field) WHERE #__cck_content_type_field.contentTypeFieldId > 0 ".$additionalWhere." ".$additionalOrderBy.$additionalLimit;
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

		$q = "SELECT ".$this->selectString." FROM (#__cck_content_type_field) WHERE #__cck_content_type_field.contentTypeFieldId = '".(int)$id."'";

		$this->db->setQuery($q);
		if (!$this->db->query()) {
			$this->lastError = "Database error. ".$this->db->ErrorMsg();
			return FALSE;
		}

		$this->result[0] = $this->db->loadAssoc();

		return $this->next();

	} // loadById

	public function loadByName($contentTypeId, $name) {

		$this->reset();

		$q = "SELECT ".$this->selectString." FROM (#__cck_content_type_field) WHERE #__cck_content_type_field.name = '".$name."'";
		//exit($q);
		$this->db->setQuery($q);
		if (!$this->db->query()) {
			$this->lastError = "Database error. ".$this->db->ErrorMsg();
			return FALSE;
		}

		$this->result[0] = $this->db->loadAssoc();

		return $this->next();

	} // loadById


	private function reload() {
		return $this->loadById($this->p["contentTypeFieldId"]);

	} // reload

	public function createNew($contentTypeId, $ordering, $label, $name, $type, $option, $isActive = 1) {
		// reset last error
		$this->lastError = NULL;

		// format input data
		$contentTypeId = (int)$contentTypeId;
		$isActive = (int)$isActive;
		$name = trim($name);
		$label = trim($label);
		$ordering = (int)$ordering;
		$type = trim($type);
		$option = @json_encode(trim($option));

		$data = new stdClass;
		$data->isActive = $isActive;
		$data->contentTypeId = $contentTypeId;
		$data->ordering = $ordering;
		$data->label = $label;
		$data->name = $name;
		$data->type = $type;
		$data->option = $option;

		if (!strlen($name) || !strlen($label) || !strlen($type) || !$contentTypeId) {
			$this->lastError = "Some of the required fields are empty - ".print_r($data, 1);
			return false;
		}

		//print_r($data); exit;

		if (!$this->db->insertObject( '#__cck_content_type_field', $data, 'contentTypeFieldId' )) {
			$this->lastError = $this->db->stderr();
			return false;
		}

		$this->loadById($data->contentTypeFieldId);

		return TRUE;

	} // createNew

	public function canUpdate() {
		// make sure anything focused
		if (!(int)$this->p["contentTypeFieldId"]) {
			$this->lastError = "Отстъпката не е открита";
			return FALSE;
		}

		// ok to update
		return TRUE;

	} // canUpdate

	public function update($contentTypeId, $ordering, $label, $name, $type, $option, $isActive = 1) {
		// reset last error
		$this->lastError = NULL;

		if (!$this->canUpdate()) {
			return FALSE;
		}

	// format input data
		$contentTypeId = (int)$contentTypeId;
		$isActive = (int)$isActive;
		$name = trim($name);
		$label = trim($label);
		$ordering = (int)$ordering;
		$type = trim($type);
		$option = @json_encode(trim($option));

		$data = new stdClass;
		$data->isActive = $isActive;
		$data->contentTypeId = $contentTypeId;
		$data->ordering = $ordering;
		$data->label = $label;
		$data->name = $name;
		$data->type = $type;
		$data->option = $option;

		if (!strlen($name) || !strlen($label) || !strlen($type) || !$contentTypeId) {
			$this->lastError = "Some of the required fields are empty - ".print_r($data, 1);
			return false;
		}

		//print_r($data); exit;

		if (!$this->db->updateObject( '#__cck_content_type_field', $data, 'contentTypeFieldId' )) {
			$this->lastError = $this->db->stderr();
			return false;
		}

		$this->loadById($data->contentTypeFieldId);

		return TRUE;

	} // update

	public function delete() {
		// reset last error
		$this->lastError = NULL;
		$this->lastErrors = array();

		if (!$this->canUpdate()) {
			return FALSE;
		}

		$q = "DELETE FROM #__cck_content_type_field WHERE #__cck_content_type_field.contentTypeFieldId = '".(int)$this->p["contentTypeFieldId"]."'";

		$this->db->setQuery($q);
		if (!$this->db->query()) {
			$this->lastError = "Database error. ".$this->db->ErrorMsg();
			return FALSE;
		}

		return TRUE;

	} // delete

	private function parseRecord() {
		if (!empty($this->p)) {
			if (strlen($this->p["option"])) {
				// remove \ character
				$this->p["option"] = str_replace("\\", "", $this->p["option"]);
				// if there is " as first or last character then remove it
				if ($this->p["option"][0] == '"') $this->p["option"] = substr($this->p["option"], 1);
				if ($this->p["option"][strlen($this->p["option"])-1] == '"') $this->p["option"] = substr($this->p["option"], 0, -1);

				$this->p["option"] = @json_decode($this->p["option"], true);
				//echo '<pre>'; print_r($this->p["option"]);exit;
			}
		}

		// we will get the select options from db
		// for static select use - "select":{"1":"Висок","2":"Среден","3":"Нисък"}
		if (!empty($this->p["option"]["selectFromDb"])) {
				
			if (empty($this->p["option"]["dbSource"])) {
				$this->db->setQuery($this->p["option"]["selectFromDb"]);
				if ($this->db->query()) {
					$tmp = $this->db->loadRowList();
					foreach ($tmp as $v) {
						$this->p["option"]["select"][$v[0]] = $v[1];
					}
				}
			} else {
				//get the data source
				$d = $this->p["option"]["dbSource"];
				global $$d;
				$db = $$d;
				//var_dump($$db);exit; 
				
				if (!empty($db)) {
					$r = $db->query($this->p["option"]["selectFromDb"]);
					while($w = $r->fetch()) {
						$this->p["option"]["select"][$w[0]] = $w[1];
					}
				}
			}
			//print_r($this->p["option"]["select"]);exit;
		}

	} // parseRecord

} // contentTypeField

?>