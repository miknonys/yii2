<?php

namespace yiiunit\framework\validators;


use yii\validators\UniqueValidator;
use Yii;
use yiiunit\data\ar\ActiveRecord;
use yiiunit\data\validators\models\FakedValidationModel;
use yiiunit\data\validators\models\ValidatorTestMainModel;
use yiiunit\data\validators\models\ValidatorTestRefModel;
use yiiunit\framework\db\DatabaseTestCase;
use yiiunit\TestCase;

class UniqueValidatorTest extends DatabaseTestCase
{
	protected $driverName = 'mysql';

	public function setUp()
	{
		parent::setUp();
		ActiveRecord::$db = $this->getConnection();
	}

	public function testAssureMessageSetOnInit()
	{
		$val = new UniqueValidator();
		$this->assertTrue(is_string($val->message));
	}

	public function testValidateAttributeDefault()
	{
		$val = new UniqueValidator();
		$m = ValidatorTestMainModel::find()->one();
		$val->validateAttribute($m, 'id');
		$this->assertFalse($m->hasErrors('id'));
		$m = ValidatorTestRefModel::find(1);
		$val->validateAttribute($m, 'ref');
		$this->assertTrue($m->hasErrors('ref'));
		// new record:
		$m = new ValidatorTestRefModel();
		$m->ref = 5;
		$val->validateAttribute($m, 'ref');
		$this->assertTrue($m->hasErrors('ref'));
		$m = new ValidatorTestRefModel();
		$m->id = 7;
		$m->ref = 12121;
		$val->validateAttribute($m, 'ref');
		$this->assertFalse($m->hasErrors('ref'));
		$m->save(false);
		$val->validateAttribute($m, 'ref');
		$this->assertFalse($m->hasErrors('ref'));
		// array error
		$m = FakedValidationModel::createWithAttributes(['attr_arr' => ['a', 'b']]);
		$val->validateAttribute($m, 'attr_arr');
		$this->assertTrue($m->hasErrors('attr_arr'));
	}

	public function testValidateAttributeOfNonARModel()
	{
		$val = new UniqueValidator(['className' => ValidatorTestRefModel::className(), 'attributeName' => 'ref']);
		$m = FakedValidationModel::createWithAttributes(['attr_1' => 5, 'attr_2' => 1313]);
		$val->validateAttribute($m, 'attr_1');
		$this->assertTrue($m->hasErrors('attr_1'));
		$val->validateAttribute($m, 'attr_2');
		$this->assertFalse($m->hasErrors('attr_2'));
	}

	public function testValidateNonDatabaseAttribute()
	{
		$val = new UniqueValidator(['className' => ValidatorTestRefModel::className(), 'attributeName' => 'ref']);
		$m = ValidatorTestMainModel::find(1);
		$val->validateAttribute($m, 'testMainVal');
		$this->assertFalse($m->hasErrors('testMainVal'));
		$m = ValidatorTestMainModel::find(1);
		$m->testMainVal = 4;
		$val->validateAttribute($m, 'testMainVal');
		$this->assertTrue($m->hasErrors('testMainVal'));
	}

	public function testValidateAttributeAttributeNotInTableException()
	{
		$this->setExpectedException('yii\base\InvalidConfigException');
		$val = new UniqueValidator();
		$m = new ValidatorTestMainModel();
		$val->validateAttribute($m, 'testMainVal');
	}
}