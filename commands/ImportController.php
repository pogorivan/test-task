<?php

namespace app\commands;

use app\models\Product;
use yii\console\Controller;
use yii\console\ExitCode;

/**
 * This command perfoms importing products from xml or csv file
 */
class ImportController extends Controller
{
    const BATCH_SIZE = 200000;

    /**
     * This command perfoms importing products from xml or csv file.
     *
     * @return int Exit code
     */
    public function actionIndex()
    {
        echo "Эта команда выполняет импорт данных о товарах из файлов xml и csv.\n\n";

        $link = $this->prompt('Введите ссылку на файл:', ['required' => true]);

        $fileHandle = @fopen($link, 'r');
        if (!$fileHandle) {
            echo "Файл по ссылке недоступен!";
            return ExitCode::DATAERR;
        }

        \Yii::$app->db->enableProfiling = false;
        \Yii::$app->db->enableLogging = false;
        $dbSchema = \Yii::$app->db->getSchema();

        $firstLine = fgets($fileHandle, 100);
        if (preg_match('/^<\?xml/i', $firstLine)) {
            fclose($fileHandle);

            echo "Файл по ссылке в xml формате, уточните данные по формату.\n";
            $itemElement = $this->prompt('КОРНЕВОЙ элемент товара:', ['default' => 'item']);
            $itemCodeElement = $this->prompt('Элемент, содержащий АРТИКУЛ товара:', ['default' => 'code']);
            $itemNameElement = $this->prompt('Элемент, содержащий НАИМЕНОВАНИЕ товара:', ['default' => 'name']);
            $itemPriceElement = $this->prompt('Элемент, содержащий ЦЕНУ товара:', ['default' => 'price']);

            $xmlReader = new \XMLReader();
            $xmlReader->open($link);
            //Ищем начало массива товаров
            while ($xmlReader->read() && $xmlReader->name !== $itemElement);

            $insertSql = self::insertSqlDefault();
            $counter = 0;
            while ($xmlReader->name == $itemElement)
            {
                $item = new \SimpleXMLElement($xmlReader->readOuterXML());
                if ($item->$itemNameElement && $item->$itemCodeElement && $item->$itemPriceElement) {
                    if ($counter > 0) {
                        $insertSql .= ', ';
                    }
                    $insertSql .= "('".$dbSchema->quoteValue($item->$itemCodeElement)."', '".$dbSchema->quoteValue($item->$itemNameElement)."', '".$dbSchema->quoteValue($item->$itemPriceElement)."')";
                    $counter++;

                    if ($counter >= self::BATCH_SIZE)
                    {
                        $this->executeSql($insertSql);

                        $insertSql = self::insertSqlDefault();
                        $counter = 0;

                        echo memory_get_usage()."\n";
                    }
                }
                unset($item);

                $xmlReader->next($itemElement);
            }
            $xmlReader->close();

            //Загружаем оставшиеся товары
            $this->executeSql($insertSql);
        } else {
            echo "Файл по ссылке в csv формате, уточните данные по формату (строки и столбцы считать начиная с 1).\n";
            $startFrom = $this->prompt('С какой строки начинать чтение файла:', ['default' => 2]);
            $itemCodeColumn = $this->prompt('В какой по счёту колонке хранится АРТИКУЛ товара:', ['default' => 1]);
            $itemNameColumn = $this->prompt('В какой по счёту колонке хранится НАИМЕНОВАНИЕ товара:', ['default' => 2]);
            $itemPriceColumn = $this->prompt('В какой по счёту колонке хранится ЦЕНА товара:', ['default' => 3]);

            $itemCodeColumn--;
            $itemNameColumn--;
            $itemPriceColumn--;

            //Пропускаем нужное количество строк от начала
            for ($i = 1;$i < $startFrom;$i++) {
                fgets($fileHandle);
            }

            $insertSql = self::insertSqlDefault();
            $counter = 0;
            while (($raw_string = fgets($fileHandle)) !== false) {
                $row = str_getcsv($raw_string, ';');

                if ($counter > 0) {
                    $insertSql .= ', ';
                }
                $insertSql .= "(".$dbSchema->quoteValue($row[$itemCodeColumn]).", ".$dbSchema->quoteValue($row[$itemNameColumn]).", ".$dbSchema->quoteValue($row[$itemPriceColumn]).")";
                $counter++;

                if ($counter >= self::BATCH_SIZE)
                {
                    $this->executeSql($insertSql);

                    $insertSql = self::insertSqlDefault();
                    $counter = 0;
                }
            }

            //Загружаем оставшиеся товары
            $this->executeSql($insertSql);
            fclose($fileHandle);
        }

        return ExitCode::OK;
    }

    private function executeSql($sql)
    {
        if ($sql != self::insertSqlDefault()) {
            $command = \Yii::$app->db->createCommand($sql);
            $num = $command->execute();

            unset($command);

            echo "Добавлено $num товаров...\n";
        }
    }

    private static function insertSqlDefault()
    {
        return "INSERT IGNORE INTO ".Product::tableName()." (code, name, price) VALUES ";
    }
}
