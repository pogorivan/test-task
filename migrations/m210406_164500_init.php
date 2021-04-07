<?php

use yii\db\Migration;

/**
 * Class m210406_164500_init
 */
class m210406_164500_init extends Migration
{
    public function up()
    {
        $this->createTable('product', [
            'id' => $this->primaryKey(),
            'code' => $this->string(63)->notNull()->unique(),
            'name' => $this->string(255)->notNull()->defaultValue(''),
            'price' => $this->decimal(9,2)->notNull()->defaultValue(0)
        ]);
    }

    public function down()
    {
        $this->dropTable('product');
    }
}
