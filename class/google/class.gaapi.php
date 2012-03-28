<?
// include the Google Analytics PHP class
require_once "gapi.class.php";

/**
* Customized Google Analytics class
*/
class gaApi extends gapi {
   
   function __construct() {
      $this->lastError = '';

      try {
         // create an instance of the GoogleAnalytics class using your own Google {email} and {password}
         parent::__construct(ga_email, ga_password);
       
      } catch (Exception $e) { 
         $this->lastError =  'Error: ' . $e->getMessage(); 
      }
   }

   // get all profiles
   function getProfiles() {
      if ( !empty($this->lastError) )
         return FALSE;

      try {
         parent::requestAccountData();

         $profiles = array();
         foreach(parent::getResults() as $result) {
           $profiles[] = $result->getProperties();
           //array("id" => $result->getProfileId(), "name" => $result->getProfileName());
         }

         return $profiles;

      } catch (Exception $e) { 
         $this->lastError =  'Error: ' . $e->getMessage(); 
         return FALSE;
      }
   }


   // get all results
   function getReports($report_id, $dimensions, $metrics, $sort_metric=null, $filter=null, $start_date=null, $end_date=null, $start_index=1, $max_results=10) {
      if ( !empty($this->lastError) )
         return FALSE;

      try {
         $this->requestReportData($report_id, $dimensions, $metrics, $sort_metric, $filter, $start_date, $end_date, $start_index, $max_results);

         $results = array();
         foreach($this->getResults() as $result) {
           $results[] =  array("metrics" => $result->getMetrics(), "dimentions" => $result->getDimensions());
           #$results['metrics'][] =  $result->getMetrics();
           #$results['dimensions'][] = $result->getDimensions();
           
         }

         return $results;

      } catch (Exception $e) { 
         $this->lastError =  'Error: ' . $e->getMessage(); 
         return FALSE;
      }
   }

   function getReport($report_id, $dimensions, $metrics, $sort_metric=null, $filter=null, $start_date=null, $end_date=null, $start_index=1, $max_results=1) {
      if ( !empty($this->lastError) )
         return FALSE;

      try {
         $this->requestReportData($report_id, $dimensions, $metrics, $sort_metric, $filter, $start_date, $end_date, $start_index, $max_results);

         $results = array();
         $result = $this->getResults();
         foreach($this->getResults() as $result) {
           $results =  array("metrics" => $result->getMetrics(), "dimentions" => $result->getDimensions());
           
         }

         return $results;

      } catch (Exception $e) { 
         $this->lastError =  'Error: ' . $e->getMessage(); 
         return FALSE;
      }
   }
}