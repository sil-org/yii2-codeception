<?php
namespace Sil\Codeception\TestCase;

use Codeception\Test as CodeceptionTest;

/**
 * Class Test
 * Override __call() to wrap new $this->tester->grabFixture('fixtureType', 'fixtureName') format
 * @package Sil\Codeception\TestCase
 */
class Test extends CodeceptionTest
{
    /**
     * Add support for old format of getting fixtures using $this->fixtureType('fixtureName') format.
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        if (is_array($arguments) && count($arguments) === 1) {
            /*
             * Looks like a call to fixtures using old format, attempt to grab fixture
             */
            $fixture = $this->tester->grabFixture($name, $arguments[0]);
            if ($fixture instanceof \yii\db\BaseActiveRecord) {
                return $fixture;
            }
        }

        parent::__call($name, $arguments);
    }
}