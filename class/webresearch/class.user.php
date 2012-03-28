<?php

class User extends baseTable {

  public $primaryKey = "id";
  public $tableName = "users"; 
 
  public $selectFields = " * ";
  public $select = " FROM users WHERE id > 0 ";
  //public $select = " FROM {$this->tableName} WHERE {$this->primaryKey} > 0 ";
  
  public $validationRules = array(
      "required,username,Полето username е задължително",
      "required,password,Полето password е задължително",
      );

  function login($username, $password) {
    $db = dbWrapper::getDb('db');

    $username = clear_xss($username);
    $password = clear_xss($password);

    $r = $db->query("SELECT id FROM users WHERE username = '{$username}' AND password = '{$password}' AND is_active = 1");

    if ($r->check()) {
      $id = $r->getOne();
      if ($user = $this->find($id)) {
        $_SESSION['userDetails']['id'] = $user['id'];
        $_SESSION['userDetails']['name'] = $user['name'];

        return true;
      }
    }

    $_SESSION['userDetails'] = array();

    return false;
  }

  function logout() {
    $_SESSION['userDetails'] = array();
  }

  function is_logged() {
    if (empty($_SESSION['userDetails']['id'])) {
      $this->lastError = 'Потребителят не е логнат';
      return false;
    }

    return true;
  }

  function has_access($page) {
    if (!$this->is_logged())
      return false;

    if (!$this->isLoaded()) 
      return false;

    $p = (object)$this->p;
    if (@$p->json['is_admin'] || @in_array($page, $p->json['pages']))
      return true;

    return false;

  }

}