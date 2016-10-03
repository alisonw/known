<?php


namespace Tests\Data {

    /**
     * Test the acls
     */
    class AccessGroupTest extends \Tests\KnownTestCase {
        
        static $acl;
        static $testUserB;
        
        protected function newObject($owner, $access = 'PUBLIC') {
            $obj = new \Idno\Entities\GenericDataItem();
            $obj->setDatatype('UnitTestObjectAccessGroup');
            $obj->setTitle("Unit Test Search Object");
            $obj->setOwner($owner);
            $obj->setAccess($access);
            
            return $obj;
        }
        
        public static function setupBeforeClass()
        {
            if (get_called_class() === 'Tests\Data\AccessGroupTest') {
                // Create acl
                self::$acl = new \Idno\Entities\AccessGroup();


                // Create user B
                $user = new \Idno\Entities\User();
                $user->handle = 'testuserb';
                $user->email = 'hello@withknown.com';
                $user->setPassword(md5(rand())); // Set password to something random to mitigate security holes if cleanup fails
                $user->setTitle('Test User B');

                $user->save();

                self::$testUserB = $user;
            }
            
        }
        
        public function testPrivateObject() {
            
            $user = $this->user();
            $old = $this->swapUser($user);
            
            // Add to acl
            self::$acl->addMember($user->getUUID());
            self::$acl->save();
            
            $obj = $this->newObject($user, self::$acl->getUUID());
            $obj->save();
            
            // Swap user
            $a = $this->swapUser(self::$testUserB);
            
            // Check that B can't access object
            $tmp = \Idno\Entities\GenericDataItem::getByUUID($obj->getUUID());
            $this->assertTrue(empty($tmp));
            
            // Check that A can
            $b = $this->swapUser($a);
            $tmp = \Idno\Entities\GenericDataItem::getByUUID($obj->getUUID());
            var_export($tmp);
            $this->assertTrue(!empty($tmp));
            
            // Check Admin can always read
            $admin = $this->admin();
            $this->swapUser($admin);
            
            $tmp = \Idno\Entities\GenericDataItem::getByUUID($obj->getUUID());
            $this->assertFalse(empty($tmp));
            
            // Restore old user if there was one
            $this->swapUser($old);
        }
        
        public static function tearDownAfterClass()
        {
            self::$acl->delete(); 
        }
    }
    
}
