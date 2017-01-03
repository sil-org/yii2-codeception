# yii2-codeception
Patches to implement nice features of yii2-codeception since it was abandoned

# What / Why
We've been developing with Yii2 for a couple years now and have several projects using the 
[yiisoft/yii2-codeception](https://github.com/yiisoft/yii2-codeception) library for unit tests.  
This library has been deprecated in favor of using a 
[Codeception module](https://github.com/Codeception/Codeception/blob/2.2/docs/modules/Yii2.md).

Unfortunately this module requires quite a bit of refactoring to run tests in a simlar way as `yii2-codeception` allowed.

After a few hours of tracing and debugging we figured out that two main things had changed that we depended on and so 
we decided to create a standalone repo to bring in the needed features.

## Loading fixtures into database
The first thing we found was that after reconfiguring Codeception to use the Yii2 module, to look like:

```yaml
# Codeception Test Suite Configuration

# suite for unit (internal) tests.
class_name: UnitTester
modules:
    enabled:
      - Asserts
      - UnitHelper
      - Yii2:
          configFile: 'tests/codeception/config/unit.php'
          cleanup: true
```

Running tests it was unable to load the fixtures due to foreign key constraints. We created a new base class for 
our active record classes that `beforeLoad` and `afterLoad` it will disable and enable foreign key constraints 
respectively. 

This new base class is `Sil\yii\test\ActiveFixture`. To use, simply update your fixture files to extend from it instead 
of the previous `yii\test\ActiveFixture` class. 

### Example fixtures class

```php
<?php
namespace tests\unit\fixtures\common\models;

use Sil\yii\test\ActiveFixture;

class UserFixture extends ActiveFixture
{
    public $modelClass = 'common\models\User';
    public $dataFile = 'tests/unit/fixtures/data/common/models/User.php';
}
```

## Accessing fixtures from test case
After getting fixtures to load properly into the database we found we could not access them in the way we were used to.
With `yii2-codeception`, within a test function we could call `$this->fixtureType('fixtureName')` to access a specific 
fixture record. After migrating to using `Codeception\TestCase\Test` as our unit test base class instead of 
`yii\codeception\DbTestCase` we got fatal errors about the method `fixtureType` does not exist on the object. When 
using the Yii2 Codeception module you access fixtures by calling 
`$this->tester->grabFixture('fixtureType', 'fixtureName')`. So to work around this we created a new base class for our 
tests that implements the `__call()` magic method to take the method name and argument and pass them to the new 
`$this->tester->grabFixture()` method. You can use this by updating your unit tests to extend 
`Sil\Codeception\TestCase\Test` instead of `Codeception\TestCase\Test`.

### Example test case file

```php
<?php
namespace tests\unit\common\models;

use Sil\Codeception\TestCase\Test;
use tests\unit\fixtures\common\models\UserFixture;

class UserTest extends Test
{
    public function fixtures()
    {
        return [
            'users' => UserFixture::className(),
        ];
    }
    
    public function testGetUser1()
    {
        $user1 = $this->users('user1');
        $this->assertEquals(1, $user1->id);
    }
}
```