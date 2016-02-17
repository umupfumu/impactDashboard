var myAppModule = angular.module('myApp',['ngMaterial']);

myAppModule.controller("ImpactDashboardCtl", ['$scope',function ImpactDashboardCtl($scope) {
	    "use strict";
	    //various variables
	    var nCheckedIndicators = 0; //number of indicators that are selected by the user
	    var nCompleteIndicators = 0; //number of indicators for which a result has returned

	    //Indicator class, one per indicator
	    var Indicator = class {
		constructor(indicatorName,indicatorChecked,extraUserInput) {
		    this.indicatorName=indicatorName;
		    this.indicatorChecked=indicatorChecked;
		    this.extraUserInput=extraUserInput||0;
		}
	    };


	    //initialize the indicators
	    $scope.indicators = {
		dmIndicators : {
		    'dmPatientsTotal' : new Indicator('dmPatientsTotal',true),
		    'dmPatientsQualifyingVDC' : new Indicator('dmPatientsQualifyingVDC',true),
		    'dmPatientsQualifyingVDCrecentVisit' : new Indicator('dmPatientsQualifyingVDCrecentVisit',true,3),
		    'dmPatientsBloodSugar' : new Indicator('dmPatientsBloodSugar',true,300)
		},
		hivIndicators : {
		    'hivPatientsCD4' : new Indicator('hivPatientsCD4',true,250)
		},
		copdIndicators : {
		    'copdExacDocumented' : new Indicator('copdExacDocumented',true),
		    'copdExacGreaterThan' : new Indicator('copdExacGreaterThan',true,1)
		}
	    };
	    
	    //initialize the date selectors
	    var todaysDate = new Date();
	    $scope.beginDate = new Date(todaysDate.getFullYear(),todaysDate.getMonth()-1,todaysDate.getDate());
	    $scope.endDate = new Date();
	    $scope.maxDate= new Date();

	    //this function generates an http request function which processes the
	    //response from generateDashboard.php for a single indicator
	    function httpRequestFunction(xmlHttpVar,whichIndicator,whichIndicatorGroup,spinner) {
		return function() {
		    if (xmlHttpVar.readyState == 4 && xmlHttpVar.status == 200) {
			var xmlResponse = xmlHttpVar.responseText;
			$scope.indicators[whichIndicatorGroup][whichIndicator].indicatorResult = xmlResponse;
			
			$scope.$apply();
			nCompleteIndicators++;
			if(nCompleteIndicators==nCheckedIndicators)
			    spinner.stop();
		    }
		    
		}
	    }
	    
	    //this function runs when the user pushes the "GO" button
	    //it generates all the selected indicators
	    $scope.generateDashboard = function() {
		var spinTarget = document.getElementById('spinHere');
		var spinner = new Spinner().spin(spinTarget);
		
		//iterates over each indicator group
		for(var indicatorGroup in $scope.indicators){
		    
		    if($scope.indicators.hasOwnProperty(indicatorGroup)) {
			//iterates over each indicator in the group
			for (var indicator in $scope.indicators[indicatorGroup]) {
			    
			    if($scope.indicators[indicatorGroup].hasOwnProperty(indicator) && $scope.indicators[indicatorGroup][indicator].indicatorChecked){
				nCheckedIndicators++;
				var jsonObject = { "beginDate" : $scope.beginDate,
						   "endDate" : $scope.endDate,
						   "indicatorName" : indicator,
						   "userData" : $scope.indicators[indicatorGroup][indicator].extraUserInput
				};
				
				var xmlhttp = new XMLHttpRequest();
				xmlhttp.onreadystatechange = httpRequestFunction(xmlhttp,indicator,indicatorGroup,spinner);
				xmlhttp.open("POST","generateDashboard.php");
				xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
				xmlhttp.send("jsonObject="+JSON.stringify(jsonObject));

			    }
			}

		    }
		}
		if(nCheckedIndicators==0) spinner.stop(); //stop spinning if nothing is checked

	    };
	}]);