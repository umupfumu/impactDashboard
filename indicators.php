<?php
class Indicator {
  var $indicatorName;
  var $indicatorHTML;
  var $indicatorCategory;
  
  function Indicator($indicatorName,$indicatorHTML,$indicatorCategory) {
    $this->indicatorName=$indicatorName;
    $this->indicatorHTML=$indicatorHTML;
    $this->indicatorCategory=$indicatorCategory;
  }
  }

//DM INDICATORS
$indicatorGroups['dmIndicators'] = array(
				    new Indicator('dmPatientsTotal','Total number of patients with diabetes mellitus who have had a visit within the given date range: ','dmIndicators'),
				    new Indicator('dmPatientsQualifyingVDC','Total number of patients with diabetes mellitus from a qualifying* VDC who have had a visit within the given date range: ','dmIndicators'),
				    new Indicator('dmPatientsQualifyingVDCrecentVisit','Total number of patients with diabetes mellitus from a qualifying* VDC who have NOT had a visit in the last <input type="number" ng-model="indicators.dmIndicators[\'dmPatientsQualifyingVDCrecentVisit\'].extraUserInput" class="smallTextBox"></input> months: ','dmIndicators'),
				    new Indicator('dmPatientsBloodSugar','Total number of patients with diabetes mellitus whoose last fasting blood sugar was > <input type="number" min="0" max="900" ng-model="indicators.dmIndicators[\'dmPatientsBloodSugar\'].extraUserInput" class="mediumTextBox"></input>: ','dmIndicators')
					 );



//HIV INDICATORS
$indicatorGroups['hivIndicators'] = array(
					  new Indicator('hivPatientsCD4','Total number of HIV patients whose last CD4 was less than <input type="number" ng-model="indicators.hivIndicators[\'hivPatientsCD4\'].extraUserInput" class="mediumTextBox"></input>: ','hivIndicators')
					  );


//COPD INDICATORS
$indicatorGroups['copdIndicators'] = array(
					   new Indicator('copdExacDocumented','Total number of COPD patients for whom a recent exacerbation number is documented: ', 'copdIndicators'),
					   new Indicator('copdExacGreaterThan','Total number of COPD patients who have had greater than or equal to <input type="number" ng-model="indicators.copdIndicators[\'copdExacGreaterThan\'].extraUserInput" class="smallTextBox"></input> exacerbations in the past three months: ','copdIndicators') 
			   );
?>