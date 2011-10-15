<?
// 20110821 -- pavlovt@wph.bg -- created

class dataField {
  /* define class properties starts */
  public $selectString = "
    cck_data_field.dataFieldId,
    cck_data_field.dataId,
    cck_data_field.name,
    cck_data_field.value,
    cck_content_type_field.label,
    cck_content_type_field.type,
    cck_content_type_field.option 
    ";
    
  public $select = " 
     FROM (cck_data_field) 
     INNER JOIN cck_data using(dataId) 
     INNER JOIN cck_content_type_field on (cck_data.contentTypeId = cck_content_type_field.contentTypeId AND 
        cck_data_field.name = cck_content_type_field.name) 
     WHERE cck_data_field.dataFieldId > 0 ";

  public $validationRules = array(
      "required,dataId,Полето contentTypeId е задължително",
      "required,name,Полето name е задължително",
      "required,value,Полето value е задължително"
      );    

}