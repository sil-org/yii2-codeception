<?php
namespace Sil\yii\test;

use yii\test\ActiveFixture as YiiTestActiveFixture;

/**
 * Class FixtureBase
 * Disable foreign key checks before load and enable again after load.
 * @package Sil\yii\test
 */
class ActiveFixture extends YiiTestActiveFixture
{
    public function beforeLoad() {
        parent::beforeLoad();
        $this->db->createCommand()->setSql('SET FOREIGN_KEY_CHECKS = 0')->execute();
    }

    public function afterLoad() {
        parent::afterLoad();
        $this->db->createCommand()->setSql('SET FOREIGN_KEY_CHECKS = 1')->execute();
    }
}