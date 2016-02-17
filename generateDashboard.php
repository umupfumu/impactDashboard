<?php

  //CONSTANTS
define("DM_CONCEPT_ID","5526");
define("DUMMY_PATIENT_ID","113");
define("FASTING_BLOOD_SUGAR_CONCEPT_ID","4814");
define("CD4_CONCEPT_ID","4822");
define("COPD_EXACERBATION_CONCEPT_ID","3598");
define("COPD_ZERO_CONCEPT_ID","3209");
define("COPD_ONE_CONCEPT_ID","2922");
define("COPD_TWO_CONCEPT_ID","3170");
define("COPD_THREE_CONCEPT_ID","2720");

define("COPD_HOSPITALIZATION_CONCEPT_ID","6642");

// a fake POST result for debugging:
// $fakePostResult = '{"beginDate":"2015-12-01","endDate":"2016-02-16","indicatorName":"copdExacGreaterThan","userData":"1"}';
// $jsonObject = json_decode($fakePostResult);

$jsonObject = json_decode($_POST['jsonObject']);
$indicatorName = $jsonObject->indicatorName;
$beginDate = $jsonObject->beginDate;
$endDate = $jsonObject->endDate;
$userData = $jsonObject->userData;

//get db password from conf file
$myFile = fopen("pw.conf","r") or die("Unable to open file pw.conf.");
$dbPw = fread($myFile,filesize("pw.conf"));
fclose($myFile);


//person not voided or dummy patient
$sqlPersonNotVoidedOrDummy = "and p.voided = '0' ".
  "and p.person_id != '".DUMMY_PATIENT_ID."'";

//sql for join on visit between dates
$joinBetweenDatesSql = "join visit v on v.patient_id=p.person_id and v.voided='0' and date(v.date_started)";

$betweenDatesSql = "date('$beginDate') and date('$endDate')";
$betweenDatesLastXMonthsSql = "NOW()-INTERVAL $userData MONTH and NOW()";

$innerJoinBetweenDatesSql = "inner $joinBetweenDatesSql between $betweenDatesSql";
$leftJoinBetweenDatesSql = "left $joinBetweenDatesSql between $betweenDatesLastXMonthsSql";

//sql for finding a person address in the qualfying VDCs
$addressInVDCsSql ="and p.person_id in ". 
  "(select pa.person_id from person_address pa where ".
  "pa.county_district='Achham' ".
  "and pa.city_village in ".
  "('Baijanath','Baradadevi','Bhagyashwari','Chandika','Gajara',".
  "'Hattikot','Jalapadevi','Janalikot','Lungra','Mastamandaun',".
  "'Nawathana','Payal','Ridikot','Siddheshwar','Sanfebagar Municipality','Sanfebagar')".
  ")";

//total number of DM patients within the given dates
$selectNumDMPatientsSql = "select count(distinct p.person_id) as num from person p ".$innerJoinBetweenDatesSql." where p.person_id in ".
  "(select person_id from obs o where o.value_coded='".DM_CONCEPT_ID."' and o.voided='0') $sqlPersonNotVoidedOrDummy"; 

//DM patients from a qualifying VDC within the given dates
$dmPatientsQualifyingVDCSql = $selectNumDMPatientsSql." ".$addressInVDCsSql.";";
$selectNumDMPatientsSql.=";";

//DM patients from qualifying VDC who haven't had a visit within the last userData months
$dmPatientsQVDCNoVisitsWithinDates = "select count(distinct p.person_id) as num from person p ".$leftJoinBetweenDatesSql." where p.person_id in ".
  "(select person_id from obs o where o.value_coded='".DM_CONCEPT_ID."') $sqlPersonNotVoidedOrDummy $addressInVDCsSql".
  " and v.patient_id is null;"; 

//DM patients w/ poorly controlled fasting blood sugar
$dmPatientsBloodSugarSql = generateSqlMostRecentObs(FASTING_BLOOD_SUGAR_CONCEPT_ID,$innerJoinBetweenDatesSql).
  "and oo.value_numeric>$userData ".
  "and p.person_id in (select person_id from obs o2 where o2.value_coded='".DM_CONCEPT_ID."' and o2.voided='0') $sqlPersonNotVoidedOrDummy;";

//HIV patients w/ CD4 less than x
$hivCD4Sql = generateSqlMostRecentObs(CD4_CONCEPT_ID,$innerJoinBetweenDatesSql).
  "and oo.value_numeric<$userData $sqlPersonNotVoidedOrDummy;";

//Patients w/ COPD exacerbations documented
$copdExacDocSql = generateSqlMostRecentObs(COPD_EXACERBATION_CONCEPT_ID,$innerJoinBetweenDatesSql,true).
  "and oo.obs_datetime between $betweenDatesSql $sqlPersonNotVoidedOrDummy";

//Patients w/ $userData or greater COPD exacerbations
$copdExacerbationNumToIncludeArray = array();
$copdExacGreaterThanSql = "select 'There is something wrong with this indicator'";
if($userData<=0)
  array_push($copdExacerbationNumToIncludeArray,COPD_ZERO_CONCEPT_ID);
if($userData<=1)
  array_push($copdExacerbationNumToIncludeArray,COPD_ONE_CONCEPT_ID);
if($userData<=2)
  array_push($copdExacerbationNumToIncludeArray,COPD_TWO_CONCEPT_ID);
if($userData<=3)
  array_push($copdExacerbationNumToIncludeArray,COPD_THREE_CONCEPT_ID);

$numConceptsInArray = count($copdExacerbationNumToIncludeArray);

if($numConceptsInArray>0){
  $copdExacGreaterThanSql = $copdExacDocSql . " and (";
  for($x=0;$x < $numConceptsInArray ;$x++){
    $copdExacGreaterThanSql.="oo.value_coded='".$copdExacerbationNumToIncludeArray[$x]."' ";
    if($x!=$numConceptsInArray-1)
      $copdExacGreaterThanSql.="or ";
  }
  $copdExacGreaterThanSql.=");";
}

$copdExacDocSql.=";";

//Connect to mysql database
$con = mysqli_connect('localhost','dashboard',$dbPw,'openmrs');
if(!$con) {
  die('Could not connect: ' .mysqli_error($con));
}


//make sql query
$madeQuery=true;
switch($indicatorName) {
case "dmPatientsTotal" : 
  $mysqlResult = mysqli_query($con,$selectNumDMPatientsSql);
  break;
case "dmPatientsQualifyingVDC" : 
  $mysqlResult = mysqli_query($con,$dmPatientsQualifyingVDCSql);
  break;
case "dmPatientsQualifyingVDCrecentVisit" :
  $mysqlResult = mysqli_query($con,$dmPatientsQVDCNoVisitsWithinDates);
  break;
case "dmPatientsBloodSugar" :
  $mysqlResult = mysqli_query($con,$dmPatientsBloodSugarSql);
  break;
case "hivPatientsCD4" :
  $mysqlResult = mysqli_query($con,$hivCD4Sql);
  break;
case "copdExacDocumented" :
  $mysqlResult = mysqli_query($con,$copdExacDocSql);
  break;
case "copdExacGreaterThan" :
  $mysqlResult = mysqli_query($con,$copdExacGreaterThanSql);
  break;
default: 
  $madeQuery=false;
}

if($madeQuery){
  $row = mysqli_fetch_array($mysqlResult);
  $result = $row['num'];  //if you want to see what SQL a query represents, change this field to $result = $hivCD4Sql or another query, and it will output
}else{                    //the SQL string in the UI
  $result='Sorry, there appears to an error with this indicator.';
}


echo $result;

//helper function to generate the first part of a sql string
//for a query that wants to find the most recent obs
function generateSqlMostRecentObs($conceptId,$innerJoinSql,$valueCoded=false){
  return "select count(distinct p.person_id) as num from person p ".$innerJoinSql.
    "inner join obs oo on p.person_id=oo.person_id ".
    "inner join".
    "(".
    "select max(obs_datetime) maxDate, obs_id, person_id, ".
    ($valueCoded?"value_coded":"value_numeric").
    " from obs where concept_id = '".$conceptId."' and ".
    ($valueCoded?"value_coded":"value_numeric").
    " is not null and voided = '0' group by person_id".
    ") o on o.obs_id=oo.obs_id and o.maxDate=oo.obs_datetime ".
    "inner join patient_identifier pi on p.person_id=pi.patient_id ";
}


?>