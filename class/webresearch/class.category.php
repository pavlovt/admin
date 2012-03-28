<?php

class Category extends baseTable {

  public $primaryKey = "id";
  public $tableName = "cats"; 
 
  public $selectFields = " id, pid, name, CONCAT(id, ' - ', name, IF(pid=0, ' parrent', '')) AS id_name ";
  public $select = " FROM cats WHERE active > 0 ";
  
  public $validationRules = array(
      "required,pid,Полето pid е задължително",
      "required,name,Полето name е задължително",
      );


  # is parent category?
  function is_parent() {
    if (!$this->isLoaded())
      return false;

    return (!$this->p['pid'] ? true : false);
  }

  # return array of subcategories or empty array
  function get_subcategory() {
    if (!$this->isLoaded() || !$this->is_parent())
      return array();

    if (!$this->loadList(null, null, null, " AND pid = {$this->p['id']}"))
      return array();

    if (!$all = $this->getColumn('id'))
      return array();

    return $all;
  }

  # get only the subcategories of the categories included in the list
  function get_selected($list) {
    $categories = array();

    foreach ($list as $category_id) {
      # it doesn't exsist or is not active
      if (!$this->loadById($category_id))
        continue;

      if ($this->is_parent()) {
        $subcats = $this->get_subcategory();
        $categories = @array_merge($categories, (array)$subcats);
      } else {
        $categories[] = $category_id;
      }

    } # foreach

    return $categories;

  }

  # get array of categories with array of subcategories under each of them for the categories included in the list
  function get_selected_by_parent($list) {
    $categories = array();

    foreach ($list as $category_id) {
      # it doesn't exsist or is not active
      if (!$this->loadById($category_id))
        continue;

      if ($this->is_parent()) {
        $categories[$category_id] = $category_id;
        $subcats = $this->get_subcategory();
        if (!empty($subcats))
          $categories[$category_id] = $subcats;
      } else {
        $categories[$category_id] = $category_id;
      }

    } # foreach

    return $categories;

  }

}