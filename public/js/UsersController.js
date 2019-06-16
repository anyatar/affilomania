var usersApp = angular.module('usersApp',['ngDialog']);

usersApp.config(['ngDialogProvider', function (ngDialogProvider) {
    ngDialogProvider.setDefaults({
        className: 'ngdialog-theme-plain',
        plain: true,
        showClose: true,
        closeByDocument: true,
        closeByEscape: true
    });
}]);

usersApp.controller('UsersController', ['$scope', '$http', '$timeout', 'ngDialog', function($scope, $http, $timeout, ngDialog) {
	
	var loadTime = 30000; // 30 sec
	var loadPromise;
	
	$scope.userData = {};
	
	$scope.initUserData = function () {
		$scope.userData = {
				first_name 	: '',
				last_name	: '',
				email		: '',
				username	: '',
				password	: '',
				is_active	: true
		};
	};
	
	$scope.init = function() {
		// load users
		$http({
			method: 'GET',
			url: '/application/getUsers'
		}).then(function(res) {
			$scope.users = res.data.users;
			nextLoad();
		});

	};
	
	$scope.createOrUpdateUserForm = function(mode, rowData) {
		
		$scope.initUserData(); 
		
		var dialogBody = 	' 	<div class="form-group row"><label class="col-sm-4 col-form-label" for="first_name">First Name</label>' +
							'		<div class="col-sm-8"><input id="first_name" name="first_name" ng-model="userData.first_name" type="text" class="form-control" required ng-minlength="2" ng-maxlength="45" size="20">' +
							'		<span ng-if="userForm.first_name.$dirty && !userForm.first_name.$valid" class="error-text" role="alert">Must be between 2 and 45 chars</span></div>' + 
							'	</div>' +
							
							' 	<div class="form-group row"><label class="col-sm-4 col-form-label" for="last_name">Last Name</label>' +
							'		<div class="col-sm-8"><input id="last_name" name="last_name" ng-model="userData.last_name" class="form-control" required type="text" ng-minlength="2" ng-maxlength="45" size="20">' +
							'		<span ng-if="userForm.last_name.$dirty && !userForm.last_name.$valid" class="error-text" role="alert">Must be between 2 and 45 chars</span></div>' + 
							'   </div>' +
							
							' 	<div class="form-group row"><label class="col-sm-4 col-form-label" for="email">Email</label>' +
							'   	<div class="col-sm-8"><input id="email" name="email" type="email" ng-model="userData.email" class="form-control" required ng-maxlength="100" size="20">' +
							'		<span ng-if="userForm.email.$dirty && !userForm.email.$valid" class="error-text" role="alert">Invalid email address</span></div>' + 
							'   </div>' +
							
							' 	<div class="form-group row"><label class="col-sm-4 col-form-label" for="username">User Name</label>' +
							'		<div class="col-sm-8"><input id="username" name="username" ng-model="userData.username" type="text" class="form-control" required ng-minlength="2" ng-maxlength="45" size="20">' +
							'		<span ng-if="userForm.username.$dirty && !userForm.username.$valid" class="error-text" role="alert">Must be between 2 and 45 chars</span></div>' + 
							'   </div>' +
							
							' 	<div class="form-group row"><label class="col-sm-4 col-form-label" for="password">Password</label>' +
							'		<div class="col-sm-8"><input ng-model="userData.password" id="password" name="password" type="password" class="form-control" ng-required="' + ((mode == "create") ? "true" : "false") + '" ng-pattern="/^(?=.{8,})(?=.*[a-z])(?=.*[A-Z])(?=(.*[\\d]))(?=.*[@!#$%^&+=]).*$/">' + 
							'		<span ng-if="userForm.password.$dirty && !userForm.password.$valid" class="error-text" role="alert">Password must contain lowercase & uppercase letters, digits and special chars</span></div>' + 
							'	</div>' +
							
							' 	<div class="custom-control custom-checkbox">' + 
							'		<input type="checkbox" class="form-check-input" ng-model="userData.is_active" checked>' +
							'		<label for="active">Active</label>' +
							'	</div>';
		
		var template = '';
		if (mode == 'create') {
			template = 	'<div class="container">' + 
						'	<h4 class="text-primary text-center">Add New User</h4>' +
						'	<form name="userForm" ng-submit="addUser();closeThisDialog(0);">' +
						dialogBody +
						'	<div>' + 
						'				<input class="btn btn-primary" ng-disabled="!userForm.$valid" type="submit" value="Save">' + 
						'				<button type="reset" class="btn btn-secondary" ng-click="closeThisDialog(0);">Cancel</button>' +
						'		  </div>' +
						'	</div>' +
						'	</form>' +
						'</div>';
			
		} else if (mode == 'update') {
			
			$scope.userData = {
				id	 		: rowData.user.Id,
				first_name 	: rowData.user.first_name,
				last_name	: rowData.user.last_name,
				email		: rowData.user.email,
				username	: rowData.user.username,
				password	: '',
				is_active	: (rowData.user.is_active) ? true : false,
			};
			
			
			
			template = 	'<div class="container">' + 
			'	<h4 class="text-primary text-center">Update User</h4>' +
			'	<form name="userForm" ng-submit="updateUser();closeThisDialog(0);">' +
			' 	<div class="form-group row">' + 
			' 		<label class="col-sm-4 col-form-label" for="userid">Id</label>' +
			'		<div class="col-sm-8"><input id="userid" class="form-control" id="disabledInput" disabled value="' +  rowData.user.Id + '" type="text"></div>' + 
			'	</div>' +
			dialogBody +
			'	<div>' +
			'		<input class="btn btn-primary" type="submit" ng-disabled="!userForm.$valid" value="Update">' + 
			'		<button type="button" class="btn btn-primary" ng-click="deleteUser(' + rowData.user.Id + ' );closeThisDialog(0);">Delete</button>' + 
			'		<button type="reset" class="btn btn-secondary" ng-click="closeThisDialog(0);">Cancel</button>' +
			'	</div>' +
			'	</form></div>';
		} else {
			alert('Bug!');
			return;
		}
		
		ngDialog.open({
				scope: $scope,
				template: template,
				plain: true,
				closeByEscape: true
			});
	};

	$scope.addUser = function() {
		$http({
			method: 'POST',
			url: '/application/addUser',
			data: $scope.userData
		}).then(function(res) {
			if (res.data.success == true) {
				$scope.init();
				alert('User added successfully');
			} else {
				alert('Adding user failed: ' + res.data.error);
			}
		});

	};
	
	$scope.updateUser = function() {
		
		$http({
			method: 'POST',
			url: '/application/updateUser',
			data: $scope.userData
		}).then(function(res) {
			if (res.data.success == true) {
				$scope.init();
				alert('User updated successfully');
			} else {
				alert('Updating user failed: ' + res.data.error);
			}
		});
		
	};
	
	$scope.deleteUser = function(id) {
		$http({
			method: 'POST',
			url: '/application/deleteUser',
			data: {'id': id}
		}).then(function(res) {
			if (res.data.success == true) {
				$scope.init();
				alert('User deleted successfully');
			} else {
				alert('Error deleting user');
			}
		});
		
	};
	
	var cancelNextLoad = function() {
		$timeout.cancel(loadPromise);
	};
	  
	var nextLoad = function() {
	    //Always make sure the last timeout is cleared before starting a new one
	    cancelNextLoad();
	    loadPromise = $timeout($scope.init, loadTime);
    };
	  
	$scope.init();
	$scope.initUserData();
	
	
	$scope.$on('$destroy', function() {
	    cancelNextLoad();
	  });
	
}]);
