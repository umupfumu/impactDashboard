<!doctype html>
<html ng-app="myApp">
  <head>
    <link rel="stylesheet" href="angular/angular-material.min.css">
    <link rel="stylesheet" href="styles.css">
    <script src="angular/angular.min.js"></script>
    <script src="angular/angular-animate.min.js"></script>
    <script src="angular/angular-aria.min.js"></script>
    <script src="angular/angular-messages.min.js"></script>
    <script src="angular/angular-material.min.js"></script>
    <script src="spin.min.js"></script>
    <script src="impactDashboard.js"></script>
  </head>
  <body>

   <?php 
   include 'indicators.php';
   ?>

    <div ng-controller="ImpactDashboardCtl">
      <span class="datePicker">Begin date: <md-datepicker ng-model="beginDate" md-placeholder="Begin date:" md-max-date="maxDate"></md-datepicker></span>
      <span class="datePicker">End date: <md-datepicker ng-model="endDate" md-placeholder="End date:" md-max-date="maxDate"></md-datepicker></span> <br>
      <md-button class="md-raised md-primary" ng-click="generateDashboard()">GO!</md-button><div class="spinSpot" id="spinHere"></div><br>
   
   <?php
   foreach($indicatorGroups as $indicatorGroup => $indicators){
   $output.="<h3>";
   switch($indicatorGroup) {
   case "dmIndicators": 
     $output.="Diabetes Indicators";break;
   case "hivIndicators":
     $output.="HIV Indicators";break;
   case "copdIndicators":
     $output.="COPD Indicators";break;
   default:$output.="This Indicator Group Needs a Header";
   }
   $output.="</h3>";
   $numIndicators=count($indicators);
   for($x=0;$x<$numIndicators;$x++){
     $indicator = $indicators[$x];

     $outputCheckboxModel = 'indicators.'.$indicator->indicatorCategory.'[\''.$indicator->indicatorName.'\'].indicatorChecked';

     $outputCheckbox =  '<input type="checkbox" ng-model="'.$outputCheckboxModel.'"/>';
     $outputInnerHTML = '<span ng-class="{\'sectionDisabled\':!'.$outputCheckboxModel.'}">';
     $outputOuterHTML = '{{indicators.'.$indicator->indicatorCategory.'[\''.$indicator->indicatorName.'\'].indicatorResult}}</span><br>';

     $output .= $outputCheckbox.$outputInnerHTML.$indicator->indicatorHTML.$outputOuterHTML;
   }
 }
 echo $output."\n";
   ?>

<br>*Qualifying VDCs: Baijanath, Baradadevi, Bhagyashwari, Chandika, Gajara, Hattikot, Jalapadevi, Janalikot, Lungra, Mastamandaun, Nawathana, Payal, Ridikot, Siddheshwar, Sanfebagar Municipality, Sanfebagar
    </div>
  </body>
</html>
