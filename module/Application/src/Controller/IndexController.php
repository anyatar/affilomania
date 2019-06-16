<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Application\Model\Mapper;

class IndexController extends AbstractActionController
{
    public function indexAction()
    {
        return new ViewModel();
    }
    
    public function getUsersAction()
    {
        $mapper = new Mapper();
        $users = $mapper->getUsers();
        return $this->getResponse()->setContent(json_encode(array('users' => $users)));
    }
    
    public function updateUserAction() {
        $request = $this->getRequest();
        
        if (!$request->isPost()) {
            return $this->getResponse()->setContent(json_encode(array('success' => false, 'error' => 'Should be POST')));
        }
        
        $params = $this->getParameters();
        try {
            $this->validateMandatoryParameters($params, array('id', 'first_name', 'last_name', 'email', 'username', 'is_active'));
        } catch (\Exception $e) {
            return $this->getResponse()->setContent(json_encode(array('success' => false, 'error' => $e->getMessage())));
        }
       
        $mapper = new Mapper();
        
        $error = $this->validateParams($params, !empty($params['password']));
        if ($error) {
            return $this->getResponse()->setContent(json_encode(array('success' => false, 'error' => $error)));
        }
        $success = $mapper->saveUser($params);
        
        return $this->getResponse()->setContent(json_encode(array('success' => $success, 'error' => ((!$success) ? 'failed': ''))));
    }
    
    public function deleteUserAction() {
        
        $request = $this->getRequest(); /* @var $request \Zend\Http\PhpEnvironment\Request */
        
        if (!$request->isPost()) {
            return $this->getResponse()->setContent(json_encode(array('success' => false, 'error' => 'Should be POST')));
        }
        
        $params = $this->getParameters();
        try {
            $this->validateMandatoryParameters($params, array('id'));
        } catch (\Exception $e) {
            return $this->getResponse()->setContent(json_encode(array('success' => false, 'error' => $e->getMessage())));
        }
        
        $mapper = new Mapper();
        $success = $mapper->deleteUser($params['id']);
       
        return $this->getResponse()->setContent(json_encode(array('success' => $success, 'error' => ((!$success) ? 'failed': ''))));
    }
    
    public function addUserAction() {
      
        $request = $this->getRequest();
        
        if (!$request->isPost()) {
            return $this->getResponse()->setContent(json_encode(array('success' => false, 'error' => 'Should be POST')));
        }
        
        $params = $this->getParameters();
        try {
            $this->validateMandatoryParameters($params, array('first_name', 'last_name', 'email', 'username', 'password', 'is_active'));
        } catch (\Exception $e) {
            return $this->getResponse()->setContent(json_encode(array('success' => false, 'error' => $e->getMessage())));
        }
       
        $success = false;
        $error = '';
        
        $mapper = new Mapper();
       
        if ($mapper->userExists($params['username'], $params['email'])) {
            $error = 'User already exists';
        } else {
            $error = $this->validateParams($params);
            if ($error) {
                return $this->getResponse()->setContent(json_encode(array('success' => false, 'error' => $error)));
            }
            $success = $mapper->saveUser($params);
        }
        
        return $this->getResponse()->setContent(json_encode(array('success' => $success, 'error' => $error)));
    }
    
    protected function validateParams($params, $checkPassword=true) {
        $error = '';
        if ($checkPassword && (filter_var($params['password'], FILTER_VALIDATE_REGEXP,  array("options" => array("regexp"=>"/^(?=.{8,})(?=.*[a-z])(?=.*[A-Z])(?=(.*[\d]))(?=.*[@!#$%^&+=]).*$/"))) === false)) {
            $error = 'Invalid password';
        } else if (filter_var($params['email'], FILTER_VALIDATE_EMAIL) === false) {
            $error = 'Invalid mail';
        } else if (!$this->validStrLen($params['first_name'], 2, 45) || !$this->validStrLen($params['last_name'], 2, 45) || !$this->validStrLen($params['username'], 2, 45)) {
            $error = 'First Name, Last Name and Username must be between 3 and 45 characters long';
        }
        return $error;
    }
    
    protected function validateMandatoryParameters(array $requestParams, array $mandatoryParams)
    {
        foreach ($mandatoryParams as $param) {
            if ((!isset($requestParams[$param])) || ('' === $requestParams[$param])) {
                throw new \Exception("This action requires the $param parameter");
            }
        }
    }
    
    protected function getParameters()
    {
        $content = $this->getRequest()->getContent();
        $postParams = json_decode($content, true);
        $postParams = array_map('trim', $postParams);
        if (isset($postParams['is_active']) && empty($postParams['is_active'])) {
            $postParams['is_active'] = 0;
        }
        return $postParams;
    }
    
    protected function validStrLen($str, $min, $max)
    {
        $len = strlen($str);
        if ($len < $min) {
            return false;
        }
        elseif ($len > $max){
            return false;
        }
        return true;
    }
}
