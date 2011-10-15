<?
// 20110701 -- pavlovt@wph.bg -- created

class dataTextField {
	/* define class properties starts */
	private $selectString = "
		#__cck_data_text_field.dataTextFieldId,
		#__cck_data_text_field.dataId,
		#__cck_data_text_field.name,
		#__cck_data_text_field.value,
		#__cck_content_type_field.label,
		#__cck_content_type_field.type,
		#__cck_content_type_field.option
		";

	private $allowedFilterFields = array("#__cck_data_text_field.dataTextFieldId" => 1, "#__cck_data_text_field.dataId" => 1);

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
		$additionalOrderBy = $orderBy ? " ORDER BY ".trim($orderBy)." " : " ORDER BY #__cck_data_text_field.dataTextFieldId";

		if ((int)$limit) {
			$additionalLimit = " LIMIT ".(int)$skip.", ".(int)$limit." ";
		} else {
			$additionalLimit = "";
		}

		$q = "SELECT ".$this->selectString." FROM (#__cck_data_text_field) INNER JOIN #__cck_data using(dataId) INNER JOIN #__cck_content_type_field on (#__cck_data.contentTypeId = #__cck_content_type_field.contentTypeId and #__cck_data_text_field.name = #__cck_content_type_field.name) WHERE #__cck_data_text_field.dataTextFieldId > 0 ".$additionalWhere." ".$additionalOrderBy.$additionalLimit;
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

		$q = "SELECT ".$this->selectString." FROM (#__cck_data_text_field) INNER JOIN #__cck_data using(dataId) INNER JOIN #__cck_content_type_field on (#__cck_data.contentTypeId = #__cck_content_type_field.contentTypeId and #__cck_data_text_field.name = #__cck_content_type_field.name) WHERE #__cck_data_text_field.dataTextFieldId = '".(int)$id."'";

		$this->db->setQuery($q);
		if (!$this->db->query()) {
			$this->lastError = "Database error. ".$this->db->ErrorMsg();
			return FALSE;
		}

		$this->result[0] = $this->db->loadAssoc();

		return $this->next();

	} // loadById

	public function loadByName($dataId, $name) {

		$this->reset();

		$q = "SELECT ".$this->selectString." FROM (#__cck_data_text_field) INNER JOIN #__cck_data using(dataId) INNER JOIN #__cck_content_type_field on (#__cck_data.contentTypeId = #__cck_content_type_field.contentTypeId and #__cck_data_text_field.name = #__cck_content_type_field.name) WHERE #__cck_data_text_field.dataId = '".(int)$dataId."' and #__cck_data_text_field.name = '".$name."'";

		$this->db->setQuery($q);
		if (!$this->db->query()) {
			$this->lastError = "Database error. ".$this->db->ErrorMsg();
			return FALSE;
		}

		$this->result[0] = $this->db->loadAssoc();

		return $this->next();

	} // loadById

	private function reload() {
		return $this->loadById($this->p["dataTextFieldId"]);

	} // reload

	public function createNew($dataId, $name, $value) {
		// reset last error
		$this->lastError = NULL;

		// format input data
		$dataId = (int)$dataId;
		$name = trim($name);
		$value = trim($value);

		$data = new stdClass;
		$data->dataId = $dataId;
		$data->name = $name;
		$data->value = $value;

		if (!$dataId || !strlen($name)) {
			$this->lastError = "Some of the required fields are empty - ".print_r($data, 1);
			return false;
		}

		//print_r($data); exit;

		if (!$this->db->insertObject( '#__cck_data_text_field', $data, 'dataTextFieldId' )) {
			$this->lastError = $this->db->stderr();
			return false;
		}

		$this->loadById($data->dataTextFieldId);

		return TRUE;

	} // createNew

	public function canUpdate() {
		// make sure anything focused
		if (!(int)$this->p["dataTextFieldId"]) {
			$this->lastError = "Data field not found";
			return FALSE;
		}

		// ok to update
		return TRUE;

	} // canUpdate

	public function update($dataId, $name, $value) {
		// reset last error
		$this->lastError = NULL;

		if (!$this->canUpdate()) {
			return FALSE;
		}

		// format input data
		$dataId = (int)$dataId;
		$name = trim($name);
		$value = trim($value);

		$data = new stdClass;
		$data->dataTextFieldId = $this->p["dataTextFieldId"];
		$data->dataId = $dataId;
		$data->name = $name;
		$data->value = $value;

		if (!$dataId || !strlen($name)) {
			$this->lastError = "Some of the required fields are empty - ".print_r($data, 1);
			return false;
		}

		//print_r($data); exit;

		if (!$this->db->updateObject( '#__cck_data_text_field', $data, 'dataTextFieldId' )) {
			$this->lastError = $this->db->stderr();
			return false;
		}

		$this->loadById($data->dataTextFieldId);

		return TRUE;

	} // update

	public function delete() {
		// reset last error
		$this->lastError = NULL;
		$this->lastErrors = array();

		if (!$this->canUpdate()) {
			return FALSE;
		}

		$q = "DELETE FROM #__cck_data_text_field WHERE #__cck_data_text_field.dataTextFieldId = '".(int)$this->p["dataTextFieldId"]."'";

		$this->db->setQuery($q);
		if (!$this->db->query()) {
			$this->lastError = "Database error. ".$this->db->ErrorMsg();
			return FALSE;
		}

		return TRUE;

	} // delete

	private function parseRecord() {
		if (!empty($this->p) && strlen($this->p["option"])) {
			$this->p["option"] = @json_decode($this->p["option"], true);
		}

	} // parseRecord

	public function getDbDescriptor() {
		return $this->db;

	} // getDbDescriptor


} // dataTextField

?>