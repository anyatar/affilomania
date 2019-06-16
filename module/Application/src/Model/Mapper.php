<?php
namespace Application\Model;

use Zend\Db\Sql\Sql;
use Zend\Db\Adapter\Adapter;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Driver\ResultInterface;

class Mapper
{
    const USERS_TABLE = 'user';

    public function getUsers()
    {
        $adapter = $this->getAdapter();
        $usersTable = new TableGateway(self::USERS_TABLE, $adapter);
        
        $select = new \Zend\Db\Sql\Select();
        $select->from($usersTable->getTable());
        $select->order(array('last_update_time' => 'DESC'));
        
        return $usersTable->selectWith($select)->toArray();
        
    }
    
    public function saveUser($user)
    {
        $adapter = $this->getAdapter();
        $usersTable = new TableGateway(self::USERS_TABLE, $adapter);
        
        $arrayToSave = array (  'first_name'    => $user['first_name'],
                                'last_name'     => $user['last_name'],
                                'email'         => $user['email'],
                                'username'      => $user['username'],
                                'password'      => md5($user['password']),
                                'is_active'     => $user['is_active']);
        try {
            if (isset($user['id'])) {
               
                if (!empty($user['password'])) {
                    $arrayToSave['password'] = md5($user['password']);
                }
                
                $arrayToSave['last_update_time'] = new \Zend\Db\Sql\Expression("NOW()");
                $result = $usersTable->update(  $arrayToSave,
                                                array('Id' => $user['id']));
            } else {
                $arrayToSave['password'] = md5($user['password']);
                $result = $usersTable->insert($arrayToSave);
            }
        } catch (\Exception $ex) {
            return false;
        }
        
        if ($result) {
            return true;
        }
        
        return false;
    }
    
    public function deleteUser($id)
    {
        $sql = new Sql($this->getAdapter());
        $delete = $sql->delete(self::USERS_TABLE);
        $delete->where(array('Id' => $id));
        $stmt   = $sql->prepareStatementForSqlObject($delete);
        $result = $stmt->execute();
        
        if ($result instanceof ResultInterface && ($result->getAffectedRows() > 0)) {
            return true;
        }
        
        return false;
    }
    
    public function userExists($username, $email)
    {
        $sql = new Sql($this->getAdapter());
        $select = $sql->select(self::USERS_TABLE);
      
        $where = new \Zend\Db\Sql\Where();
        
        $where->equalTo('username', $username)
              ->or
              ->equalTo('email', $email);
        $select->where($where);
        
        $stmt   = $sql->prepareStatementForSqlObject($select);
        $result = $stmt->execute();
        
        if ($result instanceof ResultInterface && $result->isQueryResult() && $result->current()) {
            return true;
        }
        
        return false;
    }
    
    private function getAdapter()
    {
        $config = new \Zend\Config\Config( include 'config/autoload/global.php' );
        
        if (isset($config['pdo_conf'])) {
            $pdoConfig = $config->pdo_conf->toArray();
        } else {
            $pdoConfig = array(
                'driver' => 'Mysqli',
                'database' => 'affilomania',
                'username' => 'root',
                'password' => '1234'
            );
        }
        
        return new Adapter($pdoConfig);
    }
}
